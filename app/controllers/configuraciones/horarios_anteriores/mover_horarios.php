<?php
include('../../../config.php');
session_start();

try {
    // Inicia una transacción para que ambas operaciones se realicen de forma atómica
    $pdo->beginTransaction();

    // Inserta los horarios actuales de schedule_assignments en schedule_history
    $sqlInsert = "INSERT INTO schedule_history 
        (schedule_id, subject_id, teacher_id, group_id, classroom_id, lab_id, start_time, end_time, schedule_day, estado, fyh_creacion, fyh_actualizacion, tipo_espacio)
        SELECT schedule_id, subject_id, teacher_id, group_id, classroom_id, lab_id, start_time, end_time, schedule_day, estado, fyh_creacion, fyh_actualizacion, tipo_espacio
        FROM schedule_assignments";
    $pdo->exec($sqlInsert);

    // Limpia la tabla schedule_assignments para el nuevo periodo
    $sqlDelete = "DELETE FROM schedule_assignments";
    $pdo->exec($sqlDelete);

    // Confirma la transacción
    $pdo->commit();

    $_SESSION['mensaje'] = "✅ Horarios guardados en historial correctamente y tabla limpiada.";
    $_SESSION['icono'] = "success";
    header("Location:" . APP_URL . "/admin/configuraciones/horarios");
    exit();
} catch (PDOException $e) {
    // Revierte la transacción si ocurre algún error
    $pdo->rollBack();

    $_SESSION['mensaje'] = "❌ Error al procesar los horarios: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header("Location:" . APP_URL . "/admin/configuraciones/horarios");
    exit();
}
?>
