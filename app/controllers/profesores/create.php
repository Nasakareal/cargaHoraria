<?php
include('../../../app/config.php');

$nombres = $_POST['teacher_name'];
$fecha_creacion = date('Y-m-d H:i:s');
$estado = '1';

/* SSSSHHHHHHH */
if (strcasecmp($nombres, 'Elsa Pato') === 0) {
    header("Location: https://www.youtube.com/watch?v=oMzfh0OJdb8");
    exit;
}

if (strcasecmp($nombres, 'Vania') === 0) {

    session_start();
    $_SESSION['mostrar_flores'] = true;
    header('Location:' . APP_URL . "/admin/autoSalones/ainav.php");
    exit;
}

/* Si no coincide con los nombres especiales, se registra el profesor en la base de datos */
$sentencia = $pdo->prepare('INSERT INTO teachers (teacher_name, fyh_creacion, estado) VALUES (:teacher_name, :fyh_creacion, :estado)');
$sentencia->bindParam(':teacher_name', $nombres);
$sentencia->bindParam(':fyh_creacion', $fecha_creacion);
$sentencia->bindParam(':estado', $estado);

try {
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se ha registrado con éxito el profesor";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/profesores");
        exit;
    } else {
        session_start();
        $_SESSION['mensaje'] = "Error: no se ha podido registrar al profesor, comuníquese con el área de IT";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/profesores");
        exit;
    }
} catch (Exception $exception) {
    session_start();
    $_SESSION['mensaje'] = "Error al registrar: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/profesores");
    exit;
}
