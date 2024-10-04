<?php
include('../../../app/config.php');

session_start();

$student_name = trim($_POST['student_name']);  // Asegúrate que el nombre del campo en el formulario es 'student_name'
$program_id = $_POST['program_id'];                // Asegúrate que el nombre del campo en el formulario es 'program_id'
$term_id = $_POST['term_id'];               // Asegúrate que el nombre del campo en el formulario es 'term_id'
$group_id = $_POST['group_id'];                     // Asegúrate que el nombre del campo en el formulario es 'group_id'
$fechaHora = date('Y-m-d H:i:s');
$estado_de_registro = 'activo';

/* Verificar que los campos no estén vacíos */
if (empty($student_name) || empty($group_id) || empty($program_id) || empty($term_id)) {
    $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/alumnos/create.php");
    exit();
}

/* Preparar la consulta SQL */
$sentencia = $pdo->prepare("INSERT INTO students
        (student_name, group_id, program_id, term_id, fyh_creacion, estado)
VALUES  (:student_name, :group_id, :program_id, :term_id, :fyh_creacion, :estado)");

$sentencia->bindParam(':student_name', $student_name);
$sentencia->bindParam(':group_id', $group_id);
$sentencia->bindParam(':term_id', $term_id);
$sentencia->bindParam(':program_id', $program_id);
$sentencia->bindParam(':fyh_creacion', $fechaHora);
$sentencia->bindParam(':estado', $estado_de_registro);

try {
    /* Ejecutar la consulta */
    if ($sentencia->execute()) {
        $_SESSION['mensaje'] = "Se ha registrado el nuevo estudiante.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/alumnos");
    } else {
        $_SESSION['mensaje'] = "No se ha podido registrar el nuevo estudiante, comuníquese con el área de IT.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/alumnos/create.php");
    }
} catch (Exception $exception) {
    $_SESSION['mensaje'] = "Error al registrar el estudiante: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/alumnos/create.php");
}
?>
