<?php
$sql = "SELECT 
            g.group_id, 
            g.group_name, 
            p.program_name AS programa, 
            g.period, 
            g.year, 
            g.volume 
        FROM 
            `groups` g 
        LEFT JOIN 
            programs p ON g.program_id = p.program_id";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
