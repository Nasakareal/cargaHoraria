<?php


/* Obtener el ID del profesor */
$teacher_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

/* Validar el ID del profesor */
if (!$teacher_id) {
    echo "<option value=''>ID de profesor inválido</option>";
    exit;
}

/* Consulta para obtener los grupos relacionados con el profesor */
$sql = "
    SELECT DISTINCT g.group_id, g.group_name
    FROM `groups` g
    INNER JOIN teacher_subjects ts ON g.group_id = ts.group_id
    WHERE ts.teacher_id = :teacher_id
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
    $stmt->execute();
    $grupos_relacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* Verificar si se encontraron grupos */
    if (!empty($grupos_relacionados)) {
        foreach ($grupos_relacionados as $grupo) {
            echo "<option value='" . htmlspecialchars($grupo['group_id']) . "'>" . htmlspecialchars($grupo['group_name']) . "</option>";
        }
    } else {
        echo "<option value=''>No hay grupos relacionados</option>";
    }
} catch (PDOException $e) {
    echo "<option value=''>Error al cargar grupos</option>";
}
