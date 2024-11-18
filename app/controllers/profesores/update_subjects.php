<?php

include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$materia_ids = isset($_POST['materias_asignadas']) ? $_POST['materias_asignadas'] : [];
$grupo_ids = isset($_POST['grupos_asignados']) ? array_filter($_POST['grupos_asignados']) : []; // Filtrar valores vacíos
$total_hours = isset($_POST['total_hours']) ? $_POST['total_hours'] : 0;
$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    // Validar que haya al menos un grupo seleccionado
    if (empty($grupo_ids)) {
        throw new Exception("Debe seleccionar al menos un grupo para asignar materias.");
    }

    // Construir lista segura para `IN`
    $placeholders = implode(',', array_fill(0, count($grupo_ids), '?'));

    // Obtener las materias actualmente asignadas al profesor en los grupos seleccionados
    $sentencia_materias_actuales = $pdo->prepare("
        SELECT DISTINCT ts.subject_id 
        FROM teacher_subjects ts
        INNER JOIN group_subjects gs ON ts.subject_id = gs.subject_id
        WHERE ts.teacher_id = ? AND gs.group_id IN ($placeholders)
    ");
    $sentencia_materias_actuales->execute(array_merge([$teacher_id], $grupo_ids));
    $materias_actuales = $sentencia_materias_actuales->fetchAll(PDO::FETCH_COLUMN);

    // Insertar materias nuevas asociadas al grupo seleccionado
    foreach ($materia_ids as $materia_id) {
        if (!in_array($materia_id, $materias_actuales)) {
            $sentencia_insertar = $pdo->prepare("
                INSERT INTO teacher_subjects (teacher_id, subject_id, fyh_creacion, fyh_actualizacion) 
                VALUES (?, ?, ?, ?)
            ");
            $sentencia_insertar->execute([$teacher_id, $materia_id, $fechaHora, $fechaHora]);
        }
    }

    // Eliminar materias no seleccionadas en los grupos elegidos
    foreach ($materias_actuales as $materia_actual) {
        if (!in_array($materia_actual, $materia_ids)) {
            $sentencia_eliminar = $pdo->prepare("
                DELETE ts 
                FROM teacher_subjects ts
                INNER JOIN group_subjects gs ON ts.subject_id = gs.subject_id
                WHERE ts.teacher_id = ? AND gs.group_id IN ($placeholders) AND ts.subject_id = ?
            ");
            $sentencia_eliminar->execute(array_merge([$teacher_id], $grupo_ids, [$materia_actual]));
        }
    }

    // Actualizar las horas totales del profesor
    $sentencia_actualizar_horas = $pdo->prepare("
        UPDATE teachers SET hours = ?, fyh_actualizacion = ? WHERE teacher_id = ?
    ");
    $sentencia_actualizar_horas->execute([$total_hours, $fechaHora, $teacher_id]);

    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Se ha actualizado con éxito";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
} catch (Exception $exception) {
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
}
