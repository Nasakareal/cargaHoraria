<?php
try {
    $sql_grupos_materias_faltantes = "
        SELECT 
            g.group_name AS grupo,
            GROUP_CONCAT(CASE WHEN ts.teacher_id IS NULL THEN s.subject_name END ORDER BY s.subject_name ASC SEPARATOR ', ') AS materias_faltantes,
            COUNT(CASE WHEN ts.teacher_id IS NOT NULL THEN 1 END) AS materias_asignadas,
            COUNT(CASE WHEN ts.teacher_id IS NULL THEN 1 END) AS materias_no_cubiertas,
            COUNT(DISTINCT s.subject_id) AS total_materias
        FROM 
            `groups` g
        LEFT JOIN 
            `group_subjects` gs ON g.group_id = gs.group_id
        LEFT JOIN 
            subjects s ON gs.subject_id = s.subject_id
        LEFT JOIN 
            teacher_subjects ts ON ts.subject_id = s.subject_id AND ts.group_id = g.group_id
        WHERE 
            gs.estado = '1'
        GROUP BY 
            g.group_name";

    $stmt = $pdo->prepare($sql_grupos_materias_faltantes);
    $stmt->execute();
    $grupos_materias_faltantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error al obtener los datos de los grupos: " . $e->getMessage();
    $grupos_materias_faltantes = [];
}
?>
