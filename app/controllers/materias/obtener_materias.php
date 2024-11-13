<?php

$query = "
    SELECT 
        COUNT(DISTINCT group_subjects.subject_id) AS total_materias,
        COUNT(DISTINCT teacher_subjects.subject_id) AS materias_cubiertas
    FROM group_subjects
    LEFT JOIN teacher_subjects ON group_subjects.subject_id = teacher_subjects.subject_id
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$total_materias = (int) $result['total_materias'];
$materias_cubiertas = (int) $result['materias_cubiertas'];
$materias_no_cubiertas = $total_materias - $materias_cubiertas;

$porcentaje_cubiertas = $total_materias > 0 ? round(($materias_cubiertas / $total_materias) * 100, 2) : 0;
$porcentaje_no_cubiertas = $total_materias > 0 ? round(($materias_no_cubiertas / $total_materias) * 100, 2) : 0;