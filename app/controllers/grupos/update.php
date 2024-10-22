<?php
include('../../../app/config.php');

$group_id = $_POST['group_id'];  
$group_name = $_POST['group_name'];  
$program_id = $_POST['program_id'];
$term_id = $_POST['term_id']; 
$year = $_POST['year']; 
$volume = $_POST['volume']; 
$turn_id = $_POST['turn_id']; 

$group_name = mb_strtoupper($group_name, 'UTF-8');


if (empty($group_name) || empty($program_id) || empty($term_id) || empty($year) || empty($turn_id)) {
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
    
    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "El grupo ha sido actualizado correctamente.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/grupos");
        exit();
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se ha podido actualizar el grupo.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
        exit();
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "Error al actualizar el grupo: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
    exit();
}
?>
