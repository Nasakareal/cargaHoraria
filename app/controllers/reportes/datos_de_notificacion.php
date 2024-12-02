<?php

if (!isset($report_id)) {
    header('Location: ' . APP_URL . '/app/reports/listado_notificaciones.php');
    exit;
}

try {
    
    $query = "SELECT 
                r.report_message, 
                r.created_at, 
                u.nombres AS user_name, 
                r.is_read 
              FROM reports r
              JOIN usuarios u ON r.user_id = u.id_usuario
              WHERE r.report_id = :report_id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':report_id', $report_id, PDO::PARAM_INT);
    $stmt->execute();
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        
        header('Location: ' . APP_URL . '/app/reports/listado_notificaciones.php?error=not_found');
        exit;
    }

    
    if (!$report['is_read']) {
        $updateQuery = "UPDATE reports SET is_read = TRUE WHERE report_id = :report_id";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->bindParam(':report_id', $report_id, PDO::PARAM_INT);
        $updateStmt->execute();
    }
} catch (Exception $e) {
    
    header('Location: ' . APP_URL . '/app/reports/listado_notificaciones.php?error=exception');
    exit;
}
?>
