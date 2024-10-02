<?php

$id_programa = $_GET['id'];

$sql_programa = "SELECT * FROM programs WHERE estado = '1' AND program_id = :id_programa";
$query_programa = $pdo->prepare($sql_programa);
$query_programa->bindParam(':id_programa', $id_programa);
$query_programa->execute();
$datos_programa = $query_programa->fetchAll(PDO::FETCH_ASSOC);

if (!empty($datos_programa)) {
    $nombre_programa = $datos_programa[0]['program_name'];
} else {
    $nombre_programa = 'Programa no encontrado';
}
