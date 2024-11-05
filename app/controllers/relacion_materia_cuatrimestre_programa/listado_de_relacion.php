<?php
/* Consulta para obtener las relaciones de programas, cuatrimestres y materias */
$sql_relacion = "
    SELECT 
        p.program_name, 
        t.term_name, 
        GROUP_CONCAT(s.subject_name SEPARATOR ', ') AS subjects
    FROM 
        program_term_subjects pts
    INNER JOIN 
        programs p ON pts.program_id = p.program_id
    INNER JOIN 
        terms t ON pts.term_id = t.term_id
    INNER JOIN 
        subjects s ON pts.subject_id = s.subject_id
    GROUP BY 
        p.program_name, t.term_name
    ORDER BY 
        p.program_name, t.term_name";

$query_relacion = $pdo->prepare($sql_relacion);
$query_relacion->execute();
$relations = $query_relacion->fetchAll(PDO::FETCH_ASSOC);