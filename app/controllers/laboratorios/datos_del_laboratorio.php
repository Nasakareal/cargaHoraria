<?php

$sql_laboratorios = "SELECT 
                        lab_id, 
                        lab_name, 
                        description, 
                        fyh_creacion, 
                        fyh_actualizacion
                     FROM 
                        labs 
                     WHERE 
                        lab_id = :lab_id";

$query_laboratorios = $pdo->prepare($sql_laboratorios);
$query_laboratorios->execute([':lab_id' => $lab_id]);
$laboratorio = $query_laboratorios->fetch(PDO::FETCH_ASSOC);


if (!$laboratorio) {
    echo "Laboratorio no encontrado.";
    exit;
}


$lab_id = $laboratorio['lab_id'];
$lab_name = $laboratorio['lab_name'];
$description = $laboratorio['description'] ?? 'Sin descripción';
$fyh_creacion = $laboratorio['fyh_creacion'] ?? 'Fecha no disponible';
$fyh_actualizacion = $laboratorio['fyh_actualizacion'] ?? 'Fecha no disponible';
?>
