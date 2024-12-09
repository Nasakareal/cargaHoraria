<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/config.php');


$programa_id = filter_input(INPUT_POST, 'programa_id', FILTER_VALIDATE_INT);


if (!$programa_id) {
    echo "<option value=''>Programa no válido</option>";
    error_log("Error: Programa no válido o no recibido");
    exit;
}


$sql = "
    SELECT g.group_id, g.group_name
    FROM `groups` g
    WHERE g.program_id = :programa_id
    AND g.estado = '1'
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':programa_id' => $programa_id]);

    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    if (empty($grupos)) {
        echo "<option value=''>No se encontraron grupos disponibles para este programa</option>";
        error_log("No se encontraron grupos para el programa_id: " . $programa_id);
    } else {
        
        foreach ($grupos as $grupo) {
            echo "<option value='" . htmlspecialchars($grupo['group_id']) . "'>" . htmlspecialchars($grupo['group_name']) . "</option>";
        }
    }
} catch (PDOException $e) {
    echo "<option value=''>Error en la consulta de grupos</option>";
    error_log("Error en la consulta: " . $e->getMessage());
}
