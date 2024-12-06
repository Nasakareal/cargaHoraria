<?php

$sql_subjects = "SELECT 
    s.subject_id,
    s.subject_name,
    s.class_hours,
    s.lab_hours, /* Total de horas en laboratorio */
    s.lab1_hours, /* Horas en el primer laboratorio */
    s.lab2_hours, /* Horas en el segundo laboratorio */
    s.lab3_hours, /* Horas en el tercer laboratorio */
    s.max_consecutive_class_hours AS hours_consecutive,
    s.max_consecutive_lab_hours,
    s.program_id,
    s.term_id
FROM
    subjects s";

$query_subjects = $pdo->prepare($sql_subjects);
$query_subjects->execute();
$subjects = $query_subjects->fetchAll(PDO::FETCH_ASSOC);

