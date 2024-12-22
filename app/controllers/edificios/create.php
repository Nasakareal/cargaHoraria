<?php

include('../../../app/config.php');

$building_name = $_POST['building_name'];
$planta_alta = $_POST['planta_alta'];
$planta_baja = $_POST['planta_baja'];
$areas = isset($_POST['areas']) ? $_POST['areas'] : [];

$fechaHora = date('Y-m-d H:i:s');

if ($building_name == "" || $planta_alta == "" || $planta_baja == "") {
    session_start();
    $_SESSION['mensaje'] = "Los campos Nombre del Edificio, Planta Alta y Planta Baja son obligatorios.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/edificios/create.php");
    exit;
}

try {
    $pdo->beginTransaction();

    $sentencia = $pdo->prepare('INSERT INTO building_programs 
                                (building_name, planta_alta, planta_baja, fyh_creacion) 
                                VALUES (:building_name, :planta_alta, :planta_baja, :fyh_creacion)');
    $sentencia->bindParam(':building_name', $building_name);
    $sentencia->bindParam(':planta_alta', $planta_alta);
    $sentencia->bindParam(':planta_baja', $planta_baja);
    $sentencia->bindParam(':fyh_creacion', $fechaHora);

    if (!$sentencia->execute()) {
        throw new Exception("Error al registrar el edificio principal.");
    }

    foreach ($areas as &$area) {
        $area = trim($area);
        $area_check = $pdo->prepare('SELECT COUNT(*) FROM programs WHERE area = :area');
        $area_check->bindParam(':area', $area);
        $area_check->execute();

        if ($area_check->fetchColumn() == 0) {
            throw new Exception("El área seleccionada ($area) no existe en la tabla programs.");
        }
    }

    foreach ($areas as $area) {
        $insert_area = $pdo->prepare('INSERT INTO building_programs 
                                      (building_name, area, planta_alta, planta_baja, fyh_creacion) 
                                      VALUES (:building_name, :area, :planta_alta, :planta_baja, :fyh_creacion)');
        $insert_area->bindParam(':building_name', $building_name);
        $insert_area->bindParam(':area', $area);
        $insert_area->bindParam(':planta_alta', $planta_alta);
        $insert_area->bindParam(':planta_baja', $planta_baja);
        $insert_area->bindParam(':fyh_creacion', $fechaHora);

        if (!$insert_area->execute()) {
            throw new Exception("Error al registrar el área: $area.");
        }
    }

    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "El edificio se ha registrado con éxito.";
    $_SESSION['icono'] = "success";
    header('Location:' . APP_URL . "/admin/edificios");
    exit;

} catch (Exception $exception) {
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "Error al registrar el edificio: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}

?>
