<?php

$materias = [];
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $group_id = $_GET['id'];
    $queryMaterias = $pdo->prepare("
        SELECT m.subject_id, m.subject_name 
        FROM subjects m 
        INNER JOIN group_subjects gs ON m.subject_id = gs.subject_id
        WHERE gs.group_id = :group_id
    ");
    $queryMaterias->bindParam(':group_id', $group_id, PDO::PARAM_INT);
    $queryMaterias->execute();
    $materias = $queryMaterias->fetchAll(PDO::FETCH_ASSOC);
}