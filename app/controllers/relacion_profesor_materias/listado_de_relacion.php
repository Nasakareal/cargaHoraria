<?php

/* Verificar que las variables necesarias estén definidas */
if (!isset($teacher_id)) {
    echo "Error: El ID del profesor no está definido.";
    exit;
}

/* Verificar que el grupo esté definido (opcional si trabajas con grupos) */
if (!isset($group_id)) {
    $group_id = null; // O asignar un valor por defecto
}

/* Cargar materias disponibles para el grupo seleccionado */
$sql_materias_disponibles = "
    SELECT 
        s.subject_id, 
        s.subject_name
    FROM 
        subjects s
    INNER JOIN 
        group_subjects gs ON s.subject_id = gs.subject_id
    WHERE 
        gs.group_id = :group_id
    AND 
        s.subject_id NOT IN (
            SELECT subject_id 
            FROM teacher_subjects 
            WHERE teacher_id = :teacher_id
        )";

$query_materias_disponibles = $pdo->prepare($sql_materias_disponibles);
$query_materias_disponibles->bindParam(':group_id', $group_id, PDO::PARAM_INT);
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
?>
