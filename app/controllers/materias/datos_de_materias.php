<?php
$subject_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$subject_id) {
    echo "ID de materia inválido.";
    exit;
}

$sql_subjects = "SELECT * FROM subjects WHERE subject_id = :subject_id";
$query_subjects = $pdo->prepare($sql_subjects);
$query_subjects->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
$query_subjects->execute();
$subjects = $query_subjects->fetchAll(PDO::FETCH_ASSOC);

if (empty($subjects)) {
    echo "Materia no encontrada.";
    exit;
}

foreach ($subjects as $subject) {
    $subject_name = htmlspecialchars($subject['subject_name']);
    $is_specialization = $subject['is_specialization'];
    $hours_consecutive = $subject['hours_consecutive'];
    $horas_semanales = $subject['weekly_hours'] ?? 0;
    $program_id = $subject['program_id'];
    $term_id = $subject['term_id'];
}
?>
