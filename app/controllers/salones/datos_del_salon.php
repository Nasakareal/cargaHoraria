<?php

$classroom_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$classroom_id) {
    header('Location: ' . APP_URL . '/portal/salones');
    exit;
}

$sql_classroom = "
    SELECT c.classroom_name, c.capacity, c.building, c.floor, c.estado 
    FROM classrooms c 
    WHERE c.classroom_id = :classroom_id AND c.estado = 'ACTIVO'
";

$query_classroom = $pdo->prepare($sql_classroom);
$query_classroom->bindParam(':classroom_id', $classroom_id, PDO::PARAM_INT);
$query_classroom->execute();

$classroom_data = $query_classroom->fetch(PDO::FETCH_ASSOC);

if ($classroom_data) {
    $classroom_name = htmlspecialchars($classroom_data['classroom_name'], ENT_QUOTES, 'UTF-8');
    $capacity = htmlspecialchars($classroom_data['capacity'], ENT_QUOTES, 'UTF-8') ?: "Capacidad no encontrada";
    $building = htmlspecialchars($classroom_data['building'], ENT_QUOTES, 'UTF-8') ?: "Edificio no encontrado";
    $floor = htmlspecialchars($classroom_data['floor'], ENT_QUOTES, 'UTF-8') ?: "Planta no encontrada";
    $estado = htmlspecialchars($classroom_data['estado'], ENT_QUOTES, 'UTF-8') ?: "Estado no encontrado";
} else {
    
    $classroom_name = "Salón no encontrado (ID: $classroom_id)";
    $capacity = "Capacidad no encontrada";
    $building = "Edificio no encontrado";
    $floor = "Planta no encontrada";
    $estado = "Estado no encontrado";
}
