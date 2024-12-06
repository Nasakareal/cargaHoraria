<?php
include('../../config.php');

try {
    /* Inicia la transacción */
    $pdo->beginTransaction();

    /* Elimina filas de todas las tablas que tienen una relación de clave foránea con subjects */
    $pdo->exec("DELETE FROM schedule_assignments WHERE subject_id IS NOT NULL");
    $pdo->exec("DELETE FROM subject_labs WHERE subject_id IS NOT NULL");
    $pdo->exec("DELETE FROM teacher_subjects WHERE subject_id IS NOT NULL");
    $pdo->exec("DELETE FROM program_term_subjects WHERE subject_id IS NOT NULL");

    /* Luego, elimina todas las materias en subjects */
    $stmt = $pdo->prepare("DELETE FROM subjects");
    $stmt->execute();

    /* Confirma la transacción */
    $pdo->commit();

    /* Redirige al índice con un mensaje de éxito */
    header("Location: ../../../admin/materias/index.php");
} catch (Exception $e) {
    /* Si ocurre un error, revierte la transacción */
    $pdo->rollBack();
    echo "Error al vaciar la tabla de materias: " . $e->getMessage();
}
