<?php

include('../../../app/config.php');

$term_id = $_POST['term_id'];

$sql_terms = "SELECT * FROM terms WHERE term_id = '$term_id' ";
$query_terms = $pdo->prepare($sql_terms);
$query_terms->execute();
$terms = $query_terms->fetchAll(PDO::FETCH_ASSOC);
$contador = 0;

foreach ($terms as $term) {
    $contador++;

}

    session_start();
    $_SESSION['mensaje'] = "Existe este cuatrimestre en grupos, no se puede eliminar";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/portal/cuatrimestres");

    $sentencia = $pdo->prepare("DELETE FROM terms WHERE term_id=:term_id ");

    $sentencia->bindParam('term_id', $term_id);


    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se ha eliminado el cuatrimestre";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/portal/cuatrimestres");
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se ha podido eliminar el cuatrimestre, comuniquese con el area de IT";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/portal/cuatrimestres");
    }

