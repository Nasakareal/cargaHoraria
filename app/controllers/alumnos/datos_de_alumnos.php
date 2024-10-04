<?php

// Obtener el ID del estudiante
$student_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$student_id) {
    header('Location: ' . APP_URL . '/admin/alumnos');
    exit;
}

// Consulta para obtener los datos del alumno
$sql_alumno = "SELECT s.student_name, g.group_name, p.program_name, t.term_name 
               FROM students AS s 
               INNER JOIN `groups` AS g ON s.group_id = g.group_id 
               INNER JOIN programs AS p ON s.program_id = p.program_id 
               INNER JOIN terms AS t ON s.term_id = t.term_id 
               WHERE s.student_id = :student_id";

// Preparar la consulta
$query_alumno = $pdo->prepare($sql_alumno);
$query_alumno->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$query_alumno->execute();
$alumno = $query_alumno->fetch(PDO::FETCH_ASSOC);

// Verificar si se obtuvo el alumno
if (!$alumno) {
    header('Location: ' . APP_URL . '/admin/alumnos');
    exit;
}

// Asignar los valores a las variables
$alumno_nombre = $alumno['student_name'];
$group_name = $alumno['group_name'];
$program_name = $alumno['program_name'];
$selected_term_id = $alumno['term_name'];

?>
