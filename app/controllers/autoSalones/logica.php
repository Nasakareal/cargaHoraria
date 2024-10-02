<?php
include('../../app/config.php');

// Obtener la capacidad deseada (en este caso, se obtendrá de los grupos)
$sql_grupos = "SELECT * FROM groups"; // Asegúrate de que esta tabla contenga la capacidad de los grupos
$query_grupos = $pdo->prepare($sql_grupos);
$query_grupos->execute();
$grupos = $query_grupos->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener salones que cumplen con la capacidad deseada
$sql_salones = "SELECT * FROM classrooms ORDER BY capacity ASC";
$query_salones = $pdo->prepare($sql_salones);
$query_salones->execute();
$salones_disponibles = $query_salones->fetchAll(PDO::FETCH_ASSOC);

// Crear un array para llevar un control de salones ya asignados
$salones_asignados = [];

foreach ($grupos as $grupo) {
    $capacidad_grupo = $grupo['capacity']; // Asegúrate de que esta columna exista en la tabla de grupos
    $salon_asignado = null;

    // Buscar un salón que cumpla con la capacidad del grupo
    foreach ($salones_disponibles as $key => $salon) {
        if ($salon['capacity'] >= $capacidad_grupo && !in_array($salon['id'], $salones_asignados)) {
            $salon_asignado = $salon;
            // Marcar el salón como asignado
            $salones_asignados[] = $salon['id'];
            break; // Salir del loop una vez que se encuentra un salón
        }
    }

    if ($salon_asignado) {
        echo "Grupo: " . $grupo['group_name'] . " se asignó al salón: " . $salon_asignado['classroom_name'] . " (Capacidad: " . $salon_asignado['capacity'] . ")<br>";
        // Aquí puedes agregar lógica para guardar la asignación en la base de datos
    } else {
        echo "Grupo: " . $grupo['group_name'] . " no tiene salón disponible.<br>";
    }
}
?>
