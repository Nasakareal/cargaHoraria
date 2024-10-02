<?php

include('../../../app/config.php');

$program_id = $_POST['program_id'];

$sentencia = $pdo->prepare("DELETE FROM programs WHERE program_id = :program_id");
$sentencia->bindParam(':program_id', $program_id);

if ($sentencia->execute()) {
    session_start();
    $_SESSION['mensaje'] = "Se ha eliminado el programa";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/programas");
} else {
    session_start();
    $_SESSION['mensaje'] = "No se ha podido eliminar el programa, comuníquese con el área de IT";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/programas");
}
