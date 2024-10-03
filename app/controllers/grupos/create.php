<?php
include('../../../app/config.php');

$nombre_grupo = $_POST['grupo'];
$programa_id = $_POST['programa_id'];
$cuatrimestre_id = $_POST['cuatrimestre_id'];
$fechaHora = date('Y-m-d H:i:s');
$estado_de_registro = 'activo'; // O el estado que necesites

// Verificar que el nombre del grupo no esté vacío
if (empty($nombre_grupo) || empty($programa_id) || empty($cuatrimestre_id)) {
    session_start();
    $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
    $_SESSION['icono'] = "error"; 
    header('Location:'.APP_URL."/admin/grupos/create.php");
    exit();
}

// Preparar la consulta SQL
$sentencia = $pdo->prepare("INSERT INTO `groups`
        (group_name, program_id, term_id, fyh_creacion, estado)
VALUES  (:group_name, :program_id, :term_id, :fyh_creacion, :estado)");

$sentencia->bindParam(':group_name', $nombre_grupo);
$sentencia->bindParam(':program_id', $programa_id);
$sentencia->bindParam(':term_id', $cuatrimestre_id);
$sentencia->bindParam(':fyh_creacion', $fechaHora);
$sentencia->bindParam(':estado', $estado_de_registro);

try {
    // Ejecutar la consulta
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se ha registrado el nuevo grupo.";
        $_SESSION['icono'] = "success";
        header('Location:'.APP_URL."/admin/grupos");
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se ha podido registrar el nuevo grupo, comuníquese con el área de IT.";
        $_SESSION['icono'] = "error";
        header('Location:'.APP_URL."/admin/grupos/create.php");
    }
} catch (Exception $exception) {
    session_start();
    $_SESSION['mensaje'] = "Error al registrar el grupo: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:'.APP_URL."/admin/grupos/create.php");
}
?>
