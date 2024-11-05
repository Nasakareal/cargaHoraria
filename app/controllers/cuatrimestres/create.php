<?php

include('../../../app/config.php');

$term_name = $_POST['term_name'];
$term_name = mb_strtoupper($term_name, 'UTF-8');

if ($term_name == "") {
    session_start();
    $_SESSION['mensaje'] = "Tiene que llenar el campo para continuar";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/cuatrimestres");
} else {
    $sentencia = $pdo->prepare("INSERT INTO terms
        ( term_name, fyh_creacion, estado)
VALUES  (:term_name, :fyh_creacion, :estado) ");

    $sentencia->bindParam('term_name', $term_name);
    $sentencia->bindParam('fyh_creacion', $fechaHora);
    $sentencia->bindParam('estado', $estado_de_registro);

    try {
        if ($sentencia->execute()) {
            session_start();
            $_SESSION['mensaje'] = "Se ha registrado el nuevo cuatrimestre";
            $_SESSION['icono'] = "success";
            header('Location:' . APP_URL . "/portal/cuatrimestres");
        } else {
            session_start();
            $_SESSION['mensaje'] = "No se ha podido registrar el nuevo cuatrimestre, comuniquese con el area de IT";
            $_SESSION['icono'] = "error";
            header('Location:' . APP_URL . "/portal/cuatrimestres/create.php");
        }
    } catch (Exception $exception) {
        session_start();
        $_SESSION['mensaje'] = "Este cuatrimestre ya existe";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/portal/cuatrimestres/create.php");
    }


}