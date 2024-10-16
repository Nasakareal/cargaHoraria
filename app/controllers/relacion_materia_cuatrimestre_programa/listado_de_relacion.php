<?php

$sql = "SELECT 
            p.program_name,
            t.term_name,
            GROUP_CONCAT(s.subject_name SEPARATOR ', ') AS subjects
        FROM 
            program_term_subjects pts
        JOIN 
            programs p ON pts.program_id = p.program_id
        JOIN 
            terms t ON pts.term_id = t.term_id
        JOIN 
            subjects s ON pts.subject_id = s.subject_id
        GROUP BY 
            p.program_name, t.term_name"; // Agrupar solo por programa y cuatrimestre

$stmt = $pdo->prepare($sql);
$stmt->execute();
$relations = $stmt->fetchAll(PDO::FETCH_ASSOC);
