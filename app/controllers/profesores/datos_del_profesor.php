<?php

$sql_teacher = "
    SELECT 
        t.teacher_name AS nombres, 
        t.es_local,  
        p.program_name AS programa,
        c.term_name AS cuatrimestre
    FROM 
        teachers AS t
    LEFT JOIN 
        teacher_program_term tpt ON tpt.teacher_id = t.teacher_id
    LEFT JOIN 
        programs p ON p.program_id = tpt.program_id
    LEFT JOIN 
        terms c ON c.term_id = tpt.term_id
    WHERE 
        t.teacher_id = :teacher_id";

$query_teacher = $pdo->prepare($sql_teacher);
$query_teacher->bindParam(':teacher_id', $teacher_id);
$query_teacher->execute();
$teacher = $query_teacher->fetch(PDO::FETCH_ASSOC);


if ($teacher) {
    $nombres = $teacher['nombres'];
    $es_local = $teacher['es_local'] == 1 ? 'Local' : 'Foráneo';
    $programa = $teacher['programa'] ?? 'No asignado';
    $cuatrimestre = $teacher['cuatrimestre'] ?? 'No asignado';
}


$sql_materias_asignadas = "
    SELECT 
        s.subject_name, 
        s.weekly_hours
    FROM 
        teacher_subjects ts 
    INNER JOIN 
        subjects s ON ts.subject_id = s.subject_id 
    WHERE 
        ts.teacher_id = :teacher_id";

$query_materias_asignadas = $pdo->prepare($sql_materias_asignadas);
$query_materias_asignadas->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
$query_materias_asignadas->execute();
$materias_asignadas = $query_materias_asignadas->fetchAll(PDO::FETCH_ASSOC);


$materias = [];
$horas_semanales = 0;

foreach ($materias_asignadas as $materia) {
    $materias[] = $materia['subject_name'];
    $horas_semanales += $materia['weekly_hours']; 
}

$materias = !empty($materias) ? implode(', ', $materias) : 'No asignado';
$horas_semanales = $horas_semanales > 0 ? $horas_semanales : 'No disponible';
