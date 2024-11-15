<?php

include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$nombres = isset($_POST['nombres']) ? strtoupper(trim($_POST['nombres'])) : '';
$clasificacion = strtoupper($_POST['clasificacion']);
$specialization_program_id = $_POST['programa_id'];
$fechaHora = date('Y-m-d H:i:s');

if (empty($nombres)) {
    session_start();
    $_SESSION['mensaje'] = "El campo 'Nombres' está vacío o no se envió correctamente.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/profesores/edit.php?id=" . $teacher_id);
    exit;
}

try {
    $pdo->beginTransaction();

    /* Obtener el área del programa seleccionado */
    $consulta_area = $pdo->prepare("SELECT area FROM programs WHERE program_id = :programa_id");
    $consulta_area->bindParam(':programa_id', $specialization_program_id);
    $consulta_area->execute();
    $area = $consulta_area->fetchColumn();

    if ($area === false) {
        throw new Exception("No se encontró el área para el programa seleccionado.");
    }

    /* Actualizar el nombre del profesor, clasificación, programa de especialización y área */
    $sentencia_actualizar_profesor = $pdo->prepare("UPDATE teachers 
        SET teacher_name = :nombres, 
            clasificacion = :clasificacion, 
            specialization_program_id = :specialization_program_id, 
            area = :area, 
            fyh_actualizacion = :fyh_actualizacion
        WHERE teacher_id = :teacher_id");
    $sentencia_actualizar_profesor->bindParam(':nombres', $nombres);
    $sentencia_actualizar_profesor->bindParam(':clasificacion', $clasificacion);
    $sentencia_actualizar_profesor->bindParam(':specialization_program_id', $specialization_program_id);
    $sentencia_actualizar_profesor->bindParam(':area', $area);
    $sentencia_actualizar_profesor->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia_actualizar_profesor->bindParam(':teacher_id', $teacher_id);

    if (!$sentencia_actualizar_profesor->execute()) {
        throw new Exception("Error al actualizar la tabla teachers: " . implode(", ", $sentencia_actualizar_profesor->errorInfo()));
    }

    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Se ha actualizado con éxito";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
} catch (Exception $exception) {
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
}
