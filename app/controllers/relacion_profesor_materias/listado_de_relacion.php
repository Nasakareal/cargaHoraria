<?php
// relacion_materias_profesores.php

// Obtener materias asignadas al profesor
$sql_assigned_subjects = "SELECT 
                            ts.subject_id 
                          FROM 
                            teacher_subjects ts 
                          WHERE 
                            ts.teacher_id = :teacher_id";
$query_assigned_subjects = $pdo->prepare($sql_assigned_subjects);
$query_assigned_subjects->execute(['teacher_id' => $teacher_id]);
$materias_ids_asignadas = $query_assigned_subjects->fetchAll(PDO::FETCH_COLUMN);

// Obtener materias disponibles
$sql_subjects = "SELECT 
                    s.subject_id,
                    s.subject_name
                FROM
                    subjects s";

$query_subjects = $pdo->prepare($sql_subjects);
$query_subjects->execute();
$materias_disponibles = $query_subjects->fetchAll(PDO::FETCH_ASSOC);
?>
