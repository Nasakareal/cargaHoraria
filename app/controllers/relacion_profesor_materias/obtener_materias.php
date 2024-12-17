<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/config.php');

$group_id = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);

if (!$group_id) {
    echo "<option value=''>Grupo no válido</option>";
    error_log("Error: Grupo no válido o no recibido");
    exit;
}

$sql = "
    SELECT 
        s.subject_id, 
        s.subject_name, 
        s.weekly_hours 
    FROM 
        group_subjects gs
    JOIN 
        subjects s ON gs.subject_id = s.subject_id
    LEFT JOIN 
        teacher_subjects ts ON s.subject_id = ts.subject_id AND ts.group_id = :group_id
    WHERE 
        gs.group_id = :group_id 
        AND gs.estado = '1'
        AND ts.teacher_id IS NULL
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $group_id]);

    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($materias)) {
        echo "<option value=''>No hay materias disponibles para este grupo</option>";
        error_log("No se encontraron materias disponibles para el group_id: " . $group_id);
    } else {
        foreach ($materias as $materia) {
            echo "<option value='" . htmlspecialchars($materia['subject_id']) . "' data-hours='" . htmlspecialchars($materia['weekly_hours']) . "'>" . htmlspecialchars($materia['subject_name']) . "</option>";
        }
    }
} catch (PDOException $e) {
    echo "<option value=''>Error en la consulta de materias</option>";
    error_log("Error en la consulta: " . $e->getMessage());
}
