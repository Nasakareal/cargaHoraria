<?php

$student_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$student_id) {
    echo "ID de alumno inválido.";
    exit;
}

$sql_student = "
    SELECT s.student_id, s.alumno, g.group_name, p.program_name, g.term_id
    FROM students s
    LEFT JOIN groups g ON s.group_id = g.group_id
    LEFT JOIN programs p ON g.program_id = p.program_id
    WHERE s.student_id = :student_id AND s.estado = '1'
";

$query_student = $pdo->prepare($sql_student);
$query_student->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$query_student->execute();

$student_data = $query_student->fetch(PDO::FETCH_ASSOC);

if ($student_data) {
    $alumno = $student_data['alumno'];
    $group_name = $student_data['group_name'] ?? "Grupo no encontrado";
    $program_name = $student_data['program_name'] ?? "Programa no encontrado";
    $selected_term_id = $student_data['term_id'] ?? "Termino no encontrado";
} else {
    echo "Alumno no encontrado.";
    exit;
}
