<?php

include ('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];

$sentencia = $pdo->prepare("DELETE FROM teachers WHERE teacher_id = :teacher_id");
$sentencia->bindParam(':teacher_id', $teacher_id);

if ($sentencia->execute()) {
    session_start();
    $_SESSION['mensaje'] = "Se ha eliminado el profesor";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/profesores");
} else {
    session_start();
    $_SESSION['mensaje'] = "No se ha podido eliminar el profesor, comuníquese con el área de IT";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/profesores");
}
