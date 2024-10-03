<?php

include('../../../app/config.php');

$group_id = $_POST['group_id']; // ID del grupo a actualizar
$grupo = $_POST['group_name']; // Nombre del grupo
$programa_id = $_POST['program_id']; // ID del programa
$cuatrimestre_id = $_POST['term_id']; // ID del cuatrimestre
$volumen_grupo = $_POST['volumen_grupo'] ?? null; // Permitir que este campo sea opcional

$grupo = mb_strtoupper($grupo, 'UTF-8'); // Asegúrate de que el campo esté en mayúsculas

if ($grupo == "" || $programa_id == "" || $cuatrimestre_id == "") {
    session_start();
    $_SESSION['mensaje'] = "Los campos Nombre del grupo, Programa y Cuatrimestre son obligatorios.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
    exit;
}

// Preparamos la consulta
$sentencia = $pdo->prepare("UPDATE `groups` SET group_name = :grupo, program_id = :programa_id, term_id = :cuatrimestre_id, fyh_actualizacion = NOW() WHERE group_id = :group_id");
$sentencia->bindParam(':grupo', $grupo);
$sentencia->bindParam(':programa_id', $programa_id);
$sentencia->bindParam(':cuatrimestre_id', $cuatrimestre_id);
$sentencia->bindParam(':group_id', $group_id);

try {
    // Ejecutamos la consulta
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se ha actualizado el grupo correctamente";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/grupos");
        exit; // Asegúrate de salir después de redirigir
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se ha podido actualizar el grupo, posiblemente ya existe.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
        exit;
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "Error al actualizar el grupo: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
    exit;
}
