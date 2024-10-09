<?php
$sql = "SELECT 
            g.group_id, 
            g.group_name, 
            GROUP_CONCAT(m.subject_name SEPARATOR ', ') AS materias_asignadas
        FROM 
            `groups` g 
        LEFT JOIN 
            `group_subjects` gs ON g.group_id = gs.group_id
        LEFT JOIN 
            `subjects` m ON gs.subject_id = m.subject_id
        GROUP BY 
            g.group_id"; 

$stmt = $pdo->prepare($sql);
$stmt->execute();
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
