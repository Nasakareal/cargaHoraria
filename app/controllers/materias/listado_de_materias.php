<?php

$sql_subjects = "SELECT 
    s.subject_id,
    s.subject_name,
    s.weekly_hours,
    s.max_consecutive_class_hours AS hours_consecutive,
    s.program_id,
    s.term_id,
    s.unidades
FROM
    subjects s";

$query_subjects = $pdo->prepare($sql_subjects);
$query_subjects->execute();
$subjects = $query_subjects->fetchAll(PDO::FETCH_ASSOC);

