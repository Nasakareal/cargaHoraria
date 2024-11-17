<?php


/* Obtener el ID del profesor */
$teacher_id = filter_input(INPUT_GET, 'teacher_id', FILTER_VALIDATE_INT);

/* Validar el ID del profesor */
if (!$teacher_id) {
    echo "<option value=''>ID de profesor inválido</option>";
    exit;
}

/* Consulta para obtener los grupos con materias pendientes */
$sql = "
    SELECT DISTINCT g.group_id, g.group_name
    FROM `groups` g
    INNER JOIN group_subjects gs ON g.group_id = gs.group_id
    LEFT JOIN teacher_subjects ts ON gs.subject_id = ts.subject_id
    WHERE g.estado = '1'
      AND g.program_id IN (
          SELECT program_id
          FROM teacher_program_term
          WHERE teacher_id = :teacher_id
      )
      AND ts.teacher_id IS NULL
";

$query = $pdo->prepare($sql);
$query->execute([':teacher_id' => $teacher_id]);
$grupos_disponibles = $query->fetchAll(PDO::FETCH_ASSOC);

/* Verificar si se encontraron grupos */
if (!empty($grupos_disponibles)) {
    foreach ($grupos_disponibles as $grupo) {
        echo "<option value='" . htmlspecialchars($grupo['group_id']) . "'>" . htmlspecialchars($grupo['group_name']) . "</option>";
    }
} else {
    echo "<option value=''>No hay grupos disponibles</option>";
}
