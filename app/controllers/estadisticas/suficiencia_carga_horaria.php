<?php

// Consulta para obtener estadísticas
$query = "
    SELECT 
        p.program_name AS programa, -- Nombre del programa educativo
        COUNT(DISTINCT g.group_id) AS grupos, -- Número de grupos asociados al programa
        COUNT(DISTINCT s.subject_id) AS asignaturas, -- Número de asignaturas en el programa
        SUM(s.weekly_hours) AS horas_totales_asignatura, -- Total de horas semanales de las asignaturas
        SUM(s.weekly_hours * 15) AS numero_horas_totales, -- Total de horas en el período (15 semanas)
        ROUND(SUM(s.weekly_hours) / 15, 2) AS numero_hsm -- Promedio de horas por semana
    FROM programs p
    LEFT JOIN `groups` g ON g.program_id = p.program_id -- Relación con los grupos
    INNER JOIN group_subjects gs ON gs.group_id = g.group_id -- Relación con las materias de los grupos
    INNER JOIN subjects s ON s.subject_id = gs.subject_id -- Relación con las materias
    LEFT JOIN schedule_assignments sa ON sa.group_id = g.group_id -- Relación con asignaciones de horario
    GROUP BY p.program_id -- Agrupar por programa
";

// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($query);
$stmt->execute();

// Obtener las estadísticas como un arreglo asociativo
$estadisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);

