<?php

$sql = "SELECT 
            l.lab_id, 
            l.lab_name, 
            l.fyh_creacion, 
            l.description,
            l.area
        FROM 
            labs l";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$labs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($labs)) {
    $labs = [];
}