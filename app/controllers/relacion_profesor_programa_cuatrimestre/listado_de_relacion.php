<?php
$sql_relacion = "SELECT 
                    pt.program_id, 
                    pt.term_id, 
                    p.program_name, 
                    t.term_name 
                FROM 
                    teacher_program_term pt 
                JOIN 
                    programs p ON pt.program_id = p.program_id 
                JOIN 
                    terms t ON pt.term_id = t.term_id 
                WHERE 
                    pt.teacher_id = :teacher_id";

$query_relacion = $pdo->prepare($sql_relacion);
$query_relacion->execute(['teacher_id' => $teacher_id]);
$relacion_programas_cuatrimestres = $query_relacion->fetchAll(PDO::FETCH_ASSOC);