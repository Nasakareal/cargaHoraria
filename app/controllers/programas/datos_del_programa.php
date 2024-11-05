<?php

/* Validar y obtener el ID del programa */
$program_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$program_id) {
    echo "ID de programa inválido.";
    exit;
}

/* Consultar el nombre del programa */
$sql_programa = "SELECT program_name FROM programs WHERE estado = '1' AND program_id = :program_id";
$query_programa = $pdo->prepare($sql_programa);
$query_programa->bindParam(':program_id', $program_id, PDO::PARAM_INT);
$query_programa->execute();
$datos_programa = $query_programa->fetch(PDO::FETCH_ASSOC);

$nombre_programa = $datos_programa['program_name'] ?? 'Programa no encontrado';
