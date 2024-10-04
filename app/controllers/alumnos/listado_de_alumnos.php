<?php
// listado_de_alumnos.php

$sql_students = "SELECT 
                   s.student_id,
                   s.student_name AS alumno,
                   g.group_name AS grupo,
                   t.term_name AS cuatrimestre,
                   p.program_name AS programa
               FROM 
                   students s
               JOIN 
                   `groups` g ON s.group_id = g.group_id
               JOIN 
                   terms t ON s.term_id = t.term_id
               JOIN 
                   programs p ON s.program_id = p.program_id
               ORDER BY 
                   s.student_name";

$query_students = $pdo->prepare($sql_students);
$query_students->execute();
$students = $query_students->fetchAll(PDO::FETCH_ASSOC);
