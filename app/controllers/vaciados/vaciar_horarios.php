<?php
include('../../config.php');

try {
    /* Inicia la transacción */
    $pdo->beginTransaction();

    /* Elimina todas las filas de la tabla schedule_assignments */
    $stmt = $pdo->prepare("DELETE FROM schedule_assignments");
    $stmt->execute();

    /* Confirma la transacción */
    $pdo->commit();

    /* Redirige al índice con un mensaje de éxito */
    header("Location: ../../../admin/horarios_grupos");
} catch (Exception $e) {
    /* Si ocurre un error, revierte la transacción */
    $pdo->rollBack();
    echo "Error al vaciar la tabla schedule_assignments: " . $e->getMessage();
}
