<?php

// Consulta para obtener los horarios junto con la información del grupo
$sql = "SELECT 
            g.group_id, 
            g.group_name, 
            s.schedule_day,
            s.start_time,
            s.end_time
        FROM 
            `groups` g 
        LEFT JOIN 
            `schedules` s ON g.group_id = s.group_id
        WHERE 
            g.estado = 'activo'"; 

$stmt = $pdo->prepare($sql);
$stmt->execute();
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC); // Cambiado a $horarios
