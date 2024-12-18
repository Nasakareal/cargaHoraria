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

    /* Array para almacenar las asignaciones a actualizar en manual_schedule_assignments */
    $asignaciones_actualizar = [];

    /* Verificar conflictos de horario para cada grupo y materia */
    foreach ($grupo_ids as $grupo_id) {
        foreach ($materia_ids as $materia_id) {
            // Obtener los horarios para esta materia y grupo
            $sentencia_obtener_horarios = $pdo->prepare("
                SELECT schedule_day, start_time, end_time
                FROM manual_schedule_assignments
                WHERE subject_id = ? AND group_id = ?
            ");
            $sentencia_obtener_horarios->execute([$materia_id, $grupo_id]);
            $horarios_nuevos = $sentencia_obtener_horarios->fetchAll(PDO::FETCH_ASSOC);

            if (empty($horarios_nuevos)) {
                throw new Exception("No se encontraron horarios para la materia ID $materia_id y grupo ID $grupo_id.");
            }

            foreach ($horarios_nuevos as $horario_nuevo) {
                $schedule_day = $horario_nuevo['schedule_day'];
                $new_start_time = $horario_nuevo['start_time'];
                $new_end_time = $horario_nuevo['end_time'];

                // Verificar si el profesor ya tiene asignaciones que se solapan en este horario
                $sentencia_verificar_conflicto = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM manual_schedule_assignments ma
                    JOIN teacher_subjects ts ON ma.subject_id = ts.subject_id AND ma.group_id = ts.group_id
                    WHERE ts.teacher_id = ? 
                      AND ma.schedule_day = ?
                      AND (
                            (ma.start_time < ? AND ma.end_time > ?) OR
                            (ma.start_time >= ? AND ma.start_time < ?) OR
                            (ma.end_time > ? AND ma.end_time <= ?)
                          )
                ");
                $sentencia_verificar_conflicto->execute([
                    $teacher_id,
                    $schedule_day,
                    $new_end_time, $new_start_time, // Caso 1: (ma.start_time < new_end_time AND ma.end_time > new_start_time)
                    $new_start_time, $new_end_time, // Caso 2: (ma.start_time >= new_start_time AND ma.start_time < new_end_time)
                    $new_start_time, $new_end_time  // Caso 3: (ma.end_time > new_start_time AND ma.end_time <= new_end_time)
                ]);
                $conflicto = $sentencia_verificar_conflicto->fetchColumn();

                if ($conflicto > 0) {
                    throw new Exception("El horario de la materia ID $materia_id y grupo ID $grupo_id se solapa con una asignación existente.");
                }

                // Agregar al array para actualizar manual_schedule_assignments
                $asignaciones_actualizar[] = [
                    'subject_id' => $materia_id,
                    'group_id' => $grupo_id,
                    'schedule_day' => $schedule_day,
                    'start_time' => $new_start_time,
                    'end_time' => $new_end_time
                ];
            }
        }
    }

    /* Insertar nuevas asignaciones de materias y grupos, evitando duplicados */
    foreach ($grupo_ids as $grupo_id) {
        foreach ($materia_ids as $materia_id) {
            /* Verificar si ya existe esta asignación */
            $sentencia_verificar = $pdo->prepare("
                SELECT COUNT(*) FROM teacher_subjects 
                WHERE teacher_id = ? AND subject_id = ? AND group_id = ?
            ");
            $sentencia_verificar->execute([$teacher_id, $materia_id, $grupo_id]);
            $existe_asignacion = $sentencia_verificar->fetchColumn();

            /* Si no existe, insertar la nueva asignación */
            if ($existe_asignacion == 0) {
                $sentencia_insertar = $pdo->prepare("
                    INSERT INTO teacher_subjects (teacher_id, subject_id, group_id, fyh_creacion, fyh_actualizacion) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $sentencia_insertar->execute([$teacher_id, $materia_id, $grupo_id, $fechaHora, $fechaHora]);

                // Ahora, para cada schedule block, actualizar teacher_id
                foreach ($asignaciones_actualizar as $asignacion) {
                    if ($asignacion['subject_id'] == $materia_id && $asignacion['group_id'] == $grupo_id) {
                        $sentencia_actualizar_ma = $pdo->prepare("
                            UPDATE manual_schedule_assignments
                            SET teacher_id = ?
                            WHERE subject_id = ? AND group_id = ? AND schedule_day = ? AND start_time = ? AND end_time = ?
                        ");
                        $sentencia_actualizar_ma->execute([
                            $teacher_id,
                            $asignacion['subject_id'],
                            $asignacion['group_id'],
                            $asignacion['schedule_day'],
                            $asignacion['start_time'],
                            $asignacion['end_time']
                        ]);
                    }
                }
            }
        }
    }

    /* Calcular y actualizar las horas totales del profesor */
    $sentencia_horas_totales = $pdo->prepare("
        SELECT SUM(s.weekly_hours) AS total_hours
        FROM teacher_subjects ts
        JOIN subjects s ON ts.subject_id = s.subject_id
        WHERE ts.teacher_id = ?
    ");
    $sentencia_horas_totales->execute([$teacher_id]);
    $total_hours = (int) $sentencia_horas_totales->fetchColumn();

    $sentencia_actualizar_horas = $pdo->prepare("
        UPDATE teachers SET hours = ?, fyh_actualizacion = ? WHERE teacher_id = ?
    ");
    $sentencia_actualizar_horas->execute([$total_hours, $fechaHora, $teacher_id]);

    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Se han añadido las materias con éxito.";
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
?>
