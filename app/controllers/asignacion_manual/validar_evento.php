<?php
include('../../../app/config.php');

$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
$start = filter_input(INPUT_POST, 'start', FILTER_SANITIZE_STRING);
$end = filter_input(INPUT_POST, 'end', FILTER_SANITIZE_STRING);

if (!$title || !$start) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
    exit;
}

try {
    
    $sql = "SELECT * FROM schedule_assignments 
            WHERE 
                ((start_time < :end_time AND end_time > :start_time) AND schedule_day = :schedule_day)";

    $stmt = $pdo->prepare($sql);
    $schedule_day = date('l', strtotime($start));
    $start_time = date('H:i:s', strtotime($start));
    $end_time = $end ? date('H:i:s', strtotime($end)) : null;

    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->bindParam(':schedule_day', $schedule_day);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'error' => 'Conflicto de horario detectado.']);
    } else {
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
