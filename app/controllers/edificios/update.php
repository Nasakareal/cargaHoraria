<?php

include('../../../app/config.php');

$building_id = $_POST['building_id'];
$building_name = $_POST['building_name'];
$planta_alta = $_POST['planta_alta'];
$planta_baja = $_POST['planta_baja'];
$areas = isset($_POST['areas']) ? $_POST['areas'] : [];

$building_name = mb_strtoupper($building_name, 'UTF-8');

if ($building_name == "" || $planta_alta == "" || $planta_baja == "") {
    session_start();
    $_SESSION['mensaje'] = "Los campos Nombre del Edificio, Planta Alta y Planta Baja son obligatorios.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/edificios/edit.php?id=" . $building_id);
    exit;
}

try {
    $pdo->beginTransaction();

    $sentencia = $pdo->prepare("UPDATE `building_programs` 
                                SET building_name = :building_name, 
                                    planta_alta = :planta_alta, 
                                    planta_baja = :planta_baja, 
                                    fyh_actualizacion = NOW() 
                                WHERE id = :building_id");

    $sentencia->bindParam(':building_name', $building_name);
    $sentencia->bindParam(':planta_alta', $planta_alta);
    $sentencia->bindParam(':planta_baja', $planta_baja);
    $sentencia->bindParam(':building_id', $building_id);

    if (!$sentencia->execute()) {
        throw new Exception("No se pudo actualizar el edificio.");
    }

    $delete_areas = $pdo->prepare("DELETE FROM `building_programs` WHERE building_name = :building_name AND id != :building_id");
    $delete_areas->bindParam(':building_name', $building_name);
    $delete_areas->bindParam(':building_id', $building_id);

    if (!$delete_areas->execute()) {
        throw new Exception("No se pudieron eliminar las áreas existentes del edificio.");
    }

    foreach ($areas as $area) {
        $insert_area = $pdo->prepare("INSERT INTO `building_programs` (building_name, area, planta_alta, planta_baja, fyh_creacion) 
                                      VALUES (:building_name, :area, :planta_alta, :planta_baja, NOW())");
        $insert_area->bindParam(':building_name', $building_name);
        $insert_area->bindParam(':area', $area);
        $insert_area->bindParam(':planta_alta', $planta_alta);
        $insert_area->bindParam(':planta_baja', $planta_baja);

        if (!$insert_area->execute()) {
            throw new Exception("No se pudo insertar el área: $area.");
        }
    }

    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Se ha actualizado el edificio correctamente.";
    $_SESSION['icono'] = "success";
    header('Location:' . APP_URL . "/admin/edificios");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "Error al actualizar el edificio: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/edificios/edit.php?id=" . $building_id);
    exit;
}
?>
