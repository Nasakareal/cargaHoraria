<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/config.php');

/* Obtener el ID del grupo y del profesor desde la solicitud POST */
$group_id = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
$teacher_id = filter_input(INPUT_POST, 'teacher_id', FILTER_VALIDATE_INT);

/* Verificar que se hayan recibido valores válidos */
if (!$group_id || !$teacher_id) {
    echo "<option value=''>Datos no válidos (grupo o profesor no recibido)</option>";
    error_log("Error: Grupo o profesor no válido o no recibido");
    exit;
}

/* Consulta para obtener las materias asignadas al profesor dentro del grupo */
$sql = "
    SELECT 
        s.subject_id, 
        s.subject_name, 
        s.weekly_hours 
    FROM 
        teacher_subjects ts
    JOIN 
        subjects s ON ts.subject_id = s.subject_id
    WHERE 
        ts.group_id = :group_id 
        AND ts.teacher_id = :teacher_id
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':group_id' => $group_id,
        ':teacher_id' => $teacher_id
    ]);

    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* Verificar si se encontraron materias */
    if (empty($materias)) {
        echo "<option value=''>No hay materias asignadas al profesor para este grupo</option>";
        error_log("No se encontraron materias para el group_id: " . $group_id . " y teacher_id: " . $teacher_id);
    } else {
        /* Generar las opciones del select */
        foreach ($materias as $materia) {
            echo "<option value='" . htmlspecialchars($materia['subject_id']) . "' data-hours='" . htmlspecialchars($materia['weekly_hours']) . "'>" . htmlspecialchars($materia['subject_name']) . "</option>";
        }
    }
} catch (PDOException $e) {
    echo "<option value=''>Error en la consulta de materias</option>";
    error_log("Error en la consulta: " . $e->getMessage());
}
