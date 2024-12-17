<?php

$queryAulas = $pdo->prepare("
    SELECT DISTINCT 
        g.classroom_assigned, 
        CONCAT(c.classroom_name, '(', RIGHT(c.building, 1), ')') AS aula_nombre
    FROM `groups` g
    INNER JOIN classrooms c ON g.classroom_assigned = c.classroom_id
    WHERE g.classroom_assigned IS NOT NULL
");
$queryAulas->execute();
$aulas = $queryAulas->fetchAll(PDO::FETCH_ASSOC);
