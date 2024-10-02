<?php

include ('../../../app/config.php');

$id_usuario = $_POST['id_usuario'];

$sentencia = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario=:id_usuario ");

$sentencia->bindParam(':id_usuario', $id_usuario);

    if($sentencia->execute()){
        session_start();
        $_SESSION['mensaje'] = "Se ha eliminado el usuario";
        $_SESSION['icono'] = "success";
        header('Location:'.APP_URL."/admin/usuarios");
    }else{
        session_start();
        $_SESSION['mensaje'] = "No se ha podido eliminar el usuario, comuniquese con el area de IT";
        $_SESSION['icono'] = "error";
        header('Location:'.APP_URL."/admin/usuarios");
    }
