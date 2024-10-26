<?php

include('../../config.php');

try {
    /* Inicia la transacción */
    $pdo->beginTransaction();

    /* Elimina todos los registros de la tabla de grupos y sus relaciones en cascada */
    $query = "DELETE FROM `groups`";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    /* Confirma la transacción */
    $pdo->commit();

    /* Redirige al índice con un mensaje de éxito */
    header("Location: ../../../admin/grupos/index.php");
} catch (Exception $e) {
    /* Si ocurre un error, revierte la transacción */
    $pdo->rollBack();
    echo "Error al vaciar la tabla de grupos: " . $e->getMessage();
}