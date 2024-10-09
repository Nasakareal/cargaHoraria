<?php
include('../../../app/config.php');

$group_id = $_POST['group_id']; // ID del grupo a actualizar
$grupo = $_POST['group_name']; // Nombre del grupo
$programa_id = $_POST['program_id']; // ID del programa
$periodo = $_POST['period']; // Periodo
$year = $_POST['year']; // Año
$volumen_grupo = $_POST['volume'] ?? null; // Permitir que este campo sea opcional
$turn_id = $_POST['turn_id']; // ID del turno

$grupo = mb_strtoupper($grupo, 'UTF-8'); // Asegúrate de que el campo esté en mayúsculas

if ($grupo == "" || $programa_id == "" || $periodo == "" || $year == "" || $turn_id == "") {
    session_start();
    $_SESSION['mensaje'] = "Los campos Nombre del grupo, Programa, Periodo, Año y Turno son obligatorios.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
    exit;
}

// Preparamos la consulta
$sentencia = $pdo->prepare("UPDATE `groups` 
                            SET group_name = :grupo, 
                                program_id = :programa_id, 
                                period = :periodo, 
                                year = :year, 
                                volume = :volume, 
                                turn_id = :turn_id, 
                                fyh_actualizacion = NOW() 
                            WHERE group_id = :group_id");

$sentencia->bindParam(':grupo', $grupo);
$sentencia->bindParam(':programa_id', $programa_id);
$sentencia->bindParam(':periodo', $periodo);
$sentencia->bindParam(':year', $year);
$sentencia->bindParam(':volume', $volumen_grupo);
$sentencia->bindParam(':turn_id', $turn_id); // Asegúrate de vincular el turno
$sentencia->bindParam(':group_id', $group_id);

try {
    // Ejecutamos la consulta
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se ha actualizado el grupo correctamente";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/grupos");
        exit; // Asegúrate de salir después de redirigir
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se ha podido actualizar el grupo, posiblemente ya existe.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
        exit;
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "Error al actualizar el grupo: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
    exit;
}
