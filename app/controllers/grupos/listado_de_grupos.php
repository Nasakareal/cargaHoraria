<?php

$sql = "SELECT 
            g.group_id, 
            g.group_name, 
            p.program_name,
            t.term_name,
            g.volume,
            s.shift_name,
            el.level_name
        FROM 
            `groups` g
        LEFT JOIN 
            programs p ON g.program_id = p.program_id
        LEFT JOIN 
            shifts s ON g.turn_id = s.shift_id
        LEFT JOIN 
            terms t ON g.term_id = t.term_id
        LEFT JOIN 
            educational_levels el ON g.group_id = el.group_id";  /* Relacionar con la tabla de niveles educativos */

$stmt = $pdo->prepare($sql);
$stmt->execute();
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($groups)) {
    $groups = [];
}