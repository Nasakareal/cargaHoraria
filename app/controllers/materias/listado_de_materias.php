<?php
$sql_subjects = "SELECT 
s.subject_id,
s.subject_name,
s.is_specialization,
s.hours_consecutive,
s.weekly_hours,
s.class_hours,
s.lab_hours,  
s.lab_id,
s.program_id,
s.term_id
FROM
subjects s";

$query_subjects = $pdo->prepare($sql_subjects);
$query_subjects->execute();
$subjects = $query_subjects->fetchAll(PDO::FETCH_ASSOC);
