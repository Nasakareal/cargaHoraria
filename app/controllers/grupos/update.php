<?php

include('../../../app/config.php');

$group_id = $_POST['group_id']; // Cambiado aquí
$grupo = $_POST['grupo'];
$programa_id = $_POST['programa_id'];
$cuatrimestre_id = $_POST['cuatrimestre_id'];
$volumen_grupo = $_POST['volumen_grupo'];

$grupo = mb_strtoupper($grupo, 'UTF-8'); // Asegúrate de que el campo esté en mayúsculas

if ($grupo == "" || $programa_id == "" || $cuatrimestre_id == "" || $volumen_grupo == "") {
    session_start();
    $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id); // Cambiado aquí
    exit;
}

// Preparamos la consulta
$sentencia = $pdo->prepare("UPDATE grupos SET grupo = :grupo, programa_id = :programa_id, cuatrimestre_id = :cuatrimestre_id, volumen_grupo = :volumen_grupo, fyh_actualizacion = NOW() WHERE group_id = :group_id");
$sentencia->bindParam(':grupo', $grupo);
$sentencia->bindParam(':programa_id', $programa_id);
$sentencia->bindParam(':cuatrimestre_id', $cuatrimestre_id);
$sentencia->bindParam(':volumen_grupo', $volumen_grupo);
$sentencia->bindParam(':group_id', $group_id);

try {
    // Ejecutamos la consulta
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se ha actualizado el grupo correctamente";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/grupos"); // Cambiado aquí
        exit; // Asegúrate de salir después de redirigir
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se ha podido actualizar el grupo, posiblemente ya existe.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id); // Cambiado aquí
        exit;
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "Error al actualizar el grupo: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id); // Cambiado aquí
    exit;
}
