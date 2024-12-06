<?php
$sql_teachers = "
    SELECT 
        t.teacher_id,
        t.teacher_name AS profesor,
        COALESCE(pa.program_name, 'Sin programa de adscripción') AS programa_adscripcion,
        GROUP_CONCAT(DISTINCT ptt.program_name SEPARATOR ', ') AS programas,
        GROUP_CONCAT(DISTINCT tm.term_name SEPARATOR ', ') AS cuatrimestres,
        GROUP_CONCAT(DISTINCT s.subject_name SEPARATOR ', ') AS materias,
        t.hours AS horas_semanales
    FROM
        teachers t
    LEFT JOIN
        programs pa ON t.specialization_program_id = pa.program_id
    LEFT JOIN
        teacher_program_term tpt ON t.teacher_id = tpt.teacher_id
    LEFT JOIN
        programs ptt ON tpt.program_id = ptt.program_id
    LEFT JOIN
        terms tm ON tpt.term_id = tm.term_id
    LEFT JOIN
        teacher_subjects ts ON t.teacher_id = ts.teacher_id
    LEFT JOIN
        subjects s ON ts.subject_id = s.subject_id
    GROUP BY
        t.teacher_id";

$query_teachers = $pdo->prepare($sql_teachers);
$query_teachers->execute();
$teachers = $query_teachers->fetchAll(PDO::FETCH_ASSOC);
