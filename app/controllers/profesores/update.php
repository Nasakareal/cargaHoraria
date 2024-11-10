<?php

include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$teacher_name = $_POST['teacher_name'];
$area = $_POST['area'];
$clasificacion = $_POST['clasificacion'];
$programa_id = $_POST['programa_id'];
$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    /* Actualizar el nombre del profesor, área, clasificación y programa de adscripción */
    $sentencia_actualizar_profesor = $pdo->prepare("UPDATE teachers 
        SET teacher_name = :teacher_name, 
            area = :area, 
            clasificacion = :clasificacion, 
            program_id = :programa_id, 
            fyh_actualizacion = :fyh_actualizacion
        WHERE teacher_id = :teacher_id");
    $sentencia_actualizar_profesor->bindParam(':teacher_name', $teacher_name);
    $sentencia_actualizar_profesor->bindParam(':area', $area);
    $sentencia_actualizar_profesor->bindParam(':clasificacion', $clasificacion);
    $sentencia_actualizar_profesor->bindParam(':programa_id', $programa_id);
    $sentencia_actualizar_profesor->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia_actualizar_profesor->bindParam(':teacher_id', $teacher_id);

    if (!$sentencia_actualizar_profesor->execute()) {
        throw new Exception("Error al actualizar la tabla teachers: " . implode(", ", $sentencia_actualizar_profesor->errorInfo()));
    }

    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Se ha actualizado con éxito";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/portal/profesores");
    exit;
} catch (Exception $exception) {
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/portal/profesores");
    exit;
}
