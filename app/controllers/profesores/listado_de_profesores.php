<?php

$sql_teachers = "SELECT 
                    t.teacher_id,          -- Asegúrate de seleccionar el id
                    t.teacher_name AS profesor,
                    s.subject_name AS materia,
                    ts.weekly_hours AS horas_semanales
                 FROM
                    teachers t
                 LEFT JOIN
                    teacher_subjects ts ON t.teacher_id = ts.teacher_id
                 LEFT JOIN
                    subjects s ON ts.subject_id = s.subject_id";

$query_teachers = $pdo->prepare($sql_teachers);
$query_teachers->execute();
$teachers = $query_teachers->fetchAll(PDO::FETCH_ASSOC);
