<?php

include('../../../app/config.php');
require_once('../../../app/registro_eventos.php');

$nombre_programa = $_POST['program_name'];
$area_programa = $_POST['program_area'];

$fecha_creacion = date('Y-m-d H:i:s');
$estado = 'ACTIVO';

$sentencia = $pdo->prepare('INSERT INTO programs (program_name, area, fyh_creacion, estado) VALUES (:program_name, :area, :fyh_creacion, :estado)');

$sentencia->bindParam(':program_name', $nombre_programa);
$sentencia->bindParam(':area', $area_programa);
$sentencia->bindParam(':fyh_creacion', $fecha_creacion);
$sentencia->bindParam(':estado', $estado);

try {
    if ($sentencia->execute()) {
        session_start();

        $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';
        $accion = 'Registro de programa educativo';
        $descripcion = "Se registró el programa '$nombre_programa' con área '$area_programa'.";

        registrarEvento($pdo, $usuario_email, $accion, $descripcion);

        $_SESSION['mensaje'] = "Se ha registrado el programa educativo con su área";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/programas");
        exit; 
    } else {
        session_start();
        $_SESSION['mensaje'] = "Error: no se ha podido registrar el programa, comuníquese con el área de IT";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/programas");
    }
} catch (Exception $exception) {
    session_start();
    $_SESSION['mensaje'] = "Error al registrar: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/programas");
}
