<?php
include('../../../app/config.php');

$building_id = $_POST['building_id'];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['sesion_rol']) || $_SESSION['sesion_rol'] != 1) {
    $_SESSION['mensaje'] = "No tienes permisos para eliminar edificios. Solo los administradores pueden realizar esta acción.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/edificios");
    exit();
}

try {
    $sentencia_programas = $pdo->prepare("DELETE FROM `building_programs` WHERE id = :building_id");
    $sentencia_programas->bindParam(':building_id', $building_id);
    $sentencia_programas->execute();

    $sentencia = $pdo->prepare("DELETE FROM `building_programs` WHERE id = :building_id");
    $sentencia->bindParam(':building_id', $building_id);

    if ($sentencia->execute()) {
        $_SESSION['mensaje'] = "El edificio se ha eliminado correctamente.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/edificios");
        exit();
    } else {
        $_SESSION['mensaje'] = "No se ha podido eliminar el edificio, por favor intente nuevamente.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/edificios");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al eliminar el edificio: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/edificios");
    exit();
}
