<?php
include('../../../app/config.php');

session_start();

$group_name = trim($_POST['grupo']);
$program_id = $_POST['programa_id'];
$period = $_POST['period'];
$year = $_POST['year'];
$volume = $_POST['volume'];
$fechaHora = date('Y-m-d H:i:s');
$estado_de_registro = '1'; // Cambiado a '1' para indicar activo

/* Verificar que los campos no estén vacíos */
if (empty($group_name) || empty($program_id) || empty($period) || empty($year) || empty($volume)) {
    $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/create.php");
    exit();
}

/* Preparar la consulta SQL */
$sentencia = $pdo->prepare("INSERT INTO `groups`
    (group_name, program_id, period, year, volume, fyh_creacion, estado)
VALUES  (:group_name, :program_id, :period, :year, :volume, :fyh_creacion, :estado)");

$sentencia->bindParam(':group_name', $group_name);
$sentencia->bindParam(':program_id', $program_id);
$sentencia->bindParam(':period', $period);
$sentencia->bindParam(':year', $year);
$sentencia->bindParam(':volume', $volume);
$sentencia->bindParam(':fyh_creacion', $fechaHora);
$sentencia->bindParam(':estado', $estado_de_registro);

try {
    /* Ejecutar la consulta */
    if ($sentencia->execute()) {
        $_SESSION['mensaje'] = "Se ha registrado el nuevo grupo.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/grupos");
    } else {
        $_SESSION['mensaje'] = "No se ha podido registrar el nuevo grupo, comuníquese con el área de IT.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/grupos/create.php");
    }
} catch (Exception $exception) {
    $_SESSION['mensaje'] = "Error al registrar el grupo: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/create.php");
}
