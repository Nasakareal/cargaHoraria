<?php
$sql_subjects = "SELECT 
                    s.subject_id,
                    s.subject_name,
                    s.is_specialization,
                    s.hours_consecutive,
                    s.weekly_hours,
                    s.program_id,  -- Agregado
                    s.term_id      -- Agregado
                FROM
                    subjects s";

$query_subjects = $pdo->prepare($sql_subjects);
$query_subjects->execute();
$subjects = $query_subjects->fetchAll(PDO::FETCH_ASSOC);
?>
