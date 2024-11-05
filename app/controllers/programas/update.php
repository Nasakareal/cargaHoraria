<?php

include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$nombres = $_POST['nombres'];
$programa_id = $_POST['programa_id'];
$cuatrimestre_id = $_POST['cuatrimestre_id'];
$materia_ids = $_POST['materias_asignadas'];
$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    /* Actualizar el nombre, es_local, programa y cuatrimestre del profesor */
    $sentencia_profesor = $pdo->prepare("
        UPDATE teachers 
        SET teacher_name = :nombres, 
            fyh_actualizacion = :fyh_actualizacion 
        WHERE teacher_id = :teacher_id");
    $sentencia_profesor->bindParam(':nombres', $nombres);
    $sentencia_profesor->bindParam(':es_local', $es_local);
    $sentencia_profesor->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia_profesor->bindParam(':teacher_id', $teacher_id);
    $sentencia_profesor->execute();

    /* Actualizar el programa y cuatrimestre del profesor */
    $sentencia_programa_cuatrimestre = $pdo->prepare("
        UPDATE teacher_program_term 
        SET program_id = :programa_id, 
            term_id = :cuatrimestre_id, 
            fyh_actualizacion = :fyh_actualizacion 
        WHERE teacher_id = :teacher_id");
    $sentencia_programa_cuatrimestre->bindParam(':programa_id', $programa_id);
    $sentencia_programa_cuatrimestre->bindParam(':cuatrimestre_id', $cuatrimestre_id);
    $sentencia_programa_cuatrimestre->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia_programa_cuatrimestre->bindParam(':teacher_id', $teacher_id);
    $sentencia_programa_cuatrimestre->execute();

    /* Eliminar todas las materias existentes para el profesor */
    $sentencia_eliminar = $pdo->prepare("DELETE FROM teacher_subjects WHERE teacher_id = :teacher_id");
    $sentencia_eliminar->bindParam(':teacher_id', $teacher_id);
    $sentencia_eliminar->execute();

    /* Insertar nuevas materias */
    foreach ($materia_ids as $materia_id) {
        $sentencia_insertar = $pdo->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id, fyh_creacion, fyh_actualizacion)
            VALUES (:teacher_id, :subject_id, :fyh_creacion, :fyh_actualizacion)");
        $sentencia_insertar->bindParam(':teacher_id', $teacher_id);
        $sentencia_insertar->bindParam(':subject_id', $materia_id);
        $sentencia_insertar->bindParam(':fyh_creacion', $fechaHora);
        $sentencia_insertar->bindParam(':fyh_actualizacion', $fechaHora);
        $sentencia_insertar->execute();
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