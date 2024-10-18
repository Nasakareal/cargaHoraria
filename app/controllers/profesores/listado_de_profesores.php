<?php

$sql_teachers = "
    SELECT 
        t.teacher_id,
        t.teacher_name AS profesor,
        t.es_local,  -- Nueva columna que indica si es local o foráneo
        GROUP_CONCAT(s.subject_name SEPARATOR ', ') AS materias,
        SUM(s.weekly_hours) AS horas_semanales,
        GROUP_CONCAT(DISTINCT p.program_name SEPARATOR ', ') AS programas,
        GROUP_CONCAT(DISTINCT pt.term_name SEPARATOR ', ') AS cuatrimestres
    FROM
        teachers t
    LEFT JOIN
        teacher_subjects ts ON t.teacher_id = ts.teacher_id
    LEFT JOIN
        subjects s ON ts.subject_id = s.subject_id
    LEFT JOIN
        program_term_subjects pts ON ts.subject_id = pts.subject_id
    LEFT JOIN
        programs p ON pts.program_id = p.program_id
    LEFT JOIN
        terms pt ON pts.term_id = pt.term_id
    GROUP BY
        t.teacher_id";

$query_teachers = $pdo->prepare($sql_teachers);
$query_teachers->execute();
$teachers = $query_teachers->fetchAll(PDO::FETCH_ASSOC);
