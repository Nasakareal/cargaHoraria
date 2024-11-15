<?php
try {
    
    $sql_grupos_materias_faltantes = "
        SELECT 
            g.group_name AS grupo,
            GROUP_CONCAT(DISTINCT s.subject_name ORDER BY s.subject_name ASC SEPARATOR ', ') AS materias_faltantes
        FROM 
            `groups` g
        LEFT JOIN 
            `group_subjects` gs ON g.group_id = gs.group_id
        LEFT JOIN 
            subjects s ON gs.subject_id = s.subject_id
        LEFT JOIN 
            teacher_subjects ts ON ts.subject_id = s.subject_id
        WHERE 
            ts.teacher_id IS NULL 
            AND gs.estado = '1'
        GROUP BY 
            g.group_name
        HAVING 
            materias_faltantes IS NOT NULL";

    $stmt = $pdo->prepare($sql_grupos_materias_faltantes);
    $stmt->execute();
    $grupos_materias_faltantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error al obtener los grupos con materias faltantes de profesor: " . $e->getMessage();
    $grupos_materias_faltantes = [];
}
?>
