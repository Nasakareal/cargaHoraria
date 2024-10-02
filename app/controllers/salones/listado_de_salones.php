<?php

$sql_classrooms = "SELECT 
                    c.classroom_id,   
                    c.classroom_name AS nombre_salon,
                    c.fyh_creacion AS fecha_creacion,
                    c.fyh_actualizacion AS fecha_actualizacion,
                    c.estado AS estado
                 FROM
                    classrooms c";

$query_classrooms = $pdo->prepare($sql_classrooms);
$query_classrooms->execute();
$classrooms = $query_classrooms->fetchAll(PDO::FETCH_ASSOC);
