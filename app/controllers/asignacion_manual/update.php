<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/registro_eventos.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/app/controllers/asignacion_manual/debug.log');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_id    = isset($_POST['subject_id'])    ? intval($_POST['subject_id'])    : 0;
    $start_time    = isset($_POST['start_time'])    ? $_POST['start_time']            : '';
    $end_time      = isset($_POST['end_time'])      ? $_POST['end_time']              : '';
    $schedule_day  = isset($_POST['schedule_day'])  ? $_POST['schedule_day']          : '';
    $group_id      = isset($_POST['group_id'])      ? intval($_POST['group_id'])      : 0;
    $assignment_id = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;
    $lab_id        = isset($_POST['lab_id'])        ? intval($_POST['lab_id'])        : 0;
    $aula_id       = isset($_POST['aula_id'])       ? intval($_POST['aula_id'])       : 0;
    $tipo_espacio  = isset($_POST['tipo_espacio'])  ? $_POST['tipo_espacio']          : null;

    if (empty($subject_id) || empty($start_time) || empty($schedule_day) || empty($group_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
        exit;
    }

    if ($tipo_espacio === 'Laboratorio') {
        if (empty($lab_id) || $lab_id == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Debe seleccionar un laboratorio para asignar.']);
            exit;
        }
        $aula_id = null; 
    } elseif ($tipo_espacio === 'Aula') {
        if (empty($aula_id) || $aula_id == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Debe seleccionar un aula para asignar.']);
            exit;
        }
        $lab_id = null; 
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tipo de espacio inválido.']);
        exit;
    }

    if (empty($end_time)) {
        $start_time_obj = new DateTime($start_time);
        $start_time_obj->modify('+1 hour');
        $end_time = $start_time_obj->format('H:i:s');
    }

    $start_time = date("H:i:s", strtotime($start_time));
    $end_time   = date("H:i:s", strtotime($end_time));

    try {
        $pdo->beginTransaction();

        $query_teacher = $pdo->prepare("
            SELECT ts.teacher_id 
            FROM teacher_subjects ts
            WHERE ts.subject_id = :subject_id 
              AND ts.group_id = :group_id
            LIMIT 1
        ");
        $query_teacher->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
        $query_teacher->bindParam(':group_id',   $group_id,   PDO::PARAM_INT);
        $query_teacher->execute();

        $teacher    = $query_teacher->fetch(PDO::FETCH_ASSOC);
        $teacher_id = $teacher ? intval($teacher['teacher_id']) : null;

        if ($tipo_espacio === 'Laboratorio') {
            $query_verificar_manual = $pdo->prepare("
                SELECT assignment_id 
                FROM manual_schedule_assignments 
                WHERE schedule_day = :schedule_day 
                  AND ((:start_time < end_time AND :end_time > start_time))
                  AND (lab1_assigned = :lab_id OR lab2_assigned = :lab_id)
                  AND assignment_id != :assignment_id
            ");
            $query_verificar_manual->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
        } else {
            $query_verificar_manual = $pdo->prepare("
                SELECT assignment_id 
                FROM manual_schedule_assignments 
                WHERE schedule_day = :schedule_day 
                  AND ((:start_time < end_time AND :end_time > start_time))
                  AND classroom_id = :aula_id
                  AND assignment_id != :assignment_id
            ");
            $query_verificar_manual->bindParam(':aula_id', $aula_id, PDO::PARAM_INT);
        }

        $query_verificar_manual->bindParam(':schedule_day',   $schedule_day,   PDO::PARAM_STR);
        $query_verificar_manual->bindParam(':start_time',     $start_time,     PDO::PARAM_STR);
        $query_verificar_manual->bindParam(':end_time',       $end_time,       PDO::PARAM_STR);
        $query_verificar_manual->bindParam(':assignment_id',  $assignment_id,  PDO::PARAM_INT);
        $query_verificar_manual->execute();

        if ($query_verificar_manual->rowCount() > 0) {
            echo json_encode([
                'status'  => 'error', 
                'message' => 'El espacio seleccionado ya está ocupado en el horario indicado.'
            ]);
            $pdo->rollBack();
            exit;
        }

        if ($tipo_espacio === 'Laboratorio') {
            $query_verificar_schedule = $pdo->prepare("
                SELECT assignment_id 
                FROM schedule_assignments 
                WHERE schedule_day = :schedule_day 
                  AND ((:start_time < end_time AND :end_time > start_time))
                  AND lab_id = :lab_id
                  AND assignment_id != :assignment_id
            ");
            $query_verificar_schedule->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
        } else {
            $query_verificar_schedule = $pdo->prepare("
                SELECT assignment_id 
                FROM schedule_assignments 
                WHERE schedule_day = :schedule_day 
                  AND ((:start_time < end_time AND :end_time > start_time))
                  AND classroom_id = :aula_id
                  AND assignment_id != :assignment_id
            ");
            $query_verificar_schedule->bindParam(':aula_id', $aula_id, PDO::PARAM_INT);
        }

        $query_verificar_schedule->bindParam(':schedule_day',   $schedule_day,   PDO::PARAM_STR);
        $query_verificar_schedule->bindParam(':start_time',     $start_time,     PDO::PARAM_STR);
        $query_verificar_schedule->bindParam(':end_time',       $end_time,       PDO::PARAM_STR);
        $query_verificar_schedule->bindParam(':assignment_id',  $assignment_id,  PDO::PARAM_INT);
        $query_verificar_schedule->execute();

        if ($query_verificar_schedule->rowCount() > 0) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'El espacio seleccionado ya está ocupado en el horario indicado (en schedule_assignments).'
            ]);
            $pdo->rollBack();
            exit;
        }

        if ($teacher_id !== null) {
            $query_teacher_conflict_manual = $pdo->prepare("
                SELECT assignment_id 
                FROM manual_schedule_assignments 
                WHERE teacher_id = :teacher_id 
                  AND schedule_day = :schedule_day 
                  AND ((:start_time < end_time AND :end_time > start_time))
                  AND estado = 'activo'
                  AND assignment_id != :assignment_id
            ");
            $query_teacher_conflict_manual->bindParam(':teacher_id',    $teacher_id,    PDO::PARAM_INT);
            $query_teacher_conflict_manual->bindParam(':schedule_day',  $schedule_day,  PDO::PARAM_STR);
            $query_teacher_conflict_manual->bindParam(':start_time',    $start_time,    PDO::PARAM_STR);
            $query_teacher_conflict_manual->bindParam(':end_time',      $end_time,      PDO::PARAM_STR);
            $query_teacher_conflict_manual->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
            $query_teacher_conflict_manual->execute();

            if ($query_teacher_conflict_manual->rowCount() > 0) {
                echo json_encode([
                    'status'  => 'error', 
                    'message' => 'El profesor ya tiene una asignación en el horario seleccionado.'
                ]);
                $pdo->rollBack();
                exit;
            }
        }

        if ($teacher_id !== null) {
            $query_teacher_conflict_schedule = $pdo->prepare("
                SELECT assignment_id 
                FROM schedule_assignments 
                WHERE teacher_id = :teacher_id 
                  AND schedule_day = :schedule_day 
                  AND ((:start_time < end_time AND :end_time > start_time))
                  AND estado = 'activo'
                  AND assignment_id != :assignment_id
            ");
            $query_teacher_conflict_schedule->bindParam(':teacher_id',    $teacher_id,    PDO::PARAM_INT);
            $query_teacher_conflict_schedule->bindParam(':schedule_day',  $schedule_day,  PDO::PARAM_STR);
            $query_teacher_conflict_schedule->bindParam(':start_time',    $start_time,    PDO::PARAM_STR);
            $query_teacher_conflict_schedule->bindParam(':end_time',      $end_time,      PDO::PARAM_STR);
            $query_teacher_conflict_schedule->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
            $query_teacher_conflict_schedule->execute();

            if ($query_teacher_conflict_schedule->rowCount() > 0) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'El profesor ya tiene una asignación en el horario seleccionado (en schedule_assignments).'
                ]);
                $pdo->rollBack();
                exit;
            }
        }

        $query_weekly_hours = $pdo->prepare("
            SELECT weekly_hours 
            FROM subjects 
            WHERE subject_id = :subject_id
        ");
        $query_weekly_hours->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
        $query_weekly_hours->execute();
        $subject = $query_weekly_hours->fetch(PDO::FETCH_ASSOC);

        if (!$subject) {
            echo json_encode(['status' => 'error', 'message' => 'La materia no existe.']);
            $pdo->rollBack();
            exit;
        }

        $weekly_hours = (float) $subject['weekly_hours'];

        $start          = new DateTime($start_time);
        $end            = new DateTime($end_time);
        $interval       = $start->diff($end);
        $duration_hours = (int)$interval->h + ($interval->i / 60) + ($interval->s / 3600);

        if ($assignment_id) { 
            $query_current_hours_manual = $pdo->prepare("
                SELECT start_time, end_time 
                FROM manual_schedule_assignments 
                WHERE subject_id = :subject_id 
                  AND group_id   = :group_id
                  AND assignment_id != :assignment_id 
                  AND estado = 'activo'
            ");
            $query_current_hours_manual->bindParam(':subject_id',    $subject_id,    PDO::PARAM_INT);
            $query_current_hours_manual->bindParam(':group_id',      $group_id,      PDO::PARAM_INT);
            $query_current_hours_manual->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        } else {
            $query_current_hours_manual = $pdo->prepare("
                SELECT start_time, end_time 
                FROM manual_schedule_assignments 
                WHERE subject_id = :subject_id 
                  AND group_id   = :group_id
                  AND estado = 'activo'
            ");
            $query_current_hours_manual->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $query_current_hours_manual->bindParam(':group_id',   $group_id,   PDO::PARAM_INT);
        }

        $query_current_hours_manual->execute();

        $total_assigned_hours_manual = 0;
        while ($row = $query_current_hours_manual->fetch(PDO::FETCH_ASSOC)) {
            $s = new DateTime($row['start_time']);
            $e = new DateTime($row['end_time']);
            $diff = $s->diff($e);
            $hours = (int)$diff->h + ($diff->i / 60) + ($diff->s / 3600);
            $total_assigned_hours_manual += $hours;
        }

        $new_total_manual = $total_assigned_hours_manual + $duration_hours;
        if ($new_total_manual > $weekly_hours) {
            echo json_encode([
                'status'  => 'error', 
                'message' => 'La asignación excede las horas semanales permitidas (manual_schedule_assignments).'
            ]);
            $pdo->rollBack();
            exit;
        }

        if ($assignment_id) { 
            $query_current_hours_schedule = $pdo->prepare("
                SELECT start_time, end_time 
                FROM schedule_assignments 
                WHERE subject_id = :subject_id 
                  AND group_id   = :group_id
                  AND assignment_id != :assignment_id 
                  AND estado = 'activo'
            ");
            $query_current_hours_schedule->bindParam(':subject_id',    $subject_id,    PDO::PARAM_INT);
            $query_current_hours_schedule->bindParam(':group_id',      $group_id,      PDO::PARAM_INT);
            $query_current_hours_schedule->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        } else {
            $query_current_hours_schedule = $pdo->prepare("
                SELECT start_time, end_time 
                FROM schedule_assignments 
                WHERE subject_id = :subject_id 
                  AND group_id   = :group_id
                  AND estado = 'activo'
            ");
            $query_current_hours_schedule->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $query_current_hours_schedule->bindParam(':group_id',   $group_id,   PDO::PARAM_INT);
        }

        $query_current_hours_schedule->execute();

        $total_assigned_hours_schedule = 0;
        while ($row = $query_current_hours_schedule->fetch(PDO::FETCH_ASSOC)) {
            $s = new DateTime($row['start_time']);
            $e = new DateTime($row['end_time']);
            $diff = $s->diff($e);
            $hours = (int)$diff->h + ($diff->i / 60) + ($diff->s / 3600);
            $total_assigned_hours_schedule += $hours;
        }

        $new_total_schedule = $total_assigned_hours_schedule + $duration_hours;
        if ($new_total_schedule > $weekly_hours) {
            echo json_encode([
                'status'  => 'error', 
                'message' => 'La asignación excede las horas semanales permitidas (schedule_assignments).'
            ]);
            $pdo->rollBack();
            exit;
        }

        if ($assignment_id) { 
            if ($tipo_espacio === 'Laboratorio') {
                $sentencia_actualizar_manual = $pdo->prepare("
                    UPDATE manual_schedule_assignments 
                    SET subject_id = :subject_id, 
                        teacher_id = :teacher_id,
                        start_time = :start_time, 
                        end_time   = :end_time, 
                        schedule_day = :schedule_day, 
                        fyh_actualizacion = :fyh_actualizacion, 
                        lab1_assigned = :lab_id, 
                        estado = 'activo'
                    WHERE assignment_id = :assignment_id
                ");
                $sentencia_actualizar_manual->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
            } else {
                $sentencia_actualizar_manual = $pdo->prepare("
                    UPDATE manual_schedule_assignments 
                    SET subject_id = :subject_id, 
                        teacher_id = :teacher_id,
                        start_time = :start_time, 
                        end_time   = :end_time, 
                        schedule_day = :schedule_day, 
                        fyh_actualizacion = :fyh_actualizacion, 
                        classroom_id = :aula_id, 
                        estado = 'activo'
                    WHERE assignment_id = :assignment_id
                ");
                $sentencia_actualizar_manual->bindParam(':aula_id', $aula_id, PDO::PARAM_INT);
            }

            $sentencia_actualizar_manual->bindParam(':subject_id',        $subject_id, PDO::PARAM_INT);
            if ($teacher_id === null) {
                $sentencia_actualizar_manual->bindValue(':teacher_id', null, PDO::PARAM_NULL);
            } else {
                $sentencia_actualizar_manual->bindValue(':teacher_id', $teacher_id, PDO::PARAM_INT);
            }
            $sentencia_actualizar_manual->bindParam(':start_time',        $start_time, PDO::PARAM_STR);
            $sentencia_actualizar_manual->bindParam(':end_time',          $end_time,   PDO::PARAM_STR);
            $sentencia_actualizar_manual->bindParam(':schedule_day',      $schedule_day, PDO::PARAM_STR);
            $sentencia_actualizar_manual->bindParam(':fyh_actualizacion', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $sentencia_actualizar_manual->bindParam(':assignment_id',     $assignment_id, PDO::PARAM_INT);
            $sentencia_actualizar_manual->execute();
        } else { 
            $sentencia_insertar_manual = $pdo->prepare("
                INSERT INTO manual_schedule_assignments 
                (subject_id, teacher_id, group_id, start_time, end_time, schedule_day, fyh_creacion, lab1_assigned, classroom_id, tipo_espacio, estado)
                VALUES (:subject_id, :teacher_id, :group_id, :start_time, :end_time, :schedule_day, :fyh_creacion, :lab_id, :aula_id, :tipo_espacio, 'activo')
            ");
            $sentencia_insertar_manual->bindParam(':subject_id',   $subject_id,   PDO::PARAM_INT);
            $sentencia_insertar_manual->bindParam(':group_id',     $group_id,     PDO::PARAM_INT);
            $sentencia_insertar_manual->bindParam(':start_time',   $start_time,   PDO::PARAM_STR);
            $sentencia_insertar_manual->bindParam(':end_time',     $end_time,     PDO::PARAM_STR);
            $sentencia_insertar_manual->bindParam(':schedule_day', $schedule_day, PDO::PARAM_STR);
            $sentencia_insertar_manual->bindParam(':fyh_creacion', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $sentencia_insertar_manual->bindParam(':lab_id',       $lab_id,       PDO::PARAM_INT);
            $sentencia_insertar_manual->bindParam(':aula_id',      $aula_id,      PDO::PARAM_INT);
            $sentencia_insertar_manual->bindParam(':tipo_espacio', $tipo_espacio, PDO::PARAM_STR);

            if ($teacher_id === null) {
                $sentencia_insertar_manual->bindValue(':teacher_id', null, PDO::PARAM_NULL);
            } else {
                $sentencia_insertar_manual->bindValue(':teacher_id', $teacher_id, PDO::PARAM_INT);
            }

            $sentencia_insertar_manual->execute();
        }

        if ($assignment_id) {
            if ($tipo_espacio === 'Laboratorio') {
                $sentencia_actualizar_schedule = $pdo->prepare("
                    UPDATE schedule_assignments
                    SET subject_id = :subject_id,
                        teacher_id = :teacher_id,
                        start_time = :start_time,
                        end_time = :end_time,
                        schedule_day = :schedule_day,
                        fyh_actualizacion = :fyh_actualizacion,
                        lab_id = :lab_id,
                        estado = 'activo'
                    WHERE assignment_id = :assignment_id
                ");
                $sentencia_actualizar_schedule->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
            } else {
                $sentencia_actualizar_schedule = $pdo->prepare("
                    UPDATE schedule_assignments
                    SET subject_id = :subject_id,
                        teacher_id = :teacher_id,
                        start_time = :start_time,
                        end_time = :end_time,
                        schedule_day = :schedule_day,
                        fyh_actualizacion = :fyh_actualizacion,
                        classroom_id = :aula_id,
                        estado = 'activo'
                    WHERE assignment_id = :assignment_id
                ");
                $sentencia_actualizar_schedule->bindParam(':aula_id', $aula_id, PDO::PARAM_INT);
            }

            $sentencia_actualizar_schedule->bindParam(':subject_id',        $subject_id, PDO::PARAM_INT);
            if ($teacher_id === null) {
                $sentencia_actualizar_schedule->bindValue(':teacher_id', null, PDO::PARAM_NULL);
            } else {
                $sentencia_actualizar_schedule->bindValue(':teacher_id', $teacher_id, PDO::PARAM_INT);
            }
            $sentencia_actualizar_schedule->bindParam(':start_time',        $start_time, PDO::PARAM_STR);
            $sentencia_actualizar_schedule->bindParam(':end_time',          $end_time,   PDO::PARAM_STR);
            $sentencia_actualizar_schedule->bindParam(':schedule_day',      $schedule_day, PDO::PARAM_STR);
            $sentencia_actualizar_schedule->bindParam(':fyh_actualizacion', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $sentencia_actualizar_schedule->bindParam(':assignment_id',     $assignment_id, PDO::PARAM_INT);
            $sentencia_actualizar_schedule->execute();
        } else {
            $sentencia_insertar_schedule = $pdo->prepare("
                INSERT INTO schedule_assignments
                (subject_id, teacher_id, group_id, start_time, end_time, schedule_day, fyh_creacion, lab_id, classroom_id, tipo_espacio, estado)
                VALUES (:subject_id, :teacher_id, :group_id, :start_time, :end_time, :schedule_day, :fyh_creacion, :lab_id, :aula_id, :tipo_espacio, 'activo')
            ");
            $sentencia_insertar_schedule->bindParam(':subject_id',    $subject_id,    PDO::PARAM_INT);
            $sentencia_insertar_schedule->bindParam(':group_id',      $group_id,      PDO::PARAM_INT);
            $sentencia_insertar_schedule->bindParam(':start_time',    $start_time,    PDO::PARAM_STR);
            $sentencia_insertar_schedule->bindParam(':end_time',      $end_time,      PDO::PARAM_STR);
            $sentencia_insertar_schedule->bindParam(':schedule_day',  $schedule_day,  PDO::PARAM_STR);
            $sentencia_insertar_schedule->bindParam(':fyh_creacion',  date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $sentencia_insertar_schedule->bindParam(':lab_id',        $lab_id,        PDO::PARAM_INT);
            $sentencia_insertar_schedule->bindParam(':aula_id',       $aula_id,       PDO::PARAM_INT);
            $sentencia_insertar_schedule->bindParam(':tipo_espacio',  $tipo_espacio,  PDO::PARAM_STR);

            if ($teacher_id === null) {
                $sentencia_insertar_schedule->bindValue(':teacher_id', null, PDO::PARAM_NULL);
            } else {
                $sentencia_insertar_schedule->bindValue(':teacher_id', $teacher_id, PDO::PARAM_INT);
            }

            $sentencia_insertar_schedule->execute();
        }

        $pdo->commit();

        if (isset($_SESSION['sesion_email'])) {
            $usuario_email = $_SESSION['sesion_email'];
        } else {
            $usuario_email = 'desconocido@dominio.com';
        }

        $accion      = 'Asignación manual de horario';
        $descripcion = "Se asignó la materia ID $subject_id al grupo ID $group_id en el día $schedule_day de $start_time a $end_time.";

        registrarEvento($pdo, $usuario_email, $accion, $descripcion);

        echo json_encode(['status' => 'success', 'message' => 'La asignación se ha guardado correctamente (en ambas tablas).']);
        exit;
    } catch (Exception $exception) {
        $pdo->rollBack();
        error_log("Error en asignacion_manual.php: " . $exception->getMessage());
        
        echo json_encode([
            'status'  => 'error',
            'message' => 'Error al guardar la asignación: ' . $exception->getMessage()
        ]);
        exit;
    }
}
?>
