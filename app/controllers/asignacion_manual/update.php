<?php
include('../../../../app/config.php');

// Verificar si los datos han sido enviados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Datos enviados
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
    $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
    $schedule_day = isset($_POST['schedule_day']) ? $_POST['schedule_day'] : '';
    $group_id = isset($_POST['group_id']) ? $_POST['group_id'] : '';
    
    // Revisar si los datos llegaron
    file_put_contents("debug.txt", "Datos recibidos: title=$title, start_time=$start_time, end_time=$end_time, schedule_day=$schedule_day, group_id=$group_id\n", FILE_APPEND);

    // Validación de datos
    if (empty($title) || empty($start_time) || empty($end_time) || empty($schedule_day) || empty($group_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
        exit;
    }

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Verificar si ya existe una asignación para este horario y grupo
        $consulta_existente = $pdo->prepare("SELECT assignment_id FROM manual_schedule_assignments 
            WHERE group_id = :group_id 
            AND schedule_day = :schedule_day
            AND start_time = :start_time 
            AND end_time = :end_time");
        $consulta_existente->bindParam(':group_id', $group_id);
        $consulta_existente->bindParam(':schedule_day', $schedule_day);
        $consulta_existente->bindParam(':start_time', $start_time);
        $consulta_existente->bindParam(':end_time', $end_time);
        $consulta_existente->execute();
        
        if ($consulta_existente->rowCount() > 0) {
            // Si ya existe, actualizar
            $sentencia_actualizar = $pdo->prepare("UPDATE manual_schedule_assignments 
                SET subject_name = :title, 
                    start_time = :start_time, 
                    end_time = :end_time, 
                    schedule_day = :schedule_day, 
                    fyh_actualizacion = :fyh_actualizacion
                WHERE group_id = :group_id");
            $sentencia_actualizar->bindParam(':title', $title);
            $sentencia_actualizar->bindParam(':start_time', $start_time);
            $sentencia_actualizar->bindParam(':end_time', $end_time);
            $sentencia_actualizar->bindParam(':schedule_day', $schedule_day);
            $sentencia_actualizar->bindParam(':fyh_actualizacion', date('Y-m-d H:i:s'));
            $sentencia_actualizar->bindParam(':group_id', $group_id);
            $sentencia_actualizar->execute();
        } else {
            // Si no existe, insertar nueva asignación
            $sentencia_insertar = $pdo->prepare("INSERT INTO manual_schedule_assignments 
                (subject_name, group_id, start_time, end_time, schedule_day, fyh_creacion)
                VALUES (:title, :group_id, :start_time, :end_time, :schedule_day, :fyh_creacion)");
            $sentencia_insertar->bindParam(':title', $title);
            $sentencia_insertar->bindParam(':group_id', $group_id);
            $sentencia_insertar->bindParam(':start_time', $start_time);
            $sentencia_insertar->bindParam(':end_time', $end_time);
            $sentencia_insertar->bindParam(':schedule_day', $schedule_day);
            $sentencia_insertar->bindParam(':fyh_creacion', date('Y-m-d H:i:s'));
            $sentencia_insertar->execute();
        }

        // Confirmar cambios en la base de datos
        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'La asignación se ha guardado correctamente.']);
    } catch (Exception $exception) {
        // Si ocurre un error, revertir la transacción
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $exception->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se ha recibido una solicitud POST.']);
}
?>
