<?php

$sql_materias = "SELECT 
                    s.subject_name, 
                    s.class_hours, 
                    s.lab_hours, 
                    s.lab1_hours, 
                    s.lab2_hours, 
                    s.lab3_hours, 
                    s.max_consecutive_class_hours AS hours_consecutive,
                    p.program_name, 
                    t.term_name 
                 FROM 
                    subjects s 
                 LEFT JOIN 
                    programs p ON s.program_id = p.program_id 
                 LEFT JOIN 
                    terms t ON s.term_id = t.term_id 
                 WHERE 
                    s.subject_id = :subject_id";

$query_materias = $pdo->prepare($sql_materias);
$query_materias->execute([':subject_id' => $subject_id]);
$materia = $query_materias->fetch(PDO::FETCH_ASSOC);

if (!$materia) {
    echo "Materia no encontrada.";
    exit;
}

$subject_name = $materia['subject_name'];
$hours_consecutive = $materia['hours_consecutive'];
$weekly_hours = $materia['class_hours'] + $materia['lab_hours'];
$program_name = $materia['program_name'] ?? 'No asignado';
$term_name = $materia['term_name'] ?? 'No asignado';
$class_hours = $materia['class_hours'];
$lab_hours = $materia['lab_hours'];
$lab1_hours = $materia['lab1_hours'];
$lab2_hours = $materia['lab2_hours'];
$lab3_hours = $materia['lab3_hours'];
