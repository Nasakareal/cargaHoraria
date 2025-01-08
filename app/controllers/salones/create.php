<?php
include('../../../app/config.php');
require_once('../../../app/registro_eventos.php'); // Incluir la función de registro de eventos

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_salon = isset($_POST['nombre_salon']) ? trim($_POST['nombre_salon']) : '';
    $capacidad = isset($_POST['capacidad']) ? intval($_POST['capacidad']) : 0;
    $edificio = isset($_POST['edificio']) ? trim($_POST['edificio']) : '';
    $planta = isset($_POST['planta']) ? trim($_POST['planta']) : '';
    $estado_de_registro = "ACTIVO";

    if (empty($nombre_salon) || $capacidad < 1 || empty($edificio) || empty($planta)) {
        $_SESSION['mensaje'] = "Todos los campos son obligatorios y la capacidad debe ser al menos 1.";
        $_SESSION['icono'] = "error";
        header('Location:' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    $fechaHora = date('Y-m-d H:i:s');

    try {
        $sentencia = $pdo->prepare('INSERT INTO classrooms
            (classroom_name, capacity, building, floor, fyh_creacion, estado)
            VALUES (:classroom_name, :capacity, :building, :floor, :fyh_creacion, :estado)');

        $sentencia->bindParam(':classroom_name', $nombre_salon, PDO::PARAM_STR);
        $sentencia->bindParam(':capacity', $capacidad, PDO::PARAM_INT);
        $sentencia->bindParam(':building', $edificio, PDO::PARAM_STR);
        $sentencia->bindParam(':floor', $planta, PDO::PARAM_STR);
        $sentencia->bindParam(':fyh_creacion', $fechaHora, PDO::PARAM_STR);
        $sentencia->bindParam(':estado', $estado_de_registro, PDO::PARAM_STR);

        if ($sentencia->execute()) {
            $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';
            $accion = 'Registro de salón';
            $descripcion = "Se registró el salón '$nombre_salon' con capacidad de $capacidad en el edificio '$edificio', planta '$planta'.";

            registrarEvento($pdo, $usuario_email, $accion, $descripcion);

            $_SESSION['mensaje'] = "El salón se ha registrado con éxito.";
            $_SESSION['icono'] = "success";
            header('Location: ' . APP_URL . "/admin/salones");
            exit();
        } else {
            $_SESSION['mensaje'] = "Error: No se ha podido registrar el salón. Por favor, inténtalo de nuevo o contacta al área de IT.";
            $_SESSION['icono'] = "error";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    } catch (PDOException $exception) {
        $_SESSION['mensaje'] = "Error de base de datos: " . $exception->getMessage();
        $_SESSION['icono'] = "error";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $exception) {
        $_SESSION['mensaje'] = "Error inesperado: " . $exception->getMessage();
        $_SESSION['icono'] = "error";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    header('Location: ' . APP_URL . "/admin/salones");
    exit();
}
?>
