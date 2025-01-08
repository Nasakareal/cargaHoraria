<?php
require_once '../../../app/registro_eventos.php';
include ('../../../app/config.php');

$id_usuario = $_POST['id_usuario'];

$sentencia = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario=:id_usuario");
$sentencia->bindParam(':id_usuario', $id_usuario);

if ($sentencia->execute()) {
    session_start();

    $usuario_email = $_SESSION['sesion_email'];
    $accion = 'Eliminación de usuario';
    $descripcion = "Se eliminó al usuario con ID $id_usuario del sistema.";

    registrarEvento($pdo, $usuario_email, $accion, $descripcion);

    $_SESSION['mensaje'] = "Se ha eliminado el usuario";
    $_SESSION['icono'] = "success";
    header('Location:'.APP_URL."/admin/usuarios");
} else {
    session_start();
    $_SESSION['mensaje'] = "No se ha podido eliminar el usuario, comuníquese con el área de IT";
    $_SESSION['icono'] = "error";
    header('Location:'.APP_URL."/admin/usuarios");
}
