<?php

include('../../../app/config.php');

$nombre_programa = $_POST['program_name'];

$fecha_creacion = date('Y-m-d H:i:s');
$estado = '1';

$sentencia = $pdo->prepare('INSERT INTO programs (program_name, fyh_creacion, estado) VALUES (:program_name, :fyh_creacion, :estado)');

/* Víncula las variables */
$sentencia->bindParam(':program_name', $nombre_programa);
$sentencia->bindParam(':fyh_creacion', $fecha_creacion);
$sentencia->bindParam(':estado', $estado);

try {
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se ha registrado el programa educativo";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/programas");
        exit; 
    } else {
        session_start();
        $_SESSION['mensaje'] = "Error: no se ha podido registrar el programa, comuníquese con el area de IT";
        $_SESSION['icono'] = "error";
        ?><script>window.history.back();</script><?php
    }
} catch (Exception $exception) {
    session_start();
    $_SESSION['mensaje'] = "Error al registrar: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}
