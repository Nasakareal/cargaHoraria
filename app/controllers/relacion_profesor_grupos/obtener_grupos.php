<?php
include('../../../app/config.php');


if (!$pdo) {
    echo "<option>Error en la conexión a la base de datos</option>";
    exit;
}

/* Obtener el programa y cuatrimestre desde la solicitud POST */
$programa_id = filter_input(INPUT_POST, 'programa_id', FILTER_VALIDATE_INT);
$cuatrimestre_id = filter_input(INPUT_POST, 'cuatrimestre_id', FILTER_VALIDATE_INT);

/* Validar los datos recibidos */
if (!$programa_id || !$cuatrimestre_id) {
    echo "<option value=''>Programa o cuatrimestre no válido</option>";
    exit;
}

/* Consulta SQL para obtener los grupos */
$query = "SELECT g.group_id, g.group_name 
          FROM `groups` g
          WHERE g.program_id = :programa_id AND g.term_id = :cuatrimestre_id AND g.estado = '1'";

$statement = $pdo->prepare($query);

/* Ejecutar la consulta */
if ($statement->execute([':programa_id' => $programa_id, ':cuatrimestre_id' => $cuatrimestre_id])) {
    $grupos = $statement->fetchAll(PDO::FETCH_ASSOC);

    /* Verificar si se encontraron grupos */
    if ($grupos) {
        /* Generar opciones para cada grupo encontrado */
        foreach ($grupos as $grupo) {
            echo "<option value='" . htmlspecialchars($grupo['group_id']) . "'>" . htmlspecialchars($grupo['group_name']) . "</option>";
        }
    } else {
        /* Si no hay grupos que coincidan con los criterios */
        echo "<option value=''>No hay grupos disponibles para este programa y cuatrimestre</option>";
    }
} else {
    
    $errorInfo = $statement->errorInfo();
    echo "<option>Error en la consulta de grupos: {$errorInfo[2]}</option>";
}