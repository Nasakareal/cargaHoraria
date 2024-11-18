<?php

include('../../../app/config.php');

$teacher_id = filter_input(INPUT_POST, 'teacher_id', FILTER_VALIDATE_INT);
if (!$teacher_id) {
    session_start();
    $_SESSION['mensaje'] = "ID de profesor inválido.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Verificar si el usuario es admin */
if (!isset($_SESSION['sesion_rol']) || $_SESSION['sesion_rol'] != 1) {
    $_SESSION['mensaje'] = "No tienes permisos para eliminar profesores. Solo los administradores pueden realizar esta acción.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit();
}

try {
    $sentencia = $pdo->prepare("DELETE FROM teachers WHERE teacher_id = :teacher_id");
    $sentencia->bindParam(':teacher_id', $teacher_id);

    if ($sentencia->execute()) {
        $_SESSION['mensaje'] = "Se ha eliminado el profesor.";
        $_SESSION['icono'] = "success";
    } else {
        throw new Exception("No se ha podido eliminar el profesor.");
    }
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al eliminar el profesor: " . $e->getMessage();
    $_SESSION['icono'] = "error";
}

header('Location: ' . APP_URL . "/admin/profesores");
exit();
