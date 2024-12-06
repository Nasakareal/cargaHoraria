<?php

include('../../../app/config.php');

/* Datos del formulario */
$nombres = $_POST['nombres'];
$rol_id = $_POST['rol_id'];
$email = $_POST['email'];
$password = $_POST['password'];
$password_repet = $_POST['password_repet'];

/* Verifica que las contraseñas coincidan */
if ($password == $password_repet) {
    /* Hashea la contraseña antes de guardarla en la base de datos */
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    /* Prepara la sentencia para insertar el usuario en la base de datos */
    $sentencia = $pdo->prepare('INSERT INTO usuarios
    (nombres, rol_id, email, password, fyh_creacion, estado)
    VALUES (:nombres, :rol_id, :email, :password, :fyh_creacion, :estado)');

    /* Enlaza los valores a los parámetros de la consulta */
    $sentencia->bindParam(':nombres', $nombres);
    $sentencia->bindParam(':rol_id', $rol_id);
    $sentencia->bindParam(':email', $email);
    $sentencia->bindParam(':password', $hashed_password);
    $sentencia->bindParam(':fyh_creacion', $fechaHora);
    $sentencia->bindParam(':estado', $estado_de_registro);

    try {
        /* Ejecuta la consulta */
        if ($sentencia->execute()) {
            session_start();
            $_SESSION['mensaje'] = "Se ha registrado con éxito";
            $_SESSION['icono'] = "success";
            header('Location:' . APP_URL . "/admin/usuarios");
        } else {
            session_start();
            $_SESSION['mensaje'] = "Error: No se ha podido registrar al usuario. Comuníquese con el área de IT.";
            $_SESSION['icono'] = "error";
            ?><script>window.history.back();</script><?php
        }
    } catch (Exception $exception) {
        session_start();
        $_SESSION['mensaje'] = "El email de este usuario ya existe en la base de datos";
        $_SESSION['icono'] = "error";
        ?><script>window.history.back();</script><?php
    }
} else {
    /* Las contraseñas no coinciden */
    session_start();
    $_SESSION['mensaje'] = "Las contraseñas introducidas no son iguales";
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}
