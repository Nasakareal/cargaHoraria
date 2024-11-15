<?php

try {
    
    $sql_datos_grafico = " 
        SELECT 
            COUNT(CASE WHEN gs.subject_id IS NOT NULL THEN 1 END) AS materias_cubiertas,
            COUNT(CASE WHEN gs.subject_id IS NULL THEN 1 END) AS materias_no_cubiertas
        FROM 
            subjects s
        LEFT JOIN 
            group_subjects gs ON s.subject_id = gs.subject_id
        WHERE 
            s.estado = '1'
    ";
    $query_datos_grafico = $pdo->prepare($sql_datos_grafico);
    $query_datos_grafico->execute();
    $datos_grafico = $query_datos_grafico->fetch(PDO::FETCH_ASSOC);

    $materias_cubiertas = $datos_grafico['materias_cubiertas'];
    $materias_no_cubiertas = $datos_grafico['materias_no_cubiertas'];
    $porcentaje_cubiertas = ($materias_cubiertas / ($materias_cubiertas + $materias_no_cubiertas)) * 100;
    $porcentaje_no_cubiertas = 100 - $porcentaje_cubiertas;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
