<?php

include('../../../app/config.php');

$classroom_id = $_POST['classroom_id'];

/* Verificar si el salón está asociado a algún grupo */
$sql_asociaciones = "SELECT * FROM `groups` WHERE estado = '1' AND classroom_id = :classroom_id";
$query_asociaciones = $pdo->prepare($sql_asociaciones);
$query_asociaciones->bindParam(':classroom_id', $classroom_id);
$query_asociaciones->execute();
$asociaciones = $query_asociaciones->fetchAll(PDO::FETCH_ASSOC);
$contador = count($asociaciones);

if ($contador > 0) {
    session_start();
    $_SESSION['mensaje'] = "Este salón está asociado a grupos, no se puede eliminar.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/salones");
    exit;
} else {
    $sentencia = $pdo->prepare("DELETE FROM `classrooms` WHERE classroom_id = :classroom_id");
    $sentencia->bindParam(':classroom_id', $classroom_id);

    try {
        if ($sentencia->execute()) {
            session_start();
            $_SESSION['mensaje'] = "Se ha eliminado el salón correctamente.";
            $_SESSION['icono'] = "success";
            header('Location:' . APP_URL . "/admin/salones");
            exit;
        } else {
            session_start();
            $_SESSION['mensaje'] = "No se ha podido eliminar el salón, comuníquese con el área de IT.";
            $_SESSION['icono'] = "error";
            header('Location:' . APP_URL . "/admin/salones");
            exit;
        }
    } catch (Exception $e) {
        session_start();
        $_SESSION['mensaje'] = "Error al eliminar el salón: " . $e->getMessage();
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/salones");
        exit;
    }
}
