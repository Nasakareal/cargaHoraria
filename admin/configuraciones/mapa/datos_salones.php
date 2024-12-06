<?php
// Datos simulados de los salones
$salones = [
    ["nombre" => "Salón 1", "lat" => 19.726619, "lng" => -101.163095, "capacidad" => 40, "estado" => "Disponible"],
    ["nombre" => "Salón 2", "lat" => 19.726700, "lng" => -101.163200, "capacidad" => 30, "estado" => "Ocupado"],
    ["nombre" => "Salón 3", "lat" => 19.726750, "lng" => -101.163300, "capacidad" => 25, "estado" => "Reservado"],
];

// Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode($salones);
?>
