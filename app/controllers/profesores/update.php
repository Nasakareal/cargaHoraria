<?php

include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$nombres = $_POST['nombres'];
$programa_id = $_POST['programa_id'];
$cuatrimestre_id = $_POST['cuatrimestre_id'];
$es_local = $_POST['es_local'];
$materia_ids = isset($_POST['materias_asignadas']) ? $_POST['materias_asignadas'] : [];
$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    /* Actualizar el nombre y es_local del profesor */
    $sentencia_profesor = $pdo->prepare("
        UPDATE teachers 
        SET teacher_name = :nombres, 
            es_local = :es_local, 
            fyh_actualizacion = :fyh_actualizacion 
        WHERE teacher_id = :teacher_id");
    $sentencia_profesor->bindParam(':nombres', $nombres);
    $sentencia_profesor->bindParam(':es_local', $es_local);
    $sentencia_profesor->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia_profesor->bindParam(':teacher_id', $teacher_id);

    
    if (!$sentencia_profesor->execute()) {
        throw new Exception("Error al actualizar la tabla teachers: " . implode(", ", $sentencia_profesor->errorInfo()));
    }

    /* Actualizar el programa y cuatrimestre del profesor */
    $sentencia_programa_cuatrimestre = $pdo->prepare("
        REPLACE INTO teacher_program_term 
        (teacher_id, program_id, term_id, fyh_actualizacion, estado) 
        VALUES (:teacher_id, :programa_id, :cuatrimestre_id, :fyh_actualizacion, 'ACTIVO')");
    $sentencia_programa_cuatrimestre->bindParam(':programa_id', $programa_id);
    $sentencia_programa_cuatrimestre->bindParam(':cuatrimestre_id', $cuatrimestre_id);
    $sentencia_programa_cuatrimestre->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia_programa_cuatrimestre->bindParam(':teacher_id', $teacher_id);

    
    if (!$sentencia_programa_cuatrimestre->execute()) {
        throw new Exception("Error al actualizar la tabla teacher_program_term: " . implode(", ", $sentencia_programa_cuatrimestre->errorInfo()));
    }

    /* Solo actualizar las materias si se seleccionaron materias */
    if (!empty($materia_ids)) {
        /* Obtener materias actualmente asignadas al profesor */
        $sentencia_materias_actuales = $pdo->prepare("SELECT subject_id FROM teacher_subjects WHERE teacher_id = :teacher_id");
        $sentencia_materias_actuales->bindParam(':teacher_id', $teacher_id);
        $sentencia_materias_actuales->execute();
        $materias_actuales = $sentencia_materias_actuales->fetchAll(PDO::FETCH_COLUMN);

        
        $materias_a_agregar = array_diff($materia_ids, $materias_actuales);

        
        $materias_a_eliminar = array_diff($materias_actuales, $materia_ids);

        /* Insertar nuevas materias seleccionadas */
        foreach ($materias_a_agregar as $materia_id) {
            $sentencia_insertar = $pdo->prepare("
                INSERT INTO teacher_subjects (teacher_id, subject_id, fyh_creacion, fyh_actualizacion)
                VALUES (:teacher_id, :subject_id, :fyh_creacion, :fyh_actualizacion)");
            $sentencia_insertar->bindParam(':teacher_id', $teacher_id);
            $sentencia_insertar->bindParam(':subject_id', $materia_id);
            $sentencia_insertar->bindParam(':fyh_creacion', $fechaHora);
            $sentencia_insertar->bindParam(':fyh_actualizacion', $fechaHora);

            
            if (!$sentencia_insertar->execute()) {
                throw new Exception("Error al insertar en la tabla teacher_subjects: " . implode(", ", $sentencia_insertar->errorInfo()));
            }
        }

        /* Eliminar las materias que se desasignaron */
        foreach ($materias_a_eliminar as $materia_id) {
            $sentencia_eliminar = $pdo->prepare("DELETE FROM teacher_subjects WHERE teacher_id = :teacher_id AND subject_id = :subject_id");
            $sentencia_eliminar->bindParam(':teacher_id', $teacher_id);
            $sentencia_eliminar->bindParam(':subject_id', $materia_id);

            
            if (!$sentencia_eliminar->execute()) {
                throw new Exception("Error al eliminar de la tabla teacher_subjects: " . implode(", ", $sentencia_eliminar->errorInfo()));
            }
        }
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
