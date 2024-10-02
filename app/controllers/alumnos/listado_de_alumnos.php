<?php

$sql_students = "
    SELECT 
        s.student_id,
        s.student_name AS alumno,
        g.group_name AS grupo,
        t.term_name AS cuatrimestre,
        p.program_name AS programa
    FROM
        students s
    LEFT JOIN
        `groups` g ON s.group_id = g.group_id
    LEFT JOIN
        terms t ON s.term_id = t.term_id  -- Cambiado de g.term_id a s.term_id
    LEFT JOIN
        programs p ON s.program_id = p.program_id  -- Cambiado de g.program_id a s.program_id
    WHERE
        s.estado = 1";

$query_students = $pdo->prepare($sql_students);
$query_students->execute();
$students = $query_students->fetchAll(PDO::FETCH_ASSOC);
