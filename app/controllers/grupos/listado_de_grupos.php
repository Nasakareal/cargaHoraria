<?php

$sql = "SELECT 
            g.group_id, 
            g.group_name, 
            p.program_name,   /* Obtener el nombre del programa */
            t.term_name,      /* Obtener el nombre del cuatrimestre */
            g.year, 
            g.volume,
            s.shift_name      /* Obtener el nombre del turno */
        FROM 
            `groups` g
        LEFT JOIN 
            programs p ON g.program_id = p.program_id
        LEFT JOIN 
            shifts s ON g.turn_id = s.shift_id
        LEFT JOIN 
            terms t ON g.term_id = t.term_id";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($groups)) {
    $groups = [];
}
?>
