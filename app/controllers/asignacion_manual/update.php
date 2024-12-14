<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
    $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
    $schedule_day = isset($_POST['schedule_day']) ? $_POST['schedule_day'] : '';
    $group_id = isset($_POST['group_id']) ? $_POST['group_id'] : '';
    $subject_id = isset($_POST['subject_id']) ? $_POST['subject_id'] : '';
    $assignment_id = isset($_POST['assignment_id']) ? $_POST['assignment_id'] : '';
    $lab_id = isset($_POST['lab_id']) ? $_POST['lab_id'] : 0;

    if (empty($subject_id) || empty($start_time) || empty($schedule_day) || empty($group_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
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

        
        if ($assignment_id) {
            
            $query = $pdo->prepare("SELECT lab1_assigned, lab2_assigned FROM manual_schedule_assignments WHERE assignment_id = :assignment_id");
            $query->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
            $query->execute();
            $existing_assignment = $query->fetch(PDO::FETCH_ASSOC);

            
            if (empty($existing_assignment['lab1_assigned'])) {
                $lab1_assigned = $lab_id; 
                $lab2_assigned = 0;
            } elseif (empty($existing_assignment['lab2_assigned'])) {
                $lab1_assigned = $existing_assignment['lab1_assigned'];
                $lab2_assigned = $lab_id;
            } else {
                
                echo json_encode(['status' => 'error', 'message' => 'Ambos laboratorios ya están asignados.']);
                exit;
            }

            
            $sentencia_actualizar = $pdo->prepare("UPDATE manual_schedule_assignments 
                SET subject_id = :subject_id, 
                    start_time = :start_time, 
                    end_time = :end_time, 
                    schedule_day = :schedule_day, 
                    fyh_actualizacion = :fyh_actualizacion, 
                    lab1_assigned = :lab1_assigned, 
                    lab2_assigned = :lab2_assigned 
                WHERE assignment_id = :assignment_id");

            $sentencia_actualizar->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $sentencia_actualizar->bindParam(':start_time', $start_time, PDO::PARAM_STR);
            $sentencia_actualizar->bindParam(':end_time', $end_time, PDO::PARAM_STR);
            $sentencia_actualizar->bindParam(':schedule_day', $schedule_day, PDO::PARAM_STR);
            $sentencia_actualizar->bindParam(':fyh_actualizacion', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $sentencia_actualizar->bindParam(':lab1_assigned', $lab1_assigned, PDO::PARAM_INT);
            $sentencia_actualizar->bindParam(':lab2_assigned', $lab2_assigned, PDO::PARAM_INT);
            $sentencia_actualizar->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
            $sentencia_actualizar->execute();

        } else { 
            
            $lab1_assigned = $lab_id;
            $lab2_assigned = 0;

            $sentencia_insertar = $pdo->prepare("INSERT INTO manual_schedule_assignments 
                (subject_id, group_id, start_time, end_time, schedule_day, fyh_creacion, lab1_assigned, lab2_assigned)
                VALUES (:subject_id, :group_id, :start_time, :end_time, :schedule_day, :fyh_creacion, :lab1_assigned, :lab2_assigned)");

            $sentencia_insertar->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $sentencia_insertar->bindParam(':group_id', $group_id, PDO::PARAM_INT);
            $sentencia_insertar->bindParam(':start_time', $start_time, PDO::PARAM_STR);
            $sentencia_insertar->bindParam(':end_time', $end_time, PDO::PARAM_STR);
            $sentencia_insertar->bindParam(':schedule_day', $schedule_day, PDO::PARAM_STR);
            $sentencia_insertar->bindParam(':fyh_creacion', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $sentencia_insertar->bindParam(':lab1_assigned', $lab1_assigned, PDO::PARAM_INT);
            $sentencia_insertar->bindParam(':lab2_assigned', $lab2_assigned, PDO::PARAM_INT);
            $sentencia_insertar->execute();
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'La asignación se ha guardado correctamente.']);
    } catch (Exception $exception) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $exception->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se ha recibido una solicitud POST.']);
}
?>
