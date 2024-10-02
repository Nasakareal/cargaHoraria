<?php

include('../../../app/config.php');

/* Captura de datos enviados desde el formulario */
$subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT); 
$nombres = filter_input(INPUT_POST, 'nombres', FILTER_SANITIZE_STRING); 
$is_specialization = filter_input(INPUT_POST, 'is_specialization', FILTER_VALIDATE_INT); 
$horas_consecutivas = filter_input(INPUT_POST, 'horas_consecutivas', FILTER_VALIDATE_INT); 

if (!$subject_id || !$nombres || !isset($is_specialization) || !$horas_consecutivas) {
    session_start();
    $_SESSION['mensaje'] = "Error: Datos inválidos.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/materias");
    exit;
}

/* Obtener la fecha y hora actual para la actualización */
$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    /* Actualizar los datos de la materia */
    $sentencia_actualizar = $pdo->prepare("UPDATE subjects
        SET subject_name = :nombres,
            hours_consecutive = :horas_consecutivas,
            is_specialization = :is_specialization,
            fyh_actualizacion = :fyh_actualizacion
        WHERE subject_id = :subject_id");

    /* Vincular parámetros */
    $sentencia_actualizar->bindParam(':nombres', $nombres);
    $sentencia_actualizar->bindParam(':horas_consecutivas', $horas_consecutivas); 
    $sentencia_actualizar->bindParam(':is_specialization', $is_specialization);
    $sentencia_actualizar->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia_actualizar->bindParam(':subject_id', $subject_id);

    /* Ejecutar la actualización de la materia */
    $sentencia_actualizar->execute();

    
    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Materia actualizada";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/materias");
    exit; 
} catch (Exception $exception) {
    
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/materias"); 
    exit;
}
?>
