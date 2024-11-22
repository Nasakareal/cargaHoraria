<?php
include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$materias = $_POST['materias'];

if (empty($teacher_id) || empty($materias)) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Eliminar las materias seleccionadas del profesor
    $placeholders = implode(',', array_fill(0, count($materias), '?'));
    $sentencia_eliminar = $pdo->prepare("
        DELETE FROM teacher_subjects
        WHERE teacher_id = ? AND subject_id IN ($placeholders)
    ");
    $sentencia_eliminar->execute(array_merge([$teacher_id], $materias));

    // Actualizar la tabla de horarios
    $sentencia_actualizar_horarios = $pdo->prepare("
        UPDATE schedule_assignments
        SET teacher_id = NULL, fyh_actualizacion = NOW()
        WHERE teacher_id = ? AND subject_id IN ($placeholders)
    ");
    $sentencia_actualizar_horarios->execute(array_merge([$teacher_id], $materias));

    $pdo->commit();
    echo json_encode(['success' => 'Materias eliminadas correctamente']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
