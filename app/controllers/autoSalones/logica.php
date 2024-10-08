<?php
include('../../app/config.php');

$sql_grupos = "SELECT g.group_id, g.group_name, g.volume AS capacidad_grupo 
               FROM `groups` g
               ORDER BY g.volume DESC"; 
$query_grupos = $pdo->prepare($sql_grupos);
$query_grupos->execute();
$grupos = $query_grupos->fetchAll(PDO::FETCH_ASSOC);

$sql_salones = "SELECT * FROM classrooms WHERE estado = 'activo' ORDER BY capacity ASC";
$query_salones = $pdo->prepare($sql_salones);
$query_salones->execute();
$salones_disponibles = $query_salones->fetchAll(PDO::FETCH_ASSOC);

$salones_asignados = [];

foreach ($grupos as $grupo) {
    $capacidad_grupo = $grupo['capacidad_grupo']; 

    $salon_asignado = null;

    foreach ($salones_disponibles as $salon) {
        if ($salon['capacity'] >= $capacidad_grupo && !in_array($salon['classroom_id'], $salones_asignados)) {
            $sql_verificar = "SELECT COUNT(*) AS ocupado 
                              FROM schedules 
                              WHERE classroom_id = :classroom_id 
                              AND schedule_id IS NOT NULL";

            $query_verificar = $pdo->prepare($sql_verificar);
            $query_verificar->execute(['classroom_id' => $salon['classroom_id']]);
            $resultado = $query_verificar->fetch(PDO::FETCH_ASSOC);

            if ($resultado['ocupado'] == 0) {
                $salon_asignado = $salon['classroom_id'];
                $salones_asignados[] = $salon_asignado;
                break; /* Sale del loop al encontrar un salón adecuado */
            }
        }
    }

    
}
?>
