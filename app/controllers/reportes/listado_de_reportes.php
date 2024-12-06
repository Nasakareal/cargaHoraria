<?php

$query = "SELECT 
            r.report_id, 
            r.report_message, 
            r.created_at, 
            u.nombres AS user_name,
            r.is_read
          FROM reports r
          JOIN usuarios u ON r.user_id = u.id_usuario
          ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
