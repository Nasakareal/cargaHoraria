<?php

try {
    
    $sql = "SELECT 
                el.level_id, 
                el.level_name, 
                g.group_name  /* Nombre del grupo asociado al nivel */
            FROM 
                educational_levels el
            LEFT JOIN 
                `groups` g ON el.group_id = g.group_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($levels)) {
        $levels = [];
    }
} catch (Exception $e) {
    echo "Error al obtener los niveles educativos: " . $e->getMessage();
}