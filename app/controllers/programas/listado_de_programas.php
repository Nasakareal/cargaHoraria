<?php
$sql_programs = "SELECT 
                    p.program_id,
                    p.program_name,
                    p.area,
                    p.fyh_creacion AS fecha_creacion,
                    p.fyh_actualizacion AS fecha_actualizacion,
                    p.estado
                 FROM
                    programs p";

$query_programs = $pdo->prepare($sql_programs);
$query_programs->execute();
$programs = $query_programs->fetchAll(PDO::FETCH_ASSOC);

if (empty($programs)) {
    $programs = [];
}
