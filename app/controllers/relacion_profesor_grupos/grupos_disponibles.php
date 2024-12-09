<?php

include('../../config.php');

/* Obtener el ID del profesor */
$teacher_id = filter_input(INPUT_GET, 'teacher_id', FILTER_VALIDATE_INT);

/* Validar el ID del profesor */
if (!$teacher_id) {
    echo "<option value=''>ID de profesor inválido</option>";
    exit;
}

/* Consulta para obtener los grupos relacionados al programa del profesor y sus turnos */
$sql = "
    SELECT DISTINCT g.group_id, g.group_name, s.shift_name
    FROM `groups` g
    INNER JOIN teacher_program_term tpt ON g.program_id = tpt.program_id
    INNER JOIN `shifts` s ON g.turn_id = s.shift_id
    WHERE g.estado = '1'
      AND tpt.teacher_id = :teacher_id
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':teacher_id' => $teacher_id]);
    $grupos_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* Verificar si se encontraron grupos */
    if (!empty($grupos_disponibles)) {
        foreach ($grupos_disponibles as $grupo) {
            echo "<option value='" . htmlspecialchars($grupo['group_id']) . "'>" .
                htmlspecialchars($grupo['group_name']) . " - " .
                htmlspecialchars($grupo['shift_name']) .
                "</option>";
        }
    } else {
        echo "<option value=''>No hay grupos disponibles</option>";
    }
} catch (PDOException $e) {
    echo "<option value=''>Error al cargar grupos</option>";
    error_log("Error en la consulta: " . $e->getMessage());
}
