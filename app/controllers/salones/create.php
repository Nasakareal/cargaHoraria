<?php

include('../../../app/config.php');

/* Datos del formulario */
$nombre_salon = $_POST['nombre_salon'];
$capacidad = $_POST['capacidad'];
$edificio = $_POST['edificio'];
$planta = $_POST['planta'];

/* Estado inicial del registro */
$estado_de_registro = "ACTIVO";

/* Prepara la sentencia para insertar el salón en la base de datos */
$sentencia = $pdo->prepare('INSERT INTO salones
(nombre_salon, capacidad, edificio, planta, fyh_creacion, estado)
VALUES (:nombre_salon, :capacidad, :edificio, :planta, :fyh_creacion, :estado)');

/* Enlaza los valores a los parámetros de la consulta */
$sentencia->bindParam(':nombre_salon', $nombre_salon);
$sentencia->bindParam(':capacidad', $capacidad);
$sentencia->bindParam(':edificio', $edificio);
$sentencia->bindParam(':planta', $planta);
$sentencia->bindParam(':fyh_creacion', $fechaHora);
$sentencia->bindParam(':estado', $estado_de_registro);

try {
    /* Ejecuta la consulta */
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "El salón se ha registrado con éxito";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/salones");
    } else {
        session_start();
        $_SESSION['mensaje'] = "Error: No se ha podido registrar el salón. Comuníquese con el área de IT.";
        $_SESSION['icono'] = "error";
        ?><script>window.history.back();</script><?php
    }
} catch (Exception $exception) {
    session_start();
    $_SESSION['mensaje'] = "Error: No se ha podido registrar el salón. Por favor, revise los datos ingresados.";
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}
?>
