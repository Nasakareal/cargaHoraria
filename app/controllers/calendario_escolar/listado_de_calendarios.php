<?php

$sql_calendarios = "SELECT * FROM calendario_escolar WHERE estado = 'ACTIVO' ORDER BY fecha_inicio DESC";
$query_calendarios = $pdo->prepare($sql_calendarios);
$query_calendarios->execute();
$calendarios = $query_calendarios->fetchAll(PDO::FETCH_ASSOC);

?>
