<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/config.php');

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/app/controllers/asignacion_manual/debug.log');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_id = isset($_POST['subject_id']) ? $_POST['subject_id'] : '';
    $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
    $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
    $schedule_day = isset($_POST['schedule_day']) ? $_POST['schedule_day'] : '';
    $group_id = isset($_POST['group_id']) ? $_POST['group_id'] : '';
    $assignment_id = isset($_POST['assignment_id']) ? $_POST['assignment_id'] : '';
    $lab_id = isset($_POST['lab_id']) ? $_POST['lab_id'] : 0;
    $aula_id = isset($_POST['aula_id']) ? $_POST['aula_id'] : 0;
    $tipo_espacio = isset($_POST['tipo_espacio']) ? $_POST['tipo_espacio'] : null;

    if (empty($subject_id) || empty($start_time) || empty($schedule_day) || empty($group_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
        exit;
    }

    if ($tipo_espacio === 'Laboratorio') {
        if (empty($lab_id) || $lab_id == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Debe seleccionar un laboratorio para asignar.']);
            exit;
        }
    } elseif ($tipo_espacio === 'Aula') {
        if (empty($aula_id) || $aula_id == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Debe seleccionar un aula para asignar.']);
            exit;
        }
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
    $end_time = date("H:i:s", strtotime($end_time));

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
        $query_teacher->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $query_teacher->execute();

        $teacher = $query_teacher->fetch(PDO::FETCH_ASSOC);

        if (!$teacher) {
            $teacher_id = null;
        } else {
            $teacher_id = $teacher['teacher_id'];
        }

        if ($tipo_espacio === 'Laboratorio') {
            $query_verificar = $pdo->prepare("
                SELECT assignment_id 
                FROM manual_schedule_assignments 
                WHERE schedule_day = :schedule_day 
                  AND ((:start_time < end_time AND :end_time > start_time))
                  AND (lab1_assigned = :lab_id OR lab2_assigned = :lab_id)
                  AND assignment_id != :assignment_id
            ");
            $query_verificar->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
        } elseif ($tipo_espacio === 'Aula') {
            $query_verificar = $pdo->prepare("
                SELECT assignment_id 
                FROM manual_schedule_assignments 
                WHERE schedule_day = :schedule_day 
                  AND ((:start_time < end_time AND :end_time > start_time))
                  AND classroom_id = :aula_id
                  AND assignment_id != :assignment_id
            ");
            $query_verificar->bindParam(':aula_id', $aula_id, PDO::PARAM_INT);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Tipo de espacio inválido.']);
            $pdo->rollBack();
            exit;
        }

        $query_verificar->bindParam(':schedule_day', $schedule_day, PDO::PARAM_STR);
        $query_verificar->bindParam(':start_time', $start_time, PDO::PARAM_STR);
        $query_verificar->bindParam(':end_time', $end_time, PDO::PARAM_STR);
        $query_verificar->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        $query_verificar->execute();

        if ($query_verificar->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'El horario seleccionado ya está ocupado.']);
            $pdo->rollBack();
            exit;
        }

        $query_weekly_hours = $pdo->prepare("SELECT weekly_hours FROM subjects WHERE subject_id = :subject_id");
        $query_weekly_hours->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
        $query_weekly_hours->execute();
        $subject = $query_weekly_hours->fetch(PDO::FETCH_ASSOC);

        if (!$subject) {
            echo json_encode(['status' => 'error', 'message' => 'La materia no existe.']);
            $pdo->rollBack();
            exit;
        }

        $weekly_hours = (float)$subject['weekly_hours'];

        $start = new DateTime($start_time);
        $end = new DateTime($end_time);
        $interval = $start->diff($end);
        $duration_hours = (int)$interval->h + ($interval->i / 60) + ($interval->s / 3600);

        if ($assignment_id) {
            $query_current_hours = $pdo->prepare("
                SELECT start_time, end_time 
                FROM manual_schedule_assignments 
                WHERE subject_id = :subject_id 
                  AND group_id = :group_id
                  AND assignment_id != :assignment_id 
                  AND estado = 'activo'
            ");
            $query_current_hours->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $query_current_hours->bindParam(':group_id', $group_id, PDO::PARAM_INT);
            $query_current_hours->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        } else {
            $query_current_hours = $pdo->prepare("
                SELECT start_time, end_time 
                FROM manual_schedule_assignments 
                WHERE subject_id = :subject_id 
                  AND group_id = :group_id
                  AND estado = 'activo'
            ");
            $query_current_hours->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $query_current_hours->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        }

        $query_current_hours->execute();

        $total_assigned_hours = 0;

        while ($row = $query_current_hours->fetch(PDO::FETCH_ASSOC)) {
            $s = new DateTime($row['start_time']);
            $e = new DateTime($row['end_time']);
            $diff = $s->diff($e);
            $hours = (int)$diff->h + ($diff->i / 60) + ($diff->s / 3600);
            $total_assigned_hours += $hours;
        }

        $new_total = $total_assigned_hours + $duration_hours;

        if ($new_total > $weekly_hours) {
            echo json_encode(['status' => 'error', 'message' => 'La asignación excede las horas semanales permitidas para la materia en este grupo.']);
            $pdo->rollBack();
            exit;
        }
        if ($assignment_id) { 
            if ($tipo_espacio === 'Laboratorio') {
                $sentencia_actualizar = $pdo->prepare("
                    UPDATE manual_schedule_assignments 
                    SET subject_id = :subject_id, 
                        teacher_id = :teacher_id,
                        start_time = :start_time, 
                        end_time = :end_time, 
                        schedule_day = :schedule_day, 
                        fyh_actualizacion = :fyh_actualizacion, 
                        lab1_assigned = :lab_id, 
                        estado = 'activo'
                    WHERE assignment_id = :assignment_id
                ");
                $sentencia_actualizar->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
            } else {
                $sentencia_actualizar = $pdo->prepare("
                    UPDATE manual_schedule_assignments 
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
                $sentencia_actualizar->bindParam(':aula_id', $aula_id, PDO::PARAM_INT);
            }

            $sentencia_actualizar->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            
            if ($teacher_id === null) {
                $sentencia_actualizar->bindValue(':teacher_id', null, PDO::PARAM_NULL);
            } else {
                $sentencia_actualizar->bindValue(':teacher_id', $teacher_id, PDO::PARAM_INT);
            }

            $sentencia_actualizar->bindParam(':start_time', $start_time, PDO::PARAM_STR);
            $sentencia_actualizar->bindParam(':end_time', $end_time, PDO::PARAM_STR);
            $sentencia_actualizar->bindParam(':schedule_day', $schedule_day, PDO::PARAM_STR);
            $sentencia_actualizar->bindParam(':fyh_actualizacion', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $sentencia_actualizar->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
            $sentencia_actualizar->execute();
        } else { 
            $sentencia_insertar = $pdo->prepare("
                INSERT INTO manual_schedule_assignments 
                (subject_id, teacher_id, group_id, start_time, end_time, schedule_day, fyh_creacion, lab1_assigned, classroom_id, tipo_espacio, estado)
                VALUES (:subject_id, :teacher_id, :group_id, :start_time, :end_time, :schedule_day, :fyh_creacion, :lab_id, :aula_id, :tipo_espacio, 'activo')
            ");
            $sentencia_insertar->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $sentencia_insertar->bindParam(':group_id', $group_id, PDO::PARAM_INT);
            $sentencia_insertar->bindParam(':start_time', $start_time, PDO::PARAM_STR);
            $sentencia_insertar->bindParam(':end_time', $end_time, PDO::PARAM_STR);
            $sentencia_insertar->bindParam(':schedule_day', $schedule_day, PDO::PARAM_STR);
            $sentencia_insertar->bindParam(':fyh_creacion', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $sentencia_insertar->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
            $sentencia_insertar->bindParam(':aula_id', $aula_id, PDO::PARAM_INT);
            $sentencia_insertar->bindParam(':tipo_espacio', $tipo_espacio, PDO::PARAM_STR);

            if ($teacher_id === null) {
                $sentencia_insertar->bindValue(':teacher_id', null, PDO::PARAM_NULL);
            } else {
                $sentencia_insertar->bindValue(':teacher_id', $teacher_id, PDO::PARAM_INT);
            }

            $sentencia_insertar->execute();
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'La asignación se ha guardado correctamente.']);
        exit;
    } catch (Exception $exception) {
        $pdo->rollBack();
        error_log("Error en update.php: " . $exception->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar la asignación.']);
        exit;
    }
}
?>
