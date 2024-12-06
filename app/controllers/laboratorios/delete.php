<?php

include('../../../app/config.php');

/* Obtener y validar el ID del laboratorio */
$lab_id = filter_input(INPUT_POST, 'lab_id', FILTER_VALIDATE_INT);
if (!$lab_id) {
    session_start();
    $_SESSION['mensaje'] = "ID de laboratorio inválido.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/laboratorios");
    exit();
}

/* Iniciar sesión si no está activa */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Verificar si el usuario tiene permisos de administrador */
if (!isset($_SESSION['sesion_rol']) || $_SESSION['sesion_rol'] != 1) {
    $_SESSION['mensaje'] = "No tienes permisos para eliminar laboratorios. Solo los administradores pueden realizar esta acción.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/laboratorios");
    exit();
}

try {
    /* Preparar la sentencia para eliminar el laboratorio */
    $sentencia = $pdo->prepare("DELETE FROM labs WHERE lab_id = :lab_id");
    $sentencia->bindParam(':lab_id', $lab_id);

    /* Ejecutar la sentencia */
    if ($sentencia->execute()) {
        $_SESSION['mensaje'] = "Se ha eliminado el laboratorio.";
        $_SESSION['icono'] = "success";
    } else {
        throw new Exception("No se ha podido eliminar el laboratorio.");
    }
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al eliminar el laboratorio: " . $e->getMessage();
    $_SESSION['icono'] = "error";
}

/* Redirigir al listado de laboratorios */
header('Location: ' . APP_URL . "/admin/laboratorios");
exit();
