<?php

include('../../../app/config.php');
require_once('../../../app/registro_eventos.php');

$program_id = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
if (!$program_id) {
    session_start();
    $_SESSION['mensaje'] = "ID de programa inválido.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/programas");
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario es admin
if (!isset($_SESSION['sesion_rol']) || $_SESSION['sesion_rol'] != 1) {
    $_SESSION['mensaje'] = "No tienes permisos para eliminar programas. Solo los administradores pueden realizar esta acción.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/programas");
    exit();
}

try {
    $sentencia = $pdo->prepare("DELETE FROM programs WHERE program_id = :program_id");
    $sentencia->bindParam(':program_id', $program_id);

    if ($sentencia->execute()) {

        $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';
        $accion = 'Eliminación de programa educativo';
        $descripcion = "Se eliminó el programa con ID $program_id.";

        registrarEvento($pdo, $usuario_email, $accion, $descripcion);

        $_SESSION['mensaje'] = "Se ha eliminado el programa.";
        $_SESSION['icono'] = "success";
    } else {
        throw new Exception("No se ha podido eliminar el programa.");
    }
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al eliminar el programa: " . $e->getMessage();
    $_SESSION['icono'] = "error";
}

header('Location: ' . APP_URL . "/admin/programas");
exit();
