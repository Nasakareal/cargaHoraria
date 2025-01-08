<?php
include('../../../app/config.php');
require_once('../../../app/registro_eventos.php');}

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
    // Obtener el nombre del grupo antes de eliminar
    $queryGroupName = $pdo->prepare("SELECT group_name FROM `groups` WHERE group_id = :group_id");
    $queryGroupName->bindParam(':group_id', $group_id);
    $queryGroupName->execute();
    $group_name = $queryGroupName->fetchColumn();

    /* Eliminar los registros relacionados en la tabla `educational_levels` */
    $sentencia_niveles = $pdo->prepare("DELETE FROM `educational_levels` WHERE group_id = :group_id");
    $sentencia_niveles->bindParam(':group_id', $group_id);
    $sentencia_niveles->execute();

    /* Ahora eliminar el grupo */
    $sentencia = $pdo->prepare("DELETE FROM `groups` WHERE group_id = :group_id");
    $sentencia->bindParam(':group_id', $group_id);

    if ($sentencia->execute()) {
        
        $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';}
        $accion = 'Eliminación de grupo';
        $descripcion = "Se eliminó el grupo '$group_name' con ID $group_id y sus registros relacionados.";

        registrarEvento($pdo, $usuario_email, $accion, $descripcion);}

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
