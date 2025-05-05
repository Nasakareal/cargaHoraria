<?php
try {
    $sql_all = "
    SELECT
        g.group_name AS grupo,
        GROUP_CONCAT(
            CASE WHEN ts.teacher_id IS NULL THEN s.subject_name END
            ORDER BY s.subject_name ASC
            SEPARATOR ', '
        ) AS materias_faltantes,
        SUM(ts.teacher_id IS NOT NULL) AS materias_asignadas,
        SUM(ts.teacher_id IS NULL)     AS materias_no_cubiertas,
        COUNT(s.subject_id)            AS total_materias
    FROM `groups` g
    JOIN   `group_subjects` gs ON gs.group_id   = g.group_id
    JOIN   `subjects`       s  ON s.subject_id  = gs.subject_id
    LEFT JOIN `teacher_subjects` ts
         ON ts.group_id   = g.group_id
        AND ts.subject_id = s.subject_id
    GROUP BY g.group_id, g.group_name
    ";

    $stmt_all = $pdo->prepare($sql_all);
    $stmt_all->execute();
    $grupos_all = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
    $sql_missing = $sql_all . " HAVING materias_no_cubiertas > 0";
    $stmt_miss = $pdo->prepare($sql_missing);
    $stmt_miss->execute();
    $grupos_con_materias_faltantes = $stmt_miss->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "Error al obtener los datos de los grupos: " . $e->getMessage();
    $grupos_all                      = [];
    $grupos_con_materias_faltantes   = [];
}
