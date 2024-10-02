<?php

$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : null;


$sql_subjects = "SELECT * FROM subjects AS sub WHERE sub.estado = 'activo' AND sub.subject_id = :subject_id";
$query_subjects = $pdo->prepare($sql_subjects);
$query_subjects->bindParam(':subject_id', $subject_id, PDO::PARAM_INT); 
$query_subjects->execute();
$subjects = $query_subjects->fetchAll(PDO::FETCH_ASSOC);

foreach ($subjects as $subject) {
    $subject_name = $subject['subject_name'];
    $is_specialization = $subject['is_specialization'];
    $hours_consecutive = $subject['hours_consecutive'];
    $fyh_creacion = $subject['fyh_creacion'];
    $fyh_actualizacion = $subject['fyh_actualizacion'];
    $estado = $subject['estado'];
}
