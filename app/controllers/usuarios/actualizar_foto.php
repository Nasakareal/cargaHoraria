<?php

include('../../../app/config.php');

$id_usuario = $_POST['id_usuario'];
$avatar = $_POST['avatar'];
$fechaHora = date("Y-m-d H:i:s");

try {
    
    $sentencia = $pdo->prepare("UPDATE usuarios
        SET foto_perfil=:foto_perfil,
        fyh_actualizacion=:fyh_actualizacion
        WHERE id_usuario=:id_usuario");

    $sentencia->bindParam(':foto_perfil', $avatar);
    $sentencia->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia->bindParam(':id_usuario', $id_usuario);

    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "La foto de perfil se ha actualizado con éxito.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/usuarios");
    } else {
        session_start();
        $_SESSION['mensaje'] = "Error al actualizar la foto de perfil. Comuníquese con el área de IT.";
        $_SESSION['icono'] = "error";
        echo "<script>window.history.back();</script>";
    }
} catch (Exception $exception) {
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error al intentar actualizar la foto de perfil.";
    $_SESSION['icono'] = "error";
    echo "<script>window.history.back();</script>";
}
?>
