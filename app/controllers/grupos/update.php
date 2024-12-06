<?php
include('../../../app/config.php');

$group_id = $_POST['group_id'];
$group_name = $_POST['group_name'];
$program_id = $_POST['program_id'];
$term_id = $_POST['term_id'];
$year = $_POST['year'];
$volume = $_POST['volume'];
$turn_id = $_POST['turn_id'];
$nivel_id = $_POST['nivel_id'];  


$group_name = mb_strtoupper($group_name, 'UTF-8');


if (empty($group_name) || empty($program_id) || empty($term_id) || empty($year) || empty($turn_id) || empty($nivel_id)) {
    session_start();
    $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
    exit();
}


$sentencia = $pdo->prepare("UPDATE `groups` 
                            SET group_name = :group_name, 
                                program_id = :program_id, 
                                term_id = :term_id,  
                                year = :year, 
                                volume = :volume, 
                                turn_id = :turn_id, 
                                fyh_actualizacion = NOW() 
                            WHERE group_id = :group_id");


$sentencia->bindParam(':group_name', $group_name);
$sentencia->bindParam(':program_id', $program_id);
$sentencia->bindParam(':term_id', $term_id);
$sentencia->bindParam(':year', $year);
$sentencia->bindParam(':volume', $volume);
$sentencia->bindParam(':turn_id', $turn_id);
$sentencia->bindParam(':group_id', $group_id);

try {
    $pdo->beginTransaction();

    
    if (!$sentencia->execute()) {
        throw new Exception("No se pudo actualizar la tabla `groups`.");
    }

    
    $sentencia_verificar = $pdo->prepare("SELECT * FROM `educational_levels` WHERE group_id = :group_id");
    $sentencia_verificar->bindParam(':group_id', $group_id);
    $sentencia_verificar->execute();
    $nivel_existente = $sentencia_verificar->fetch(PDO::FETCH_ASSOC);

    if ($nivel_existente) {
        
        $sentencia_nivel = $pdo->prepare("UPDATE `educational_levels`
                                          SET level_name = (SELECT level_name FROM `educational_levels` WHERE level_id = :nivel_id)
                                          WHERE group_id = :group_id");

        $sentencia_nivel->bindParam(':nivel_id', $nivel_id);
        $sentencia_nivel->bindParam(':group_id', $group_id);
    } else {
        
        $sentencia_nivel = $pdo->prepare("INSERT INTO `educational_levels` (level_name, group_id)
                                          SELECT level_name, :group_id 
                                          FROM `educational_levels` 
                                          WHERE level_id = :nivel_id");

        $sentencia_nivel->bindParam(':nivel_id', $nivel_id);
        $sentencia_nivel->bindParam(':group_id', $group_id);
    }

    if (!$sentencia_nivel->execute()) {
        throw new Exception("No se pudo actualizar o insertar el nivel educativo en la tabla `educational_levels`.");
    }

    
    $pdo->commit();

    
    session_start();
    $_SESSION['mensaje'] = "El grupo ha sido actualizado correctamente.";
    $_SESSION['icono'] = "success";
    header('Location:' . APP_URL . "/admin/grupos");
    exit();

} catch (Exception $e) {
    
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "Error al actualizar el grupo: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
    exit();
}