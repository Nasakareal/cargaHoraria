<?php

$sql_grupos = "
    SELECT g.group_id, g.group_name, g.volume AS capacidad_grupo, s.shift_name AS turn, 
           c.classroom_name, c.building 
    FROM `groups` g
    JOIN shifts s ON g.turn_id = s.shift_id
    LEFT JOIN classrooms c ON g.classroom_assigned = c.classroom_id
    WHERE g.volume > 0 
    ORDER BY g.volume DESC";
$query_grupos = $pdo->prepare($sql_grupos);
$query_grupos->execute();
$grupos_con_salones = $query_grupos->fetchAll(PDO::FETCH_ASSOC);
