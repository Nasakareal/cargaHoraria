<?php

include('../../../app/config.php');

$term_id = $_POST['term_id'];
$term_name = $_POST['term_name'];
$term_name = mb_strtoupper($term_name, 'UTF-8');

if ($term_name == "") {
    session_start();
    $_SESSION['mensaje'] = "Tiene que llenar el campo para continuar";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/cuatrimestres/edit.php?id=" . $term_id);
} else {
    $sentencia = $pdo->prepare("UPDATE terms
SET term_name=:term_name,
    fyh_actualizacion=:fyh_actualizacion
WHERE term_id=:term_id ");

    $sentencia->bindParam('term_name', $term_name);
    $sentencia->bindParam('fyh_actualizacion', $fechaHora);
    $sentencia->bindParam('term_id', $term_id);

    try {
        if ($sentencia->execute()) {
            session_start();
            $_SESSION['mensaje'] = "Se ha actualizado el cuatrimestre";
            $_SESSION['icono'] = "success";
            header('Location:' . APP_URL . "/admin/cuatrimestres");
        } else {
            session_start();
            $_SESSION['mensaje'] = "No se ha podido actualizar el cuatrimestre, comuniquese con el area de IT";
            $_SESSION['icono'] = "error";
            header('Location:' . APP_URL . "/admin/cuatrimestres/edit.php?id=" . $term_id);
        }
    } catch (Exception $exception) {
        session_start();
        $_SESSION['mensaje'] = "Este cuatrimestre ya existe";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/cuatrimestres/edit.php?id=" . $term_id);
    }


}