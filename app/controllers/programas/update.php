<?php

include('../../../app/config.php');

$program_id = $_POST['program_id'];
$program_name = $_POST['program_name'];

$program_name = mb_strtoupper($program_name, 'UTF-8');

if ($program_name == "") {
    session_start();
    $_SESSION['mensaje'] = "Tiene que llenar el campo para continuar";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/programas/edit.php?id=" . $program_id);
    exit;
}

/* Preparamos la consulta */
$sentencia = $pdo->prepare("UPDATE programs SET program_name = :program_name, fyh_actualizacion = NOW() WHERE program_id = :program_id");
$sentencia->bindParam(':program_name', $program_name);
$sentencia->bindParam(':program_id', $program_id);

try {
    /* Ejecutamos la consulta */
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se ha actualizado el programa correctamente";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/programas");
        exit; 
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se ha podido actualizar el programa, posiblemente ya existe.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/programas/edit.php?id=" . $program_id);
        exit;
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "Error al actualizar el programa: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/programas/edit.php?id=" . $program_id);
    exit;
}
