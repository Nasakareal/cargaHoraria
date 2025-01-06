<?php
try {
    $sql = "SELECT 
                bp.id, 
                COALESCE(bp.building_name, 'Sin nombre') AS building_name, 
                GROUP_CONCAT(bp.area ORDER BY bp.area ASC SEPARATOR ', ') AS areas, 
                MAX(bp.planta_alta) AS planta_alta, 
                MAX(bp.planta_baja) AS planta_baja
            FROM 
                `building_programs` bp
            GROUP BY 
                bp.id, bp.building_name";

    $stmt = $pdo->prepare($sql);
    
    if (!$stmt) {
        echo "Error al preparar la consulta: ";
        print_r($pdo->errorInfo());
        exit;
    }

    $stmt->execute();

    $buildingPrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($buildingPrograms)) {
        echo "No se encontraron resultados en la consulta.<br>";
        $buildingPrograms = [];
    }
} catch (PDOException $e) {
    echo "Error en la consulta SQL: " . $e->getMessage() . "<br>";
    exit;
}
