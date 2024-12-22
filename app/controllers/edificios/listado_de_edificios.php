<?php

$sql = "SELECT 
            bp.id, 
            COALESCE(bp.building_name, 'Sin nombre') AS building_name, 
            GROUP_CONCAT(bp.area ORDER BY bp.area ASC SEPARATOR ', ') AS areas, 
            MAX(bp.planta_alta) AS planta_alta, 
            MAX(bp.planta_baja) AS planta_baja
        FROM 
            `building_programs` bp
        GROUP BY 
            bp.building_name";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$buildingPrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($buildingPrograms)) {
    $buildingPrograms = [];
}
