<?php

include ('../../../app/config.php');

$group_id = $_POST['group_id'];

/* Verificar si el grupo está asociado a algún estudiante */
$sql_asociaciones = "SELECT * FROM students WHERE estado = '1' AND group_id = :group_id"; 
$query_asociaciones = $pdo->prepare($sql_asociaciones);
$query_asociaciones->bindParam(':group_id', $group_id);
$query_asociaciones->execute();
$asociaciones = $query_asociaciones->fetchAll(PDO::FETCH_ASSOC);
$contador = count($asociaciones); 

if ($contador > 0) {
    session_start();
    $_SESSION['mensaje'] = "Este grupo está asociado a estudiantes, no se puede eliminar.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos");
    exit;
} else {
    $sentencia = $pdo->prepare("DELETE FROM `groups` WHERE group_id = :group_id");
    $sentencia->bindParam(':group_id', $group_id);

    try {
        if ($sentencia->execute()) {
            session_start();
            $_SESSION['mensaje'] = "Se ha eliminado el grupo correctamente.";
            $_SESSION['icono'] = "success";
            header('Location:' . APP_URL . "/admin/grupos");
            exit;
        } else {
            session_start();
            $_SESSION['mensaje'] = "No se ha podido eliminar el grupo, comuníquese con el área de IT.";
            $_SESSION['icono'] = "error";
            header('Location:' . APP_URL . "/admin/grupos");
            exit;
        }
    } catch (Exception $e) {
        session_start();
        $_SESSION['mensaje'] = "Error al eliminar el grupo: " . $e->getMessage();
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/grupos");
        exit;
    }
}
