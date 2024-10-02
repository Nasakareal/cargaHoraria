<?php

include('../../../app/config.php');

/* Captura de datos enviados desde el formulario */
$teacher_id = $_POST['teacher_id'];
$nombres = $_POST['nombres'];
<<<<<<< HEAD
$materia_id = $_POST['materia_id']; 
$horas_semanales = $_POST['horas_semanales']; 
=======
$materias = $_POST['materias'] ?? [];
>>>>>>> 09dfda8 (descagada)

/* Obtener la fecha y hora actual para la actualización */
$fechaHora = date('Y-m-d H:i:s');

try {
<<<<<<< HEAD
    
=======

>>>>>>> 09dfda8 (descagada)
    $pdo->beginTransaction();

    /* Actualizar los datos del profesor */
    $sentencia_profesor = $pdo->prepare("UPDATE teachers
        SET teacher_name = :nombres,
            fyh_actualizacion = :fyh_actualizacion
        WHERE teacher_id = :teacher_id");

    /* Vincular parámetros */
    $sentencia_profesor->bindParam(':nombres', $nombres);
    $sentencia_profesor->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia_profesor->bindParam(':teacher_id', $teacher_id);

    /* Ejecutar la actualización del profesor */
    $sentencia_profesor->execute();

<<<<<<< HEAD
    /* Verificar si la relación ya existe */
    $sentencia_verificacion = $pdo->prepare("SELECT * FROM teacher_subjects WHERE teacher_id = :teacher_id AND subject_id = :subject_id");
    $sentencia_verificacion->bindParam(':teacher_id', $teacher_id);
    $sentencia_verificacion->bindParam(':subject_id', $materia_id);
    $sentencia_verificacion->execute();

    if ($sentencia_verificacion->rowCount() > 0) {

        $sentencia_actualizar = $pdo->prepare("UPDATE teacher_subjects
            SET weekly_hours = :weekly_hours,
                fyh_actualizacion = :fyh_actualizacion
            WHERE teacher_id = :teacher_id AND subject_id = :subject_id");

        $sentencia_actualizar->bindParam(':weekly_hours', $horas_semanales);
        $sentencia_actualizar->bindParam(':fyh_actualizacion', $fechaHora);
        $sentencia_actualizar->bindParam(':teacher_id', $teacher_id);
        $sentencia_actualizar->bindParam(':subject_id', $materia_id);
        $sentencia_actualizar->execute();
    } else {
        $sentencia_insertar = $pdo->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id, weekly_hours, fyh_creacion, fyh_actualizacion)
            VALUES (:teacher_id, :subject_id, :weekly_hours, :fyh_creacion, :fyh_actualizacion)");

        $sentencia_insertar->bindParam(':teacher_id', $teacher_id);
        $sentencia_insertar->bindParam(':subject_id', $materia_id);
        $sentencia_insertar->bindParam(':weekly_hours', $horas_semanales);
        $sentencia_insertar->bindParam(':fyh_creacion', $fechaHora);
        $sentencia_insertar->bindParam(':fyh_actualizacion', $fechaHora);
        $sentencia_insertar->execute();
    }

    
=======
    /* Actualizar las materias del profesor */
    // Primero eliminar todas las materias asignadas previamente
    $sentencia_eliminar = $pdo->prepare("DELETE FROM teacher_subjects WHERE teacher_id = :teacher_id");
    $sentencia_eliminar->bindParam(':teacher_id', $teacher_id);
    $sentencia_eliminar->execute();

    // Luego, insertar las materias seleccionadas nuevamente
    foreach ($materias as $subject_id => $materia) {
        if (isset($materia['seleccionada'])) {
            $horas = $materia['horas'];
            $sentencia_insertar = $pdo->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id, weekly_hours, fyh_creacion, fyh_actualizacion)
                VALUES (:teacher_id, :subject_id, :weekly_hours, :fyh_creacion, :fyh_actualizacion)");

            $sentencia_insertar->bindParam(':teacher_id', $teacher_id);
            $sentencia_insertar->bindParam(':subject_id', $subject_id);
            $sentencia_insertar->bindParam(':weekly_hours', $horas);
            $sentencia_insertar->bindParam(':fyh_creacion', $fechaHora);
            $sentencia_insertar->bindParam(':fyh_actualizacion', $fechaHora);
            $sentencia_insertar->execute();
        }
    }

>>>>>>> 09dfda8 (descagada)
    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Se ha actualizado con éxito";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/profesores");
<<<<<<< HEAD
    exit; 
} catch (Exception $exception) {
    
=======
    exit;
} catch (Exception $exception) {

>>>>>>> 09dfda8 (descagada)
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
<<<<<<< HEAD
    header('Location: ' . APP_URL . "/admin/profesores"); 
=======
    header('Location: ' . APP_URL . "/admin/profesores");
>>>>>>> 09dfda8 (descagada)
    exit;
}
?>
