<?php
include('../../../app/config.php');
require_once('../../../app/registro_eventos.php');

$group_id = $_POST['group_id'];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['sesion_rol']) || $_SESSION['sesion_rol'] != 1) {
    $_SESSION['mensaje'] = "No tienes permisos para eliminar grupos. Solo los administradores pueden realizar esta acción.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos");
    exit();
}

try {
    $queryGroupName = $pdo->prepare("SELECT group_name FROM `groups` WHERE group_id = :group_id");
    $queryGroupName->bindParam(':group_id', $group_id);
    $queryGroupName->execute();
    $group_name = $queryGroupName->fetchColumn();

    $stmt1 = $pdo->prepare("DELETE FROM `educational_levels` WHERE group_id = :group_id");
    $stmt1->bindParam(':group_id', $group_id);
    $stmt1->execute();

    $stmt2 = $pdo->prepare("DELETE FROM `schedule_assignments` WHERE group_id = :group_id");
    $stmt2->bindParam(':group_id', $group_id);
    $stmt2->execute();

    $stmt3 = $pdo->prepare("DELETE FROM `group_subjects` WHERE group_id = :group_id");
    $stmt3->bindParam(':group_id', $group_id);
    $stmt3->execute();

    $stmt4 = $pdo->prepare("DELETE FROM `groups` WHERE group_id = :group_id");
    $stmt4->bindParam(':group_id', $group_id);
    
    if ($stmt4->execute()) {
        $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';
        $accion = 'Eliminación de grupo';
        $descripcion = "Se eliminó el grupo '$group_name' con ID $group_id y sus registros relacionados.";
        registrarEvento($pdo, $usuario_email, $accion, $descripcion);

        $_SESSION['mensaje'] = "El grupo se ha eliminado correctamente.";
        $_SESSION['icono'] = "success";
    } else {
        $_SESSION['mensaje'] = "No se ha podido eliminar el grupo, por favor intente nuevamente.";
        $_SESSION['icono'] = "error";
    }

    header('Location:' . APP_URL . "/admin/grupos");
    exit();

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al eliminar el grupo: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos");
    exit();
}
