<?php
// Consulta principal para obtener datos del profesor
$sql_teacher = "
    SELECT 
        t.teacher_name AS nombres, 
        t.es_local,  
        tpt.program_id AS programa_id,  
        tpt.term_id AS cuatrimestre_id 
    FROM 
        teachers AS t
    LEFT JOIN 
        teacher_program_term AS tpt ON tpt.teacher_id = t.teacher_id
    WHERE 
        t.teacher_id = :teacher_id";

$query_teacher = $pdo->prepare($sql_teacher);
$query_teacher->bindParam(':teacher_id', $teacher_id);
$query_teacher->execute();
$teacher = $query_teacher->fetch(PDO::FETCH_ASSOC);

// Si se encuentran los datos del profesor, los asignamos a variables
if ($teacher) {
    $nombres = $teacher['nombres'];
    $programa_id = $teacher['programa_id'];
    $cuatrimestre_id = $teacher['cuatrimestre_id'];
    $es_local = $teacher['es_local'] == 1 ? 'Local' : 'Foráneo';
}

// Consulta para obtener las materias asignadas al profesor
$sql_materias_asignadas = "
    SELECT 
        s.subject_id, 
        s.subject_name 
    FROM 
        teacher_subjects ts 
    INNER JOIN 
        subjects s ON ts.subject_id = s.subject_id 
    WHERE 
        ts.teacher_id = :teacher_id";

$query_materias_asignadas = $pdo->prepare($sql_materias_asignadas);
$query_materias_asignadas->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
$query_materias_asignadas->execute();
$materias_ids_asignadas = $query_materias_asignadas->fetchAll(PDO::FETCH_ASSOC);