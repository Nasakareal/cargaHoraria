<?php

include('../../../app/config.php');

$classroom_id = $_POST['classroom_id'];
$classroom_name = $_POST['classroom_name'];
$capacity = $_POST['capacity'];
$building = $_POST['building'];
$floor = $_POST['floor'];

$classroom_name = mb_strtoupper($classroom_name, 'UTF-8'); 

if ($classroom_name == "" || $capacity == "" || $building == "" || $floor == "") {
    session_start();
    $_SESSION['mensaje'] = "Los campos Nombre del salón, Capacidad, Edificio y Planta son obligatorios.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/salones/edit.php?id=" . $classroom_id);
    exit;
}

/* Preparamos la consulta */
$sentencia = $pdo->prepare("UPDATE `classrooms` 
                            SET classroom_name = :classroom_name, 
                                capacity = :capacity, 
                                building = :building, 
                                floor = :floor, 
                                fyh_actualizacion = NOW() 
                            WHERE classroom_id = :classroom_id");

$sentencia->bindParam(':classroom_name', $classroom_name);
$sentencia->bindParam(':capacity', $capacity);
$sentencia->bindParam(':building', $building);
$sentencia->bindParam(':floor', $floor);
$sentencia->bindParam(':classroom_id', $classroom_id);

try {
    
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se ha actualizado el salón correctamente";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/salones");
        exit; 
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se ha podido actualizar el salón, posiblemente ya existe.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/salones/edit.php?id=" . $classroom_id);
        exit;
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "Error al actualizar el salón: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/salones/edit.php?id=" . $classroom_id);
    exit;
}
