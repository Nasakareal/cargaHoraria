<?php
include ('../../../app/config.php');


$subject_id = $_POST['subject_id'];


var_dump($subject_id);

$sentencia = $pdo->prepare("DELETE FROM subjects WHERE subject_id = :subject_id");
$sentencia->bindParam(':subject_id', $subject_id);

if ($sentencia->execute()) {
    echo "Eliminación exitosa"; 
    session_start();
    $_SESSION['mensaje'] = "Se ha eliminado la materia";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/materias");
} else {
    echo "Error al eliminar";
    session_start();
    $_SESSION['mensaje'] = "No se ha podido eliminar la materia, comuníquese con el área de IT";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/materias");
}
