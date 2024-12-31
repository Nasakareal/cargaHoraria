<?php

$group_id = $_GET['id'] ?? null;

if ($group_id !== null) {
    $queryAulas = $pdo->prepare("
        SELECT 
            g.classroom_assigned, 
            CONCAT(c.classroom_name, '(', RIGHT(c.building, 1), ')') AS aula_nombre
        FROM `groups` g
        INNER JOIN classrooms c ON g.classroom_assigned = c.classroom_id
        WHERE g.group_id = :group_id AND g.classroom_assigned IS NOT NULL
    ");
    $queryAulas->bindParam(':group_id', $group_id, PDO::PARAM_INT);
    $queryAulas->execute();
    $aulas = $queryAulas->fetchAll(PDO::FETCH_ASSOC);
} else {
    $aulas = [];
}

?>
