<?php

/* Verificar que las variables necesarias estén definidas */
if (!isset($teacher_id)) {
    echo "Error: El ID del profesor no está definido.";
    exit;
}

/* Cargar materias ya asignadas al profesor con el grupo al que pertenecen */
$sql_materias_asignadas = "
    SELECT 
        s.subject_id, 
        s.subject_name, 
        g.group_name
    FROM 
        teacher_subjects ts
    INNER JOIN 
        subjects s ON ts.subject_id = s.subject_id
    INNER JOIN 
        `groups` g ON ts.group_id = g.group_id
    WHERE 
        ts.teacher_id = :teacher_id";

$query_materias_asignadas = $pdo->prepare($sql_materias_asignadas);
$query_materias_asignadas->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
$query_materias_asignadas->execute();
$materias_asignadas = $query_materias_asignadas->fetchAll(PDO::FETCH_ASSOC);
