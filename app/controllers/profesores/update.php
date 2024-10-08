<?php

include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$nombres = $_POST['nombres'];
$materia_ids = $_POST['materia_ids']; 
$horas_semanales = $_POST['horas_semanales'];

$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    /* Actualizar el nombre del profesor */
    $sentencia_profesor = $pdo->prepare("UPDATE teachers
        SET teacher_name = :nombres,
            fyh_actualizacion = :fyh_actualizacion
        WHERE teacher_id = :teacher_id");

    $sentencia_profesor->bindParam(':nombres', $nombres);
    $sentencia_profesor->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia_profesor->bindParam(':teacher_id', $teacher_id);
    $sentencia_profesor->execute();

    /* Eliminar todas las materias existentes para el profesor */
    $sentencia_eliminar = $pdo->prepare("DELETE FROM teacher_subjects WHERE teacher_id = :teacher_id");
    $sentencia_eliminar->bindParam(':teacher_id', $teacher_id);
    $sentencia_eliminar->execute();

    /* Insertar nuevas materias */
    foreach ($materia_ids as $materia_id) {
        $sentencia_insertar = $pdo->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id, weekly_hours, fyh_creacion, fyh_actualizacion)
            VALUES (:teacher_id, :subject_id, :weekly_hours, :fyh_creacion, :fyh_actualizacion)");

        $sentencia_insertar->bindParam(':teacher_id', $teacher_id);
        $sentencia_insertar->bindParam(':subject_id', $materia_id);
        $sentencia_insertar->bindParam(':weekly_hours', $horas_semanales); 
        $sentencia_insertar->bindParam(':fyh_creacion', $fechaHora);
        $sentencia_insertar->bindParam(':fyh_actualizacion', $fechaHora);
        $sentencia_insertar->execute();
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
?>
