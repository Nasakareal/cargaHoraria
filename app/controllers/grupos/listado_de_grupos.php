<?php

$sql_groups = "SELECT 
                    g.group_id, 
                    g.group_name AS grupo, 
                    p.program_name AS programa,
                    t.term_name AS cuatrimestre, 
                    (SELECT COUNT(*) FROM students s WHERE s.group_id = g.group_id) AS volumen_grupo,
                    g.fyh_creacion AS fecha_creacion,
                    g.fyh_actualizacion AS fecha_actualizacion,
                    g.estado
                FROM 
                    `groups` g  
                JOIN 
                    programs p ON g.program_id = p.program_id
                JOIN 
                    terms t ON g.term_id = t.term_id";

$query_groups = $pdo->prepare($sql_groups);
$query_groups->execute();
$groups = $query_groups->fetchAll(PDO::FETCH_ASSOC);
