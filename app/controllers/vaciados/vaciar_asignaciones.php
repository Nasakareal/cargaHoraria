<?php

include('../../config.php');

try {
    /* Inicia la sesión para mensajes de retroalimentación */
    session_start();

    /* Inicia la transacción */
    $pdo->beginTransaction();

    /* 1. Eliminar las asignaciones de profesores en la tabla schedule_assignments */
    $query1 = "UPDATE `schedule_assignments` SET `teacher_id` = NULL";
    $stmt1 = $pdo->prepare($query1);
    $stmt1->execute();

    /* 2. Eliminar todos los registros de la tabla teacher_subjects */
    $query2 = "DELETE FROM `teacher_subjects`";
    $stmt2 = $pdo->prepare($query2);
    $stmt2->execute();

    /* Confirma la transacción */
    $pdo->commit();

    /* Redirige con un mensaje de éxito */
    $_SESSION['mensaje'] = "Todas las asignaciones de profesores han sido eliminadas exitosamente.";
    $_SESSION['icono'] = "success";
    header("Location: ../../../admin/profesores/index.php");
    exit;

} catch (Exception $e) {
    /* Si ocurre un error, revierte la transacción y muestra un mensaje de error */
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['mensaje'] = "Error al eliminar las asignaciones de profesores: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header("Location: ../../../admin/profesores/index.php");
    exit;
}
