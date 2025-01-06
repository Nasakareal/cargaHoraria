<?php
// swap_assignments.php

include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/config.php');

// Configuración de errores
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/app/controllers/intercambios/debug_swap.log');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener y sanitizar los datos recibidos
    $assignment_id_1 = isset($_POST['assignment_id_1']) ? intval($_POST['assignment_id_1']) : 0;
    $assignment_id_2 = isset($_POST['assignment_id_2']) ? intval($_POST['assignment_id_2']) : 0;

    // Validaciones básicas
    if (empty($assignment_id_1) || empty($assignment_id_2)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan IDs de asignaciones para intercambiar.']);
        exit;
    }

    // Evitar intercambiar una asignación consigo misma
    if ($assignment_id_1 === $assignment_id_2) {
        echo json_encode(['status' => 'error', 'message' => 'No se puede intercambiar una asignación consigo misma.']);
        exit;
    }

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Obtener detalles de la primera asignación
        $query1 = $pdo->prepare("
            SELECT *
            FROM manual_schedule_assignments
            WHERE assignment_id = :assignment_id
            FOR UPDATE
        ");
        $query1->bindParam(':assignment_id', $assignment_id_1, PDO::PARAM_INT);
        $query1->execute();
        $assignment1 = $query1->fetch(PDO::FETCH_ASSOC);

        if (!$assignment1) {
            throw new Exception('La primera asignación no existe.');
        }

        // Obtener detalles de la segunda asignación
        $query2 = $pdo->prepare("
            SELECT *
            FROM manual_schedule_assignments
            WHERE assignment_id = :assignment_id
            FOR UPDATE
        ");
        $query2->bindParam(':assignment_id', $assignment_id_2, PDO::PARAM_INT);
        $query2->execute();
        $assignment2 = $query2->fetch(PDO::FETCH_ASSOC);

        if (!$assignment2) {
            throw new Exception('La segunda asignación no existe.');
        }

        // Asegurar que ambas asignaciones pertenecen al mismo grupo
        if ($assignment1['group_id'] != $assignment2['group_id']) {
            throw new Exception('Las asignaciones pertenecen a grupos diferentes.');
        }

        // Asegurar que ambas asignaciones tienen el mismo tipo de espacio
        if ($assignment1['tipo_espacio'] !== $assignment2['tipo_espacio']) {
            throw new Exception('Las asignaciones tienen diferentes tipos de espacio.');
        }

        // Verificar conflictos de espacio después del intercambio
        // Comprobar si el espacio de la asignación 1 está disponible para la asignación 2
        $space_column_1 = ($assignment1['tipo_espacio'] === 'Laboratorio') ? 'lab1_assigned' : 'classroom_id';
        $space_id_1 = ($assignment1['tipo_espacio'] === 'Laboratorio') ? intval($assignment1['lab_id']) : intval($assignment1['classroom_id']);

        $query_conflict_space_1 = $pdo->prepare("
            SELECT assignment_id
            FROM manual_schedule_assignments
            WHERE schedule_day = :schedule_day
              AND (
                  (:new_start_time < end_time AND :new_end_time > start_time)
              )
              AND $space_column_1 = :space_id
              AND assignment_id NOT IN (:assignment_id_1, :assignment_id_2)
              AND estado = 'activo'
            LIMIT 1
        ");
        $query_conflict_space_1->bindParam(':schedule_day', $assignment2['schedule_day'], PDO::PARAM_STR);
        $query_conflict_space_1->bindParam(':new_start_time', $assignment1['start_time'], PDO::PARAM_STR);
        $query_conflict_space_1->bindParam(':new_end_time', $assignment1['end_time'], PDO::PARAM_STR);
        $query_conflict_space_1->bindParam(':space_id', $space_id_1, PDO::PARAM_INT);
        $query_conflict_space_1->bindParam(':assignment_id_1', $assignment_id_1, PDO::PARAM_INT);
        $query_conflict_space_1->bindParam(':assignment_id_2', $assignment_id_2, PDO::PARAM_INT);
        $query_conflict_space_1->execute();

        if ($query_conflict_space_1->rowCount() > 0) {
            throw new Exception('El espacio de la primera asignación ya está ocupado en el nuevo horario.');
        }

        // Comprobar si el espacio de la asignación 2 está disponible para la asignación 1
        $space_column_2 = ($assignment2['tipo_espacio'] === 'Laboratorio') ? 'lab1_assigned' : 'classroom_id';
        $space_id_2 = ($assignment2['tipo_espacio'] === 'Laboratorio') ? intval($assignment2['lab_id']) : intval($assignment2['classroom_id']);

        $query_conflict_space_2 = $pdo->prepare("
            SELECT assignment_id
            FROM manual_schedule_assignments
            WHERE schedule_day = :schedule_day
              AND (
                  (:new_start_time < end_time AND :new_end_time > start_time)
              )
              AND $space_column_2 = :space_id
              AND assignment_id NOT IN (:assignment_id_1, :assignment_id_2)
              AND estado = 'activo'
            LIMIT 1
        ");
        $query_conflict_space_2->bindParam(':schedule_day', $assignment1['schedule_day'], PDO::PARAM_STR);
        $query_conflict_space_2->bindParam(':new_start_time', $assignment2['start_time'], PDO::PARAM_STR);
        $query_conflict_space_2->bindParam(':new_end_time', $assignment2['end_time'], PDO::PARAM_STR);
        $query_conflict_space_2->bindParam(':space_id', $space_id_2, PDO::PARAM_INT);
        $query_conflict_space_2->bindParam(':assignment_id_1', $assignment_id_1, PDO::PARAM_INT);
        $query_conflict_space_2->bindParam(':assignment_id_2', $assignment_id_2, PDO::PARAM_INT);
        $query_conflict_space_2->execute();

        if ($query_conflict_space_2->rowCount() > 0) {
            throw new Exception('El espacio de la segunda asignación ya está ocupado en el nuevo horario.');
        }

        // Verificar conflictos de profesor después del intercambio
        $teacher_id_1 = $assignment1['teacher_id'];
        $teacher_id_2 = $assignment2['teacher_id'];

        // Verificar disponibilidad del profesor 1 en el nuevo horario de la asignación 2
        if ($teacher_id_1 !== null) {
            $query_conflict_teacher_1 = $pdo->prepare("
                SELECT assignment_id
                FROM manual_schedule_assignments
                WHERE teacher_id = :teacher_id
                  AND schedule_day = :schedule_day
                  AND (
                      (:new_start_time < end_time AND :new_end_time > start_time)
                  )
                  AND assignment_id NOT IN (:assignment_id_1, :assignment_id_2)
                  AND estado = 'activo'
                LIMIT 1
            ");
            $query_conflict_teacher_1->bindParam(':teacher_id', $teacher_id_1, PDO::PARAM_INT);
            $query_conflict_teacher_1->bindParam(':schedule_day', $assignment2['schedule_day'], PDO::PARAM_STR);
            $query_conflict_teacher_1->bindParam(':new_start_time', $assignment1['start_time'], PDO::PARAM_STR);
            $query_conflict_teacher_1->bindParam(':new_end_time', $assignment1['end_time'], PDO::PARAM_STR);
            $query_conflict_teacher_1->bindParam(':assignment_id_1', $assignment_id_1, PDO::PARAM_INT);
            $query_conflict_teacher_1->bindParam(':assignment_id_2', $assignment_id_2, PDO::PARAM_INT);
            $query_conflict_teacher_1->execute();

            if ($query_conflict_teacher_1->rowCount() > 0) {
                throw new Exception('El profesor de la primera asignación ya tiene una asignación en el nuevo horario de la segunda asignación.');
            }
        }

        // Verificar disponibilidad del profesor 2 en el nuevo horario de la asignación 1
        if ($teacher_id_2 !== null) {
            $query_conflict_teacher_2 = $pdo->prepare("
                SELECT assignment_id
                FROM manual_schedule_assignments
                WHERE teacher_id = :teacher_id
                  AND schedule_day = :schedule_day
                  AND (
                      (:new_start_time < end_time AND :new_end_time > start_time)
                  )
                  AND assignment_id NOT IN (:assignment_id_1, :assignment_id_2)
                  AND estado = 'activo'
                LIMIT 1
            ");
            $query_conflict_teacher_2->bindParam(':teacher_id', $teacher_id_2, PDO::PARAM_INT);
            $query_conflict_teacher_2->bindParam(':schedule_day', $assignment1['schedule_day'], PDO::PARAM_STR);
            $query_conflict_teacher_2->bindParam(':new_start_time', $assignment2['start_time'], PDO::PARAM_STR);
            $query_conflict_teacher_2->bindParam(':new_end_time', $assignment2['end_time'], PDO::PARAM_STR);
            $query_conflict_teacher_2->bindParam(':assignment_id_1', $assignment_id_1, PDO::PARAM_INT);
            $query_conflict_teacher_2->bindParam(':assignment_id_2', $assignment_id_2, PDO::PARAM_INT);
            $query_conflict_teacher_2->execute();

            if ($query_conflict_teacher_2->rowCount() > 0) {
                throw new Exception('El profesor de la segunda asignación ya tiene una asignación en el nuevo horario de la primera asignación.');
            }
        }

        // Verificar horas semanales permitidas para ambas asignaciones después del intercambio

        // Para la primera asignación después del intercambio
        $subject_id_1 = intval($assignment1['subject_id']);
        $group_id_1 = intval($assignment1['group_id']);

        $query_weekly_hours_1 = $pdo->prepare("SELECT weekly_hours FROM subjects WHERE subject_id = :subject_id");
        $query_weekly_hours_1->bindParam(':subject_id', $subject_id_1, PDO::PARAM_INT);
        $query_weekly_hours_1->execute();
        $subject_1 = $query_weekly_hours_1->fetch(PDO::FETCH_ASSOC);

        if (!$subject_1) {
            throw new Exception('La materia de la primera asignación no existe.');
        }

        $weekly_hours_1 = floatval($subject_1['weekly_hours']);

        // Calcular duración de la segunda asignación (para la primera asignación)
        $start_new_1 = new DateTime($assignment2['start_time']);
        $end_new_1 = new DateTime($assignment2['end_time']);
        $interval_new_1 = $start_new_1->diff($end_new_1);
        $duration_new_1 = $interval_new_1->h + ($interval_new_1->i / 60) + ($interval_new_1->s / 3600);

        // Calcular horas actuales asignadas para la primera asignación después del intercambio
        $query_current_hours_1 = $pdo->prepare("
            SELECT start_time, end_time
            FROM manual_schedule_assignments
            WHERE subject_id = :subject_id
              AND group_id = :group_id
              AND assignment_id NOT IN (:assignment_id_1, :assignment_id_2)
              AND estado = 'activo'
        ");
        $query_current_hours_1->bindParam(':subject_id', $subject_id_1, PDO::PARAM_INT);
        $query_current_hours_1->bindParam(':group_id', $group_id_1, PDO::PARAM_INT);
        $query_current_hours_1->bindParam(':assignment_id_1', $assignment_id_1, PDO::PARAM_INT);
        $query_current_hours_1->bindParam(':assignment_id_2', $assignment_id_2, PDO::PARAM_INT);
        $query_current_hours_1->execute();

        $total_assigned_hours_1 = 0;

        while ($row = $query_current_hours_1->fetch(PDO::FETCH_ASSOC)) {
            $s = new DateTime($row['start_time']);
            $e = new DateTime($row['end_time']);
            $diff = $s->diff($e);
            $hours = $diff->h + ($diff->i / 60) + ($diff->s / 3600);
            $total_assigned_hours_1 += $hours;
        }

        $new_total_1 = $total_assigned_hours_1 + $duration_new_1;

        if ($new_total_1 > $weekly_hours_1) {
            throw new Exception('El intercambio excede las horas semanales permitidas para la materia de la primera asignación en este grupo.');
        }

        // Para la segunda asignación después del intercambio
        $subject_id_2 = intval($assignment2['subject_id']);
        $group_id_2 = intval($assignment2['group_id']);

        $query_weekly_hours_2 = $pdo->prepare("SELECT weekly_hours FROM subjects WHERE subject_id = :subject_id");
        $query_weekly_hours_2->bindParam(':subject_id', $subject_id_2, PDO::PARAM_INT);
        $query_weekly_hours_2->execute();
        $subject_2 = $query_weekly_hours_2->fetch(PDO::FETCH_ASSOC);

        if (!$subject_2) {
            throw new Exception('La materia de la segunda asignación no existe.');
        }

        $weekly_hours_2 = floatval($subject_2['weekly_hours']);

        // Calcular duración de la primera asignación (para la segunda asignación)
        $start_new_2 = new DateTime($assignment1['start_time']);
        $end_new_2 = new DateTime($assignment1['end_time']);
        $interval_new_2 = $start_new_2->diff($end_new_2);
        $duration_new_2 = $interval_new_2->h + ($interval_new_2->i / 60) + ($interval_new_2->s / 3600);

        // Calcular horas actuales asignadas para la segunda asignación después del intercambio
        $query_current_hours_2 = $pdo->prepare("
            SELECT start_time, end_time
            FROM manual_schedule_assignments
            WHERE subject_id = :subject_id
              AND group_id = :group_id
              AND assignment_id NOT IN (:assignment_id_1, :assignment_id_2)
              AND estado = 'activo'
        ");
        $query_current_hours_2->bindParam(':subject_id', $subject_id_2, PDO::PARAM_INT);
        $query_current_hours_2->bindParam(':group_id', $group_id_2, PDO::PARAM_INT);
        $query_current_hours_2->bindParam(':assignment_id_1', $assignment_id_1, PDO::PARAM_INT);
        $query_current_hours_2->bindParam(':assignment_id_2', $assignment_id_2, PDO::PARAM_INT);
        $query_current_hours_2->execute();

        $total_assigned_hours_2 = 0;

        while ($row = $query_current_hours_2->fetch(PDO::FETCH_ASSOC)) {
            $s_conflict = new DateTime($row['start_time']);
            $e_conflict = new DateTime($row['end_time']);
            $diff_conflict = $s_conflict->diff($e_conflict);
            $hours_conflict = $diff_conflict->h + ($diff_conflict->i / 60) + ($diff_conflict->s / 3600);
            $total_assigned_hours_2 += $hours_conflict;
        }

        $new_total_2 = $total_assigned_hours_2 + $duration_new_2;

        if ($new_total_2 > $weekly_hours_2) {
            throw new Exception('El intercambio excede las horas semanales permitidas para la materia de la segunda asignación en este grupo.');
        }

        // Realizar el intercambio de horarios
        // Actualizar la primera asignación con los horarios de la segunda asignación
        $stmt_update_1 = $pdo->prepare("
            UPDATE manual_schedule_assignments
            SET
                start_time = :new_start_time,
                end_time = :new_end_time,
                schedule_day = :new_schedule_day,
                fyh_actualizacion = :fyh_actualizacion
            WHERE assignment_id = :assignment_id
        ");
        $stmt_update_1->bindParam(':new_start_time', $assignment2['start_time'], PDO::PARAM_STR);
        $stmt_update_1->bindParam(':new_end_time', $assignment2['end_time'], PDO::PARAM_STR);
        $stmt_update_1->bindParam(':new_schedule_day', $assignment2['schedule_day'], PDO::PARAM_STR);
        $stmt_update_1->bindParam(':fyh_actualizacion', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt_update_1->bindParam(':assignment_id', $assignment_id_1, PDO::PARAM_INT);
        $stmt_update_1->execute();

        // Actualizar la segunda asignación con los horarios de la primera asignación
        $stmt_update_2 = $pdo->prepare("
            UPDATE manual_schedule_assignments
            SET
                start_time = :new_start_time,
                end_time = :new_end_time,
                schedule_day = :new_schedule_day,
                fyh_actualizacion = :fyh_actualizacion
            WHERE assignment_id = :assignment_id
        ");
        $stmt_update_2->bindParam(':new_start_time', $assignment1['start_time'], PDO::PARAM_STR);
        $stmt_update_2->bindParam(':new_end_time', $assignment1['end_time'], PDO::PARAM_STR);
        $stmt_update_2->bindParam(':new_schedule_day', $assignment1['schedule_day'], PDO::PARAM_STR);
        $stmt_update_2->bindParam(':fyh_actualizacion', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt_update_2->bindParam(':assignment_id', $assignment_id_2, PDO::PARAM_INT);
        $stmt_update_2->execute();

        // Restaurar asignaciones en schedule_assignments
        restaurarAsignaciones($pdo);

        // Confirmar transacción
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Las asignaciones han sido intercambiadas correctamente.']);
        exit;
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $pdo->rollBack();
        error_log("Error en swap_assignments.php: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// Función para restaurar asignaciones en schedule_assignments desde manual_schedule_assignments
function restaurarAsignaciones($pdo)
{
    try {
        error_log("Iniciando la restauración de todas las asignaciones...");

        $sql_restore = "
            INSERT INTO schedule_assignments 
                (assignment_id, subject_id, group_id, teacher_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion, tipo_espacio, lab_id)
            SELECT 
                m.assignment_id,
                m.subject_id, 
                m.group_id, 
                m.teacher_id, 
                m.classroom_id, 
                m.schedule_day, 
                m.start_time, 
                m.end_time, 
                m.estado, 
                m.fyh_creacion, 
                m.tipo_espacio, 
                m.lab_id
            FROM manual_schedule_assignments m
            LEFT JOIN schedule_assignments sa 
                ON sa.assignment_id = m.assignment_id
            WHERE NOT EXISTS (
                SELECT 1 
                FROM schedule_assignments sa2
                WHERE sa2.assignment_id = m.assignment_id
            )
        ";

        error_log("Consulta de restauración ejecutada: $sql_restore");

        $stmt = $pdo->prepare($sql_restore);
        $stmt->execute();
        $rows_affected = $stmt->rowCount();

        if ($rows_affected > 0) {
            error_log("Todas las asignaciones restauradas exitosamente: $rows_affected filas insertadas.");
        } else {
            error_log("No se encontraron asignaciones nuevas para restaurar o ya existen.");
        }
    } catch (PDOException $e) {
        error_log("Error al restaurar asignaciones: " . $e->getMessage());
        throw $e;
    }
}
?>
