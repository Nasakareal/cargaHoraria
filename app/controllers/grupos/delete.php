<?php
include('../../../app/config.php');

$group_id = $_POST['group_id'];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario es admin
if (!isset($_SESSION['sesion_rol']) || $_SESSION['sesion_rol'] != 1) {
    $_SESSION['mensaje'] = "No tienes permisos para eliminar grupos. Solo los administradores pueden realizar esta acción.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos");
    exit();
}

try {
    /* Eliminar los registros relacionados en la tabla `educational_levels` */
    $sentencia_niveles = $pdo->prepare("DELETE FROM `educational_levels` WHERE group_id = :group_id");
    $sentencia_niveles->bindParam(':group_id', $group_id);
    $sentencia_niveles->execute();

    /* Ahora eliminar el grupo */
    $sentencia = $pdo->prepare("DELETE FROM `groups` WHERE group_id = :group_id");
    $sentencia->bindParam(':group_id', $group_id);

    if ($sentencia->execute()) {
        $_SESSION['mensaje'] = "El grupo se ha eliminado correctamente.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/grupos");
        exit();
    } else {
        $_SESSION['mensaje'] = "No se ha podido eliminar el grupo, por favor intente nuevamente.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/grupos");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al eliminar el grupo: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos");
    exit();
}
