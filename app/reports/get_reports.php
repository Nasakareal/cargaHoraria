<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/config.php');

/* Consulta para obtener los reportes no leídos */
$query = "SELECT 
            r.report_id, 
            r.report_message, 
            r.created_at, 
            u.nombres AS user_name 
          FROM reports r
          JOIN usuarios u ON r.user_id = u.id_usuario
          WHERE r.is_read = FALSE 
          ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute();

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);


header('Content-Type: application/json');
echo json_encode($notifications);
