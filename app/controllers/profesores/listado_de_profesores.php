<?php

$sql_teachers = "SELECT 
<<<<<<< HEAD
                    t.teacher_id,          -- Asegúrate de seleccionar el id
                    t.teacher_name AS profesor,
                    s.subject_name AS materia,
                    ts.weekly_hours AS horas_semanales
=======
                    t.teacher_id, 
                    t.teacher_name AS profesor,
                    GROUP_CONCAT(s.subject_name SEPARATOR ', ') AS materias,  
                    SUM(ts.weekly_hours) AS horas_semanales                    
>>>>>>> 09dfda8 (descagada)
                 FROM
                    teachers t
                 LEFT JOIN
                    teacher_subjects ts ON t.teacher_id = ts.teacher_id
                 LEFT JOIN
<<<<<<< HEAD
                    subjects s ON ts.subject_id = s.subject_id";
=======
                    subjects s ON ts.subject_id = s.subject_id
                 GROUP BY
                    t.teacher_id";  
>>>>>>> 09dfda8 (descagada)

$query_teachers = $pdo->prepare($sql_teachers);
$query_teachers->execute();
$teachers = $query_teachers->fetchAll(PDO::FETCH_ASSOC);
<<<<<<< HEAD
=======

>>>>>>> 09dfda8 (descagada)
