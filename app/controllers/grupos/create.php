<?php
include('../../../app/config.php');
require_once('../../../app/registro_eventos.php');

session_start();

$group_name = isset($_POST['grupo']) ? trim($_POST['grupo']) : null;
$programa_id = isset($_POST['programa_id']) ? trim($_POST['programa_id']) : null;
$term_id = isset($_POST['term_id']) ? trim($_POST['term_id']) : null;
$volume = isset($_POST['volume']) ? trim($_POST['volume']) : null;
$turn_id = isset($_POST['turn_id']) ? trim($_POST['turn_id']) : null;
$fechaHora = date('Y-m-d H:i:s');
$estado_de_registro = '1';

if (empty($group_name) || empty($programa_id) || empty($term_id) || empty($volume) || empty($turn_id)) {
    $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/create.php");
    exit();
}

$sentencia = $pdo->prepare("INSERT INTO `groups`
    (group_name, program_id, term_id, volume, turn_id, fyh_creacion, estado)
VALUES  (:group_name, :programa_id, :term_id, :volume, :turn_id, :fyh_creacion, :estado)");

$sentencia->bindParam(':group_name', $group_name);
$sentencia->bindParam(':programa_id', $programa_id);
$sentencia->bindParam(':term_id', $term_id);
$sentencia->bindParam(':volume', $volume);
$sentencia->bindParam(':turn_id', $turn_id);
$sentencia->bindParam(':fyh_creacion', $fechaHora);
$sentencia->bindParam(':estado', $estado_de_registro);

try {
    if ($sentencia->execute()) {
        $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';
        $accion = 'Registro de grupo';
        $descripcion = "Se registró el grupo '$group_name' en el programa ID $programa_id, periodo ID $term_id, volumen $volume, turno ID $turn_id.";

        registrarEvento($pdo, $usuario_email, $accion, $descripcion);

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
