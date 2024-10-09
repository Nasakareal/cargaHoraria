<?php
$sql = "SELECT 
            shift_id, 
            shift_name, 
            schedule_details 
        FROM 
            shifts 
        WHERE 
            estado = '1'"; 

$stmt = $pdo->prepare($sql);
$stmt->execute();
$turns = $stmt->fetchAll(PDO::FETCH_ASSOC);