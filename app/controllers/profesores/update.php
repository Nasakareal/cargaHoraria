<?php

include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$nombres = $_POST['nombres'];
$programa_id = $_POST['programa_id'];
$cuatrimestre_id = $_POST['cuatrimestre_id'];
$materia_ids = isset($_POST['materias_asignadas']) ? $_POST['materias_asignadas'] : [];
$grupo_ids = isset($_POST['grupos_asignados']) ? $_POST['grupos_asignados'] : [];
$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    /* Actualizar el nombre del profesor */
    $sentencia_profesor = $pdo->prepare("
        UPDATE teachers 
        SET teacher_name = :nombres, 
            fyh_actualizacion = :fyh_actualizacion 
        WHERE teacher_id = :teacher_id");
    $sentencia_profesor->bindParam(':nombres', $nombres);
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

    /* Manejo de materias asignadas al profesor */
    /* Obtener los IDs de las materias actualmente asignadas al profesor */
    $sentencia_materias_actuales = $pdo->prepare("SELECT subject_id FROM teacher_subjects WHERE teacher_id = :teacher_id");
    $sentencia_materias_actuales->bindParam(':teacher_id', $teacher_id);
    $sentencia_materias_actuales->execute();
    $materias_actuales = $sentencia_materias_actuales->fetchAll(PDO::FETCH_COLUMN);

    /* Insertar solo las materias nuevas que aún no estén asignadas */
    foreach ($materia_ids as $materia_id) {
        if (!in_array($materia_id, $materias_actuales)) {
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
    }

    /* Eliminar solo las materias que ya no están seleccionadas */
    foreach ($materias_actuales as $materia_id_actual) {
        if (!in_array($materia_id_actual, $materia_ids)) {
            $sentencia_eliminar = $pdo->prepare("DELETE FROM teacher_subjects WHERE teacher_id = :teacher_id AND subject_id = :subject_id");
            $sentencia_eliminar->bindParam(':teacher_id', $teacher_id);
            $sentencia_eliminar->bindParam(':subject_id', $materia_id_actual);
            if (!$sentencia_eliminar->execute()) {
                throw new Exception("Error al eliminar materia del profesor: " . implode(", ", $sentencia_eliminar->errorInfo()));
            }
        }
    }

    /* Manejo de grupos asignados al profesor */
    /* Obtener los IDs de los grupos actualmente asignados al profesor */
    $sentencia_grupos_actuales = $pdo->prepare("SELECT group_id FROM teacher_groups WHERE teacher_id = :teacher_id");
    $sentencia_grupos_actuales->bindParam(':teacher_id', $teacher_id);
    $sentencia_grupos_actuales->execute();
    $grupos_actuales = $sentencia_grupos_actuales->fetchAll(PDO::FETCH_COLUMN);

    /* Insertar solo los grupos nuevos que aún no estén asignados */
    foreach ($grupo_ids as $grupo_id) {
        if (!in_array($grupo_id, $grupos_actuales)) {
            $sentencia_insertar_grupo = $pdo->prepare("
                INSERT INTO teacher_groups (teacher_id, group_id, fyh_creacion)
                VALUES (:teacher_id, :group_id, :fyh_creacion)");
            $sentencia_insertar_grupo->bindParam(':teacher_id', $teacher_id);
            $sentencia_insertar_grupo->bindParam(':group_id', $grupo_id);
            $sentencia_insertar_grupo->bindParam(':fyh_creacion', $fechaHora);

            if (!$sentencia_insertar_grupo->execute()) {
                throw new Exception("Error al insertar en la tabla teacher_groups: " . implode(", ", $sentencia_insertar_grupo->errorInfo()));
            }
        }
    }

    /* Eliminar solo los grupos que ya no están seleccionados */
    foreach ($grupos_actuales as $grupo_id_actual) {
        if (!in_array($grupo_id_actual, $grupo_ids)) {
            $sentencia_eliminar_grupo = $pdo->prepare("DELETE FROM teacher_groups WHERE teacher_id = :teacher_id AND group_id = :group_id");
            $sentencia_eliminar_grupo->bindParam(':teacher_id', $teacher_id);
            $sentencia_eliminar_grupo->bindParam(':group_id', $grupo_id_actual);
            if (!$sentencia_eliminar_grupo->execute()) {
                throw new Exception("Error al eliminar grupo del profesor: " . implode(", ", $sentencia_eliminar_grupo->errorInfo()));
            }
        }
    }

    /* Asignar el teacher_subject_id a los horarios existentes en schedules */
    foreach ($materia_ids as $materia_id) {
        /* Obtener el teacher_subject_id correspondiente */
        $sentencia_teacher_subject = $pdo->prepare("SELECT teacher_subject_id FROM teacher_subjects WHERE teacher_id = :teacher_id AND subject_id = :subject_id");
        $sentencia_teacher_subject->bindParam(':teacher_id', $teacher_id);
        $sentencia_teacher_subject->bindParam(':subject_id', $materia_id);
        $sentencia_teacher_subject->execute();
        $teacher_subject_id = $sentencia_teacher_subject->fetchColumn();

        if (!$teacher_subject_id) {
            throw new Exception("No se encontró teacher_subject_id para teacher_id $teacher_id y subject_id $materia_id");
        }

        foreach ($grupo_ids as $grupo_id) {
            /* Actualizar los registros de schedules para asignar el teacher_subject_id */
            $sentencia_actualizar_schedule = $pdo->prepare("
                UPDATE schedules s
                INNER JOIN subjects sub ON sub.subject_id = :subject_id
                SET s.teacher_subject_id = :teacher_subject_id
                WHERE s.group_id = :group_id AND s.teacher_subject_id IS NULL");
            $sentencia_actualizar_schedule->bindParam(':teacher_subject_id', $teacher_subject_id);
            $sentencia_actualizar_schedule->bindParam(':group_id', $grupo_id);
            $sentencia_actualizar_schedule->bindParam(':subject_id', $materia_id);

            if (!$sentencia_actualizar_schedule->execute()) {
                throw new Exception("Error al actualizar schedules: " . implode(", ", $sentencia_actualizar_schedule->errorInfo()));
            }

            /* Ahora insertar en group_schedule_teacher */
            /* Primero, eliminar cualquier asignación previa de este horario, profesor y materia */
            $sentencia_eliminar_gst = $pdo->prepare("
                DELETE gst FROM group_schedule_teacher gst
                INNER JOIN schedules s ON gst.schedule_id = s.schedule_id
                WHERE s.group_id = :group_id AND gst.teacher_id = :teacher_id AND gst.subject_id = :subject_id");
            $sentencia_eliminar_gst->bindParam(':group_id', $grupo_id);
            $sentencia_eliminar_gst->bindParam(':teacher_id', $teacher_id);
            $sentencia_eliminar_gst->bindParam(':subject_id', $materia_id);

            if (!$sentencia_eliminar_gst->execute()) {
                throw new Exception("Error al eliminar asignaciones previas en group_schedule_teacher: " . implode(", ", $sentencia_eliminar_gst->errorInfo()));
            }

            /* Insertar nueva asignación en group_schedule_teacher */
            $sentencia_obtener_schedules = $pdo->prepare("
                SELECT s.schedule_id FROM schedules s
                INNER JOIN teacher_subjects ts ON s.teacher_subject_id = ts.teacher_subject_id
                WHERE s.group_id = :group_id AND ts.teacher_id = :teacher_id AND ts.subject_id = :subject_id");
            $sentencia_obtener_schedules->bindParam(':group_id', $grupo_id);
            $sentencia_obtener_schedules->bindParam(':teacher_id', $teacher_id);
            $sentencia_obtener_schedules->bindParam(':subject_id', $materia_id);
            $sentencia_obtener_schedules->execute();
            $schedule_ids = $sentencia_obtener_schedules->fetchAll(PDO::FETCH_COLUMN);

            foreach ($schedule_ids as $schedule_id) {
                $sentencia_insertar_gst = $pdo->prepare("
                    INSERT INTO group_schedule_teacher (schedule_id, teacher_id, subject_id, fyh_creacion)
                    VALUES (:schedule_id, :teacher_id, :subject_id, :fyh_creacion)");
                $sentencia_insertar_gst->bindParam(':schedule_id', $schedule_id);
                $sentencia_insertar_gst->bindParam(':teacher_id', $teacher_id);
                $sentencia_insertar_gst->bindParam(':subject_id', $materia_id);
                $sentencia_insertar_gst->bindParam(':fyh_creacion', $fechaHora);

                if (!$sentencia_insertar_gst->execute()) {
                    throw new Exception("Error al insertar en group_schedule_teacher: " . implode(", ", $sentencia_insertar_gst->errorInfo()));
                }
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
