<?php

include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$materia_ids = isset($_POST['materias_asignadas']) ? $_POST['materias_asignadas'] : [];
$grupo_ids = isset($_POST['grupos_asignados']) ? array_filter($_POST['grupos_asignados']) : [];
$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    /* Validar que haya al menos un grupo seleccionado */
    if (empty($grupo_ids)) {
        throw new Exception("Debe seleccionar al menos un grupo para asignar materias.");
    }

    /* Validar interferencias de horarios */
    foreach ($materia_ids as $materia_id) {
        foreach ($grupo_ids as $grupo_id) {
            $sentencia_interferencia = $pdo->prepare("
                SELECT sa.schedule_id, sa.schedule_day, sa.start_time, sa.end_time, sa.group_id
                FROM schedule_assignments sa
                WHERE sa.teacher_id = ?
                AND sa.schedule_day = (
                    SELECT sa2.schedule_day 
                    FROM schedule_assignments sa2
                    WHERE sa2.group_id = ? AND sa2.subject_id = ?
                    LIMIT 1
                )
                AND (
                    sa.start_time < (
                        SELECT sa2.end_time 
                        FROM schedule_assignments sa2
                        WHERE sa2.group_id = ? AND sa2.subject_id = ?
                        LIMIT 1
                    )
                    AND sa.end_time > (
                        SELECT sa2.start_time 
                        FROM schedule_assignments sa2
                        WHERE sa2.group_id = ? AND sa2.subject_id = ?
                        LIMIT 1
                    )
                )
            ");
            $sentencia_interferencia->execute([$teacher_id, $grupo_id, $materia_id, $grupo_id, $materia_id, $grupo_id, $materia_id]);
            $interferencia = $sentencia_interferencia->fetch(PDO::FETCH_ASSOC);

            if ($interferencia) {
                $dia_conflicto = $interferencia['schedule_day'];
                $hora_inicio_conflicto = $interferencia['start_time'];
                $hora_fin_conflicto = $interferencia['end_time'];
                $grupo_conflicto = $interferencia['group_id'];
                throw new Exception(
                    "Conflicto detectado: No puede asignar la materia (ID: $materia_id) al grupo (ID: $grupo_id) porque ya tiene un horario asignado en el grupo (ID: $grupo_conflicto) el día $dia_conflicto de $hora_inicio_conflicto a $hora_fin_conflicto."
                );
            }
        }
    }

    /* Insertar materias nuevas asociadas a los grupos seleccionados */
    foreach ($grupo_ids as $grupo_id) {
        foreach ($materia_ids as $materia_id) {
            $sentencia_insertar = $pdo->prepare("
                INSERT INTO teacher_subjects (teacher_id, subject_id, group_id, fyh_creacion, fyh_actualizacion) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $sentencia_insertar->execute([$teacher_id, $materia_id, $grupo_id, $fechaHora, $fechaHora]);

            /* Actualizar la tabla de horarios */
            $sentencia_actualizar_horarios = $pdo->prepare("
                UPDATE schedule_assignments 
                SET teacher_id = ?, fyh_actualizacion = ?
                WHERE group_id = ? AND subject_id = ?
            ");
            $sentencia_actualizar_horarios->execute([$teacher_id, $fechaHora, $grupo_id, $materia_id]);
        }
    }

    /* Obtener las horas actuales del profesor */
    $sentencia_horas_actuales = $pdo->prepare("
        SELECT SUM(s.weekly_hours) AS total_hours
        FROM teacher_subjects ts
        JOIN subjects s ON ts.subject_id = s.subject_id
        WHERE ts.teacher_id = ?
    ");
    $sentencia_horas_actuales->execute([$teacher_id]);
    $horas_actuales = (int) $sentencia_horas_actuales->fetchColumn();

    /* Calcular las horas de las nuevas materias */
    $total_hours = $horas_actuales;
    if (!empty($materia_ids)) {
        $placeholders_materias = implode(',', array_fill(0, count($materia_ids), '?'));
        $sentencia_horas_materias = $pdo->prepare("
            SELECT SUM(s.weekly_hours) AS total_hours
            FROM subjects s
            WHERE s.subject_id IN ($placeholders_materias)
        ");
        $sentencia_horas_materias->execute($materia_ids);
        $total_hours += (int) $sentencia_horas_materias->fetchColumn();
    }

    /* Actualizar las horas totales del profesor */
    $sentencia_actualizar_horas = $pdo->prepare("
        UPDATE teachers SET hours = ?, fyh_actualizacion = ? WHERE teacher_id = ?
    ");
    $sentencia_actualizar_horas->execute([$total_hours, $fechaHora, $teacher_id]);

    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Se ha actualizado con éxito";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
} catch (Exception $exception) {
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = $exception->getMessage();
    $_SESSION['icono'] = "error";
    error_log("Error: " . $exception->getMessage());
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
}
