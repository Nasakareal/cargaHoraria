<?php

$sql_building = "
    SELECT 
        bp.id AS building_id,
        COALESCE(bp.building_name, 'Sin nombre') AS building_name,
        MAX(bp.planta_alta) AS planta_alta,
        MAX(bp.planta_baja) AS planta_baja,
        GROUP_CONCAT(DISTINCT bp.area ORDER BY bp.area ASC SEPARATOR ', ') AS areas
    FROM 
        `building_programs` bp
    WHERE 
        bp.building_name = (
            SELECT building_name FROM `building_programs` WHERE id = :building_id LIMIT 1
        )
    GROUP BY 
        bp.building_name
";

$query_building = $pdo->prepare($sql_building);
$query_building->bindParam(':building_id', $building_id, PDO::PARAM_INT);
$query_building->execute();

$building_data = $query_building->fetch(PDO::FETCH_ASSOC);

if ($building_data) {
    $building_name = htmlspecialchars($building_data['building_name'], ENT_QUOTES, 'UTF-8');
    $planta_alta = $building_data['planta_alta'] ? 'Sí' : 'No';
    $planta_baja = $building_data['planta_baja'] ? 'Sí' : 'No';
    $areas = htmlspecialchars($building_data['areas'] ?? "No se encontraron áreas", ENT_QUOTES, 'UTF-8');
} else {
    $building_name = "Edificio no encontrado (ID: $building_id)";
    $planta_alta = "N/A";
    $planta_baja = "N/A";
    $areas = "No se encontraron áreas";
}
?>
