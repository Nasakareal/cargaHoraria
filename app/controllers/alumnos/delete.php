<?php

include('../../../app/config.php');

$id_alumno = $_POST['student_id']; 

try {
    $sentencia = $pdo->prepare("DELETE FROM students WHERE student_id = :id_alumno");
    $sentencia->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);

    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se ha eliminado el alumno";
        $_SESSION['icono'] = "success";
    } else {
        throw new Exception("No se pudo eliminar el alumno");
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "No se ha podido eliminar el alumno: " . $e->getMessage();
    $_SESSION['icono'] = "error";
}

header('Location: ' . APP_URL . "/admin/alumnos");
?>
