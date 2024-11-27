<?php

include('../../../app/config.php');

/* Datos del formulario */
$nombre = $_POST['nombre'];
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'];
$estado = $_POST['estado'];
$fechaHora = date('Y-m-d H:i:s'); // Fecha y hora actual para registro

/* Validar que las fechas sean coherentes */
if (strtotime($fecha_inicio) > strtotime($fecha_fin)) {
    session_start();
    $_SESSION['mensaje'] = "La fecha de inicio no puede ser mayor que la fecha de fin.";
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
    exit;
}

/* Prepara la sentencia para insertar el calendario en la base de datos */
$sentencia = $pdo->prepare('INSERT INTO calendario_escolar
(nombre_cuatrimestre, fecha_inicio, fecha_fin, estado, fyh_creacion)
VALUES (:nombre_cuatrimestre, :fecha_inicio, :fecha_fin, :estado, :fyh_creacion)');

/* Enlaza los valores a los parámetros de la consulta */
$sentencia->bindParam(':nombre_cuatrimestre', $nombre);
$sentencia->bindParam(':fecha_inicio', $fecha_inicio);
$sentencia->bindParam(':fecha_fin', $fecha_fin);
$sentencia->bindParam(':estado', $estado);
$sentencia->bindParam(':fyh_creacion', $fechaHora);

try {
    /* Ejecuta la consulta */
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Calendario registrado con éxito.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/configuraciones/calendarios");
        exit;
    } else {
        session_start();
        $_SESSION['mensaje'] = "Error: No se ha podido registrar el calendario. Comuníquese con el área de IT.";
        $_SESSION['icono'] = "error";
        ?><script>window.history.back();</script><?php
    }
} catch (Exception $exception) {
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error al registrar el calendario: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}
?>
