<?php

include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$nombres = isset($_POST['nombres']) ? strtoupper(trim($_POST['nombres'])) : '';
$clasificacion = strtoupper($_POST['clasificacion']);
$specialization_program_id = isset($_POST['programa_adscripcion']) && !empty($_POST['programa_adscripcion'])
    ? $_POST['programa_adscripcion']
    : null;
$program_ids = isset($_POST['programas']) ? $_POST['programas'] : [];
$area_ids = isset($_POST['areas']) ? $_POST['areas'] : [];
$term_id = isset($_POST['term_id']) ? $_POST['term_id'] : null;

/* Datos de horarios */
$days_of_week = isset($_POST['day_of_week']) ? $_POST['day_of_week'] : [];
$start_times = isset($_POST['start_time']) ? $_POST['start_time'] : [];
$end_times = isset($_POST['end_time']) ? $_POST['end_time'] : [];

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

    /* Validar que el programa de adscripción existe */
    if ($specialization_program_id !== null) {
        $consulta_programa = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE program_id = :programa_id");
        $consulta_programa->bindParam(':programa_id', $specialization_program_id);
        $consulta_programa->execute();
        $existe_programa = $consulta_programa->fetchColumn();

        if (!$existe_programa) {
            throw new Exception("El programa de adscripción seleccionado no existe.");
        }
    }

    /* Actualizar los datos principales del profesor */
    $sentencia_actualizar_profesor = $pdo->prepare("UPDATE teachers 
        SET teacher_name = :nombres, 
            clasificacion = :clasificacion, 
            specialization_program_id = :specialization_program_id, 
            fyh_actualizacion = :fyh_actualizacion
        WHERE teacher_id = :teacher_id");
    $sentencia_actualizar_profesor->bindParam(':nombres', $nombres);
    $sentencia_actualizar_profesor->bindParam(':clasificacion', $clasificacion);
    $sentencia_actualizar_profesor->bindParam(':specialization_program_id', $specialization_program_id);
    $sentencia_actualizar_profesor->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia_actualizar_profesor->bindParam(':teacher_id', $teacher_id);

    if (!$sentencia_actualizar_profesor->execute()) {
        throw new Exception("Error al actualizar la tabla teachers: " . implode(", ", $sentencia_actualizar_profesor->errorInfo()));
    }

    /* Limpiar asociaciones previas en teacher_program_term */
    $sentencia_limpiar_asociaciones = $pdo->prepare("DELETE FROM teacher_program_term WHERE teacher_id = :teacher_id");
    $sentencia_limpiar_asociaciones->bindParam(':teacher_id', $teacher_id);
    $sentencia_limpiar_asociaciones->execute();

    /* Manejo de áreas seleccionadas */
    if (!empty($area_ids)) {
        $sql_programs_by_area = "SELECT program_id FROM programs WHERE area IN (" . implode(',', array_fill(0, count($area_ids), '?')) . ")";
        $query_programs_by_area = $pdo->prepare($sql_programs_by_area);
        $query_programs_by_area->execute($area_ids);
        $programs_from_areas = $query_programs_by_area->fetchAll(PDO::FETCH_COLUMN);

        
        $program_ids = array_unique(array_merge($program_ids, $programs_from_areas));
    }

    /* Insertar programas en teacher_program_term */
    if (!empty($program_ids)) {
        $sentencia_insertar_asociacion = $pdo->prepare("INSERT INTO teacher_program_term (teacher_id, program_id, term_id, fyh_creacion, estado) 
            VALUES (:teacher_id, :program_id, :term_id, :fyh_creacion, 'ACTIVO')");
        foreach ($program_ids as $program_id) {
            $sentencia_insertar_asociacion->bindParam(':teacher_id', $teacher_id);
            $sentencia_insertar_asociacion->bindParam(':program_id', $program_id);
            $sentencia_insertar_asociacion->bindParam(':term_id', $term_id);
            $sentencia_insertar_asociacion->bindParam(':fyh_creacion', $fechaHora);
            $sentencia_insertar_asociacion->execute();
        }
    }

    /* Limpiar horarios previos */
    $sentencia_limpiar_horarios = $pdo->prepare("DELETE FROM teacher_availability WHERE teacher_id = :teacher_id");
    $sentencia_limpiar_horarios->bindParam(':teacher_id', $teacher_id);
    $sentencia_limpiar_horarios->execute();

    /* Insertar nuevos horarios */
    if (!empty($days_of_week) && count($days_of_week) === count($start_times) && count($days_of_week) === count($end_times)) {
        $sentencia_insertar_horarios = $pdo->prepare("INSERT INTO teacher_availability (teacher_id, day_of_week, start_time, end_time, fyh_creacion) 
            VALUES (:teacher_id, :day_of_week, :start_time, :end_time, :fyh_creacion)");

        for ($i = 0; $i < count($days_of_week); $i++) {
            $sentencia_insertar_horarios->bindParam(':teacher_id', $teacher_id);
            $sentencia_insertar_horarios->bindParam(':day_of_week', $days_of_week[$i]);
            $sentencia_insertar_horarios->bindParam(':start_time', $start_times[$i]);
            $sentencia_insertar_horarios->bindParam(':end_time', $end_times[$i]);
            $sentencia_insertar_horarios->bindParam(':fyh_creacion', $fechaHora);
            $sentencia_insertar_horarios->execute();
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
