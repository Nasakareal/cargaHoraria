<?php

include('../../../app/config.php');

$id_usuario = $_POST['id_usuario'];
$nombres = $_POST['nombres'];
$rol_id = $_POST['rol_id'];
$email = $_POST['email'];

$password = $_POST['password'];
$password_repet = $_POST['password_repet'];

if($password == ""){
    
        $sentencia = $pdo->prepare("UPDATE usuarios
        SET nombres=:nombres,
        rol_id=:rol_id,
        email=:email,
        fyh_actualizacion=:fyh_actualizacion
        WHERE id_usuario=:id_usuario ");
    
    $sentencia->bindParam(':nombres',$nombres);
    $sentencia->bindParam(':rol_id',$rol_id);
    $sentencia->bindParam(':email',$email);
    $sentencia->bindParam(':fyh_actualizacion',$fechaHora);
    $sentencia->bindParam(':id_usuario',$id_usuario);
    
    try{
        if($sentencia->execute()){
            session_start();
        $_SESSION['mensaje'] = "Se ha actualizado con exito";
        $_SESSION['icono'] = "success";
        header('Location:' .APP_URL."/admin/usuarios");
            }else{
                session_start();
                $_SESSION['mensaje'] = "Error no se ha podido actualizar al usuario, comuniquese con el area de IT";
                $_SESSION['icono'] = "error";
                ?><script>window.history.back();</script><?php
            }
    }catch (Exception $exception){
        session_start();
        $_SESSION['mensaje'] = "El email de este usuario ya existe en la base de datos";
        $_SESSION['icono'] = "error";
        ?><script>window.history.back();</script><?php
    }
    
}else{
    
    if($password == $password_repet){
        //echo "Las contraseñas son iguales";
    
        $sentencia = $pdo->prepare("UPDATE usuarios
        SET nombres=:nombres,
        rol_id=:rol_id,
        email=:email,
        password=:password,
        fyh_actualizacion=:fyh_actualizacion
        WHERE id_usuario=:id_usuario ");
    
    $sentencia->bindParam(':nombres',$nombres);
    $sentencia->bindParam(':rol_id',$rol_id);
    $sentencia->bindParam(':email',$email);
    $sentencia->bindParam(':password',$password);
    $sentencia->bindParam('fyh_actualizacion',$fechaHora);
    $sentencia->bindParam('id_usuario',$id_usuario);
    
    try{
        if($sentencia->execute()){
            session_start();
        $_SESSION['mensaje'] = "Se ha registrado con exito";
        $_SESSION['icono'] = "success";
        header('Location:' .APP_URL."/admin/usuarios");
            }else{
                session_start();
                $_SESSION['mensaje'] = "Error no se ha podido registrar al usuario, comuniquese con el area de IT";
                $_SESSION['icono'] = "error";
                ?><script>window.history.back();</script><?php
            }
    }catch (Exception $exception){
        session_start();
        $_SESSION['mensaje'] = "El email de este usuario ya existe en la base de datos";
        $_SESSION['icono'] = "error";
        ?><script>window.history.back();</script><?php
    }
    }else{
        echo "Las contraseñas no son iguales";
        session_start();
        $_SESSION['mensaje'] = "Las contraseñas introducidas no son iguales";
        $_SESSION['icono'] = "error";
        ?><script>window.history.back();</script><?php
    }
}





