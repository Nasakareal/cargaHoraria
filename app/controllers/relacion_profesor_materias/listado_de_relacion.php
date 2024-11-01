<?php
/* Cargar materias disponibles para el programa y cuatrimestre del profesor */
$sql_materias_disponibles = "
    SELECT 
        s.subject_id, 
        s.subject_name
    FROM 
        subjects s
    INNER JOIN 
        program_term_subjects pts ON s.subject_id = pts.subject_id
    WHERE 
        pts.program_id = :programa_id
    AND 
        pts.term_id = :cuatrimestre_id
    AND 
        s.subject_id NOT IN (
            SELECT subject_id 
            FROM teacher_subjects 
            WHERE teacher_id = :teacher_id
        )";

$query_materias_disponibles = $pdo->prepare($sql_materias_disponibles);
$query_materias_disponibles->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
$query_materias_disponibles->bindParam(':cuatrimestre_id', $cuatrimestre_id, PDO::PARAM_INT);
$query_materias_disponibles->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
$query_materias_disponibles->execute();
$materias_disponibles = $query_materias_disponibles->fetchAll(PDO::FETCH_ASSOC);

/* Cargar materias ya asignadas al profesor */
$sql_materias_asignadas = "
    SELECT 
        s.subject_id, 
        s.subject_name
    FROM 
        subjects s
    INNER JOIN 
        teacher_subjects ts ON s.subject_id = ts.subject_id
    WHERE 
        ts.teacher_id = :teacher_id";

$query_materias_asignadas = $pdo->prepare($sql_materias_asignadas);
$query_materias_asignadas->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
$query_materias_asignadas->execute();
$materias_asignadas = $query_materias_asignadas->fetchAll(PDO::FETCH_ASSOC);