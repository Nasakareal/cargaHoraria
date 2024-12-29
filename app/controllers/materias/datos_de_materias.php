<?php

$sql_materias = "SELECT 
                    s.subject_id,
                    s.subject_name, 
                    s.weekly_hours,
                    s.max_consecutive_class_hours AS hours_consecutive,
                    s.fyh_creacion,
                    s.fyh_actualizacion,
                    s.estado,
                    p.program_id,
                    p.program_name, 
                    t.term_id,
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
$weekly_hours = $materia['weekly_hours'];
$program_id = $materia['program_id'];
$program_name = $materia['program_name'] ?? 'No asignado';
$term_id = $materia['term_id'];
$term_name = $materia['term_name'] ?? 'No asignado';
$estado = $materia['estado'];
$fyh_creacion = $materia['fyh_creacion'];
$fyh_actualizacion = $materia['fyh_actualizacion'];
?>
