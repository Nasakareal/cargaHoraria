<?php
require_once '../../../app/registro_eventos.php';
include('../../../app/config.php');

session_start();

$id_usuario = $_POST['id_usuario'];
$nombres = $_POST['nombres'];
$rol_id = $_POST['rol_id'];
$email = $_POST['email'];
$areas = isset($_POST['areas']) ? $_POST['areas'] : [];
$area = implode(',', $areas);
$password = $_POST['password'];
$password_repet = $_POST['password_repet'];
$fechaHora = date("Y-m-d H:i:s");

try {
    if ($password === "") {
        $sentencia = $pdo->prepare("UPDATE usuarios
            SET nombres = :nombres,
                rol_id = :rol_id,
                email = :email,
                area = :area,
                fyh_actualizacion = :fyh_actualizacion
            WHERE id_usuario = :id_usuario");
        
        $sentencia->bindParam(':nombres', $nombres);
        $sentencia->bindParam(':rol_id', $rol_id);
        $sentencia->bindParam(':email', $email);
        $sentencia->bindParam(':area', $area);
        $sentencia->bindParam(':fyh_actualizacion', $fechaHora);
        $sentencia->bindParam(':id_usuario', $id_usuario);

    } else {
        if ($password === $password_repet) {
            $password_encriptada = password_hash($password, PASSWORD_BCRYPT);

            $sentencia = $pdo->prepare("UPDATE usuarios
                SET nombres = :nombres,
                    rol_id = :rol_id,
                    email = :email,
                    area = :area,
                    password = :password,
                    fyh_actualizacion = :fyh_actualizacion
                WHERE id_usuario = :id_usuario");
            
            $sentencia->bindParam(':nombres', $nombres);
            $sentencia->bindParam(':rol_id', $rol_id);
            $sentencia->bindParam(':email', $email);
            $sentencia->bindParam(':area', $area);
            $sentencia->bindParam(':password', $password_encriptada);
            $sentencia->bindParam(':fyh_actualizacion', $fechaHora);
            $sentencia->bindParam(':id_usuario', $id_usuario);
        } else {
            $_SESSION['mensaje'] = "Las contraseñas introducidas no son iguales";
            $_SESSION['icono'] = "error";
            echo "<script>window.history.back();</script>";
            exit();
        }
    }

    $usuario_email = $_SESSION['sesion_email'];
    $accion = 'Actualización de Usuario';
    $descripcion = "Se actualizó un usuario con ID $id_usuario.";

    registrarEvento($pdo, $usuario_email, $accion, $descripcion);

    if ($sentencia->execute()) {
        $_SESSION['mensaje'] = "Se ha actualizado con éxito";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/usuarios");
        exit();
    } else {
        $_SESSION['mensaje'] = "Error, no se ha podido actualizar al usuario. Comuníquese con el área de IT.";
        $_SESSION['icono'] = "error";
        echo "<script>window.history.back();</script>";
        exit();
    }
} catch (PDOException $exception) {
    if ($exception->getCode() == 23000) {
        $_SESSION['mensaje'] = "El email de este usuario ya existe en la base de datos";
    } else {
        $_SESSION['mensaje'] = "Ocurrió un error inesperado: " . $exception->getMessage();
    }
    $_SESSION['icono'] = "error";
    echo "<script>window.history.back();</script>";
    exit();
}
?>
