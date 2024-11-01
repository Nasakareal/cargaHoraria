<?php
include('../../config.php');

/* Obtener los datos del POST */
$programa_id = filter_input(INPUT_POST, 'programa_id', FILTER_VALIDATE_INT);
$cuatrimestre_id = filter_input(INPUT_POST, 'cuatrimestre_id', FILTER_VALIDATE_INT);

/* Obtener los IDs de materias ya asignadas */
$assigned_subjects = filter_input(INPUT_POST, 'assigned_subjects', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
if (!$assigned_subjects) {
    $assigned_subjects = [];
} else {
    /* Asegurarse de que todos los IDs sean numéricos */
    $assigned_subjects = array_filter($assigned_subjects, 'is_numeric');
}

/* Verificar que se reciban correctamente los IDs de programa y cuatrimestre */
if (!$programa_id || !$cuatrimestre_id) {
    echo "ID de programa o cuatrimestre no válidos.";
    exit;
}

/* Base de la consulta SQL */
$sql = "
    SELECT s.subject_id, s.subject_name, s.weekly_hours
    FROM subjects s
    INNER JOIN program_term_subjects pts ON s.subject_id = pts.subject_id
    WHERE pts.program_id = :programa_id AND pts.term_id = :cuatrimestre_id
";

$params = [':programa_id' => $programa_id, ':cuatrimestre_id' => $cuatrimestre_id];

/* Excluir las materias ya asignadas */
if (!empty($assigned_subjects)) {
    $placeholders = [];
    foreach ($assigned_subjects as $index => $subject_id) {
        $key = ":subject_id_$index";
        $placeholders[] = $key;
        $params[$key] = $subject_id;
    }
    $placeholders_str = implode(',', $placeholders);
    $sql .= " AND s.subject_id NOT IN ($placeholders_str)";
}

try {
    /* Preparar y ejecutar la consulta */
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    /* Obtener las materias disponibles */
    $materias_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* Verificar si se obtuvieron resultados */
    if (empty($materias_disponibles)) {
        echo "No se encontraron materias.";
    } else {
        /* Generar las opciones */
        foreach ($materias_disponibles as $materia) {
            echo '<option value="' . $materia['subject_id'] . '" data-hours="' . $materia['weekly_hours'] . '">' . htmlspecialchars($materia['subject_name']) . '</option>';
        }
    }
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
}
?>
