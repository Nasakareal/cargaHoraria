<?php
include('../../../app/config.php');

$report_id = $_POST['report_id'];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['sesion_rol']) || $_SESSION['sesion_rol'] != 1) {
    $_SESSION['mensaje'] = "No tienes permisos para eliminar notificaciones. Solo los administradores pueden realizar esta acción.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/app/reports/listado_notificaciones.php");
    exit();
}

try {
    
    $sentencia = $pdo->prepare("DELETE FROM reports WHERE report_id = :report_id");
    $sentencia->bindParam(':report_id', $report_id, PDO::PARAM_INT);

    if ($sentencia->execute()) {
        $_SESSION['mensaje'] = "La notificación se ha eliminado correctamente.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/app/reports/listado_notificaciones.php");
        exit();
    } else {
        $_SESSION['mensaje'] = "No se ha podido eliminar la notificación, por favor intente nuevamente.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/app/reports/listado_notificaciones.php");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al eliminar la notificación: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/app/reports/listado_notificaciones.php");
    exit();
}
?>
