<?php
// Consulta para obtener los salones con el nombre concatenado con el último dígito del edificio
$sql_classrooms = "SELECT 
                    c.classroom_id,   
                    CONCAT(c.classroom_name, '-', RIGHT(c.building, 1)) AS nombre_salon,
                    c.capacity AS capacidad,
                    c.building AS edificio,
                    c.floor AS planta,
                    c.estado AS estado,
                    c.fyh_creacion AS fecha_creacion,
                    c.fyh_actualizacion AS fecha_actualizacion
                 FROM
                    classrooms c";

$query_classrooms = $pdo->prepare($sql_classrooms);
$query_classrooms->execute();
$classrooms = $query_classrooms->fetchAll(PDO::FETCH_ASSOC);

?>
