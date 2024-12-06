<?php

include('../../config.php');

try {
    /* Inicia la transacción */
    $pdo->beginTransaction();

    /* Elimina todos los registros de la tabla de profesores y sus relaciones en cascada */
    $query = "DELETE FROM `teachers`";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    /* Confirma la transacción */
    $pdo->commit();

    /* Redirige al índice con un mensaje de éxito */
    session_start();
    $_SESSION['mensaje'] = "La tabla de Profesores ha sido vaciada exitosamente.";
    $_SESSION['icono'] = "success";
    header("Location: ../../../admin/profesores/index.php");
} catch (Exception $e) {
    /* Si ocurre un error, revierte la transacción */
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "Error al vaciar la tabla de profesores: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header("Location: ../../../admin/profesores/index.php");
}
