<?php

include('../../../app/config.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$teacher_id = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;
$materia_ids = isset($_POST['materias_asignadas']) ? $_POST['materias_asignadas'] : [];
$grupo_ids = isset($_POST['grupos_asignados']) ? array_filter($_POST['grupos_asignados'], 'is_numeric') : [];
$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    if (empty($grupo_ids)) {
        throw new Exception("Debe seleccionar al menos un grupo para asignar materias.");
    }

    $asignaciones_actualizar = [];

    foreach ($grupo_ids as $grupo_id) {
        foreach ($materia_ids as $materia_id) {
            $sentencia_obtener_horarios = $pdo->prepare("
                SELECT schedule_day, start_time, end_time, 'manual_schedule_assignments' AS tabla
                FROM manual_schedule_assignments
                WHERE subject_id = ? AND group_id = ?
                UNION ALL
                SELECT schedule_day, start_time, end_time, 'schedule_assignments' AS tabla
                FROM schedule_assignments
                WHERE subject_id = ? AND group_id = ?
            ");
            $sentencia_obtener_horarios->execute([$materia_id, $grupo_id, $materia_id, $grupo_id]);
            $horarios_nuevos = $sentencia_obtener_horarios->fetchAll(PDO::FETCH_ASSOC);

            if (empty($horarios_nuevos)) {
                continue;
            }

            foreach ($horarios_nuevos as $horario_nuevo) {
                $schedule_day = $horario_nuevo['schedule_day'];
                $new_start_time = $horario_nuevo['start_time'];
                $new_end_time = $horario_nuevo['end_time'];
                $tabla_conflicto = $horario_nuevo['tabla'];

                $sentencia_verificar_conflicto = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM (
                        SELECT ma.schedule_day, ma.start_time, ma.end_time
                        FROM schedule_assignments ma
                        JOIN teacher_subjects ts ON ma.subject_id = ts.subject_id AND ma.group_id = ts.group_id
                        WHERE ts.teacher_id = ?
                          AND ma.schedule_day = ?
                          AND (
                                (ma.start_time < ? AND ma.end_time > ?) OR
                                (ma.start_time >= ? AND ma.start_time < ?) OR
                                (ma.end_time > ? AND ma.end_time <= ?)
                              )
                        UNION ALL
                        SELECT msa.schedule_day, msa.start_time, msa.end_time
                        FROM manual_schedule_assignments msa
                        JOIN teacher_subjects ts ON msa.subject_id = ts.subject_id AND msa.group_id = ts.group_id
                        WHERE ts.teacher_id = ?
                          AND msa.schedule_day = ?
                          AND (
                                (msa.start_time < ? AND msa.end_time > ?) OR
                                (msa.start_time >= ? AND msa.start_time < ?) OR
                                (msa.end_time > ? AND msa.end_time <= ?)
                              )
                    ) AS conflictos
                ");
                $sentencia_verificar_conflicto->execute([
                    $teacher_id,
                    $schedule_day,
                    $new_end_time, $new_start_time,
                    $new_start_time, $new_end_time,
                    $new_start_time, $new_end_time,
                    $teacher_id,
                    $schedule_day,
                    $new_end_time, $new_start_time,
                    $new_start_time, $new_end_time,
                    $new_start_time, $new_end_time
                ]);
                $conflicto = $sentencia_verificar_conflicto->fetchColumn();

                if ($conflicto > 0) {
                    $sentencia_verificar_tabla = $pdo->prepare("
                        SELECT 'schedule_assignments' AS tabla
                        FROM schedule_assignments ma
                        JOIN teacher_subjects ts ON ma.subject_id = ts.subject_id AND ma.group_id = ts.group_id
                        WHERE ts.teacher_id = ?
                          AND ma.schedule_day = ?
                          AND (
                                (ma.start_time < ? AND ma.end_time > ?) OR
                                (ma.start_time >= ? AND ma.start_time < ?) OR
                                (ma.end_time > ? AND ma.end_time <= ?)
                              )
                        UNION ALL
                        SELECT 'manual_schedule_assignments' AS tabla
                        FROM manual_schedule_assignments msa
                        JOIN teacher_subjects ts ON msa.subject_id = ts.subject_id AND msa.group_id = ts.group_id
                        WHERE ts.teacher_id = ?
                          AND msa.schedule_day = ?
                          AND (
                                (msa.start_time < ? AND msa.end_time > ?) OR
                                (msa.start_time >= ? AND msa.start_time < ?) OR
                                (msa.end_time > ? AND msa.end_time <= ?)
                              )
                        LIMIT 1
                    ");
                    $sentencia_verificar_tabla->execute([
                        $teacher_id,
                        $schedule_day,
                        $new_end_time, $new_start_time,
                        $new_start_time, $new_end_time,
                        $new_start_time, $new_end_time,
                        $teacher_id,
                        $schedule_day,
                        $new_end_time, $new_start_time,
                        $new_start_time, $new_end_time,
                        $new_start_time, $new_end_time
                    ]);
                    $tabla_conflicto_detectada = $sentencia_verificar_tabla->fetchColumn();

                    throw new Exception("El horario de la materia ID $materia_id y grupo ID $grupo_id se solapa con una asignación existente en la tabla '$tabla_conflicto_detectada'.");
                }

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

    foreach ($grupo_ids as $grupo_id) {
        foreach ($materia_ids as $materia_id) {
            $sentencia_verificar = $pdo->prepare("
                SELECT COUNT(*) FROM teacher_subjects 
                WHERE teacher_id = ? AND subject_id = ? AND group_id = ?
            ");
            $sentencia_verificar->execute([$teacher_id, $materia_id, $grupo_id]);
            $existe_asignacion = $sentencia_verificar->fetchColumn();

            if ($existe_asignacion == 0) {
                $sentencia_insertar = $pdo->prepare("
                    INSERT INTO teacher_subjects (teacher_id, subject_id, group_id, fyh_creacion, fyh_actualizacion) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $sentencia_insertar->execute([$teacher_id, $materia_id, $grupo_id, $fechaHora, $fechaHora]);

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

                        $sentencia_actualizar_sa = $pdo->prepare("
                            UPDATE schedule_assignments
                            SET teacher_id = ?
                            WHERE subject_id = ? AND group_id = ? AND schedule_day = ? AND start_time = ? AND end_time = ?
                        ");
                        $sentencia_actualizar_sa->execute([
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

    $_SESSION['mensaje'] = "Se han añadido las materias con éxito.";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
} catch (Exception $exception) {
    $pdo->rollBack();

    $_SESSION['mensaje'] = $exception->getMessage();
    $_SESSION['icono'] = "error";
    error_log("Error: " . $exception->getMessage());
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
}

