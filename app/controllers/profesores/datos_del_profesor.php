<?php

$sql_teacher = "
    SELECT 
        t.teacher_name AS nombres, 
        GROUP_CONCAT(s.subject_name SEPARATOR ', ') AS materias,
        SUM(s.weekly_hours) AS total_horas, -- Sumar las horas semanales aquí
        GROUP_CONCAT(DISTINCT p.program_name SEPARATOR ', ') AS programas,
        GROUP_CONCAT(DISTINCT pt.term_name SEPARATOR ', ') AS cuatrimestres,
        t.fyh_creacion,
        t.estado
    FROM 
        teachers AS t
    LEFT JOIN 
        teacher_subjects AS ts ON ts.teacher_id = t.teacher_id
    LEFT JOIN 
        subjects AS s ON ts.subject_id = s.subject_id
    LEFT JOIN 
        program_term_subjects pts ON ts.subject_id = pts.subject_id
    LEFT JOIN 
        programs p ON pts.program_id = p.program_id
    LEFT JOIN 
        terms pt ON pts.term_id = pt.term_id
    WHERE 
        t.teacher_id = :teacher_id
    GROUP BY 
        t.teacher_id";


$query_teacher = $pdo->prepare($sql_teacher);
$query_teacher->bindParam(':teacher_id', $teacher_id);
$query_teacher->execute();
$teacher = $query_teacher->fetch(PDO::FETCH_ASSOC);


if ($teacher) {
    $nombres = $teacher['nombres'];
    $materias = $teacher['materias'] ?: 'No asignadas';
    $horas_semanales = $teacher['total_horas'] ?: 0;
    $programa = $teacher['programas'] ?: 'No asignado';
    $cuatrimestre = $teacher['cuatrimestres'] ?: 'No asignado';
    $fyh_creacion = $teacher['fyh_creacion'];
    $estado = $teacher['estado'];
}
