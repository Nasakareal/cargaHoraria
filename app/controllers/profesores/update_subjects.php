<?php

include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$materia_ids = isset($_POST['materias_asignadas']) ? $_POST['materias_asignadas'] : [];
$grupo_ids = isset($_POST['grupos_asignados']) ? array_filter($_POST['grupos_asignados']) : [];
$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    /* Validar que haya al menos un grupo seleccionado */
    if (empty($grupo_ids)) {
        throw new Exception("Debe seleccionar al menos un grupo para asignar materias.");
    }

    /* Obtener las materias actualmente asignadas al profesor en los grupos seleccionados */
    $placeholders = implode(',', array_fill(0, count($grupo_ids), '?'));
    $sentencia_materias_actuales = $pdo->prepare("
        SELECT DISTINCT ts.subject_id, ts.group_id 
        FROM teacher_subjects ts
        WHERE ts.teacher_id = ? AND ts.group_id IN ($placeholders)
    ");
    $sentencia_materias_actuales->execute(array_merge([$teacher_id], $grupo_ids));
    $materias_actuales = $sentencia_materias_actuales->fetchAll(PDO::FETCH_ASSOC);

    /* Crear un índice de materias por grupo para facilitar la comparación */
    $materias_actuales_por_grupo = [];
    foreach ($materias_actuales as $materia) {
        $materias_actuales_por_grupo[$materia['group_id']][] = $materia['subject_id'];
    }

    /* Insertar materias nuevas asociadas a los grupos seleccionados */
    foreach ($grupo_ids as $grupo_id) {
        foreach ($materia_ids as $materia_id) {
            if (!isset($materias_actuales_por_grupo[$grupo_id]) || !in_array($materia_id, $materias_actuales_por_grupo[$grupo_id])) {
                $sentencia_insertar = $pdo->prepare("
                    INSERT INTO teacher_subjects (teacher_id, subject_id, group_id, fyh_creacion, fyh_actualizacion) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $sentencia_insertar->execute([$teacher_id, $materia_id, $grupo_id, $fechaHora, $fechaHora]);

                /* Actualizar la tabla de horarios */
                $sentencia_actualizar_horarios = $pdo->prepare("
                    UPDATE schedule_assignments 
                    SET teacher_id = ?, fyh_actualizacion = ?
                    WHERE group_id = ? AND subject_id = ?
                ");
                $sentencia_actualizar_horarios->execute([$teacher_id, $fechaHora, $grupo_id, $materia_id]);
            }
        }
    }

    /* Eliminar materias no seleccionadas en los grupos elegidos */
    foreach ($materias_actuales_por_grupo as $grupo_id => $materias_del_grupo) {
        foreach ($materias_del_grupo as $materia_actual) {
            if (!in_array($materia_actual, $materia_ids)) {
                $sentencia_eliminar = $pdo->prepare("
                    DELETE FROM teacher_subjects 
                    WHERE teacher_id = ? AND subject_id = ? AND group_id = ?
                ");
                $sentencia_eliminar->execute([$teacher_id, $materia_actual, $grupo_id]);

                /* Actualizar la tabla de horarios para remover solo la asignación del profesor */
                $sentencia_actualizar_horarios = $pdo->prepare("
                    UPDATE schedule_assignments 
                    SET teacher_id = NULL, fyh_actualizacion = ?
                    WHERE teacher_id = ? AND subject_id = ? AND group_id = ?
                ");
                $sentencia_actualizar_horarios->execute([$fechaHora, $teacher_id, $materia_actual, $grupo_id]);
            }
        }
    }

    /* Calcular horas totales solo si hay materias seleccionadas */
    $total_hours = 0;
    if (!empty($materia_ids)) {
        $placeholders_materias = implode(',', array_fill(0, count($materia_ids), '?'));
        $sentencia_horas_materias = $pdo->prepare("
            SELECT SUM(s.weekly_hours) AS total_hours
            FROM subjects s
            WHERE s.subject_id IN ($placeholders_materias)
        ");
        $sentencia_horas_materias->execute($materia_ids);
        $total_hours = (int) $sentencia_horas_materias->fetchColumn();
    }

    /* Actualizar las horas totales del profesor */
    $sentencia_actualizar_horas = $pdo->prepare("
        UPDATE teachers SET hours = ?, fyh_actualizacion = ? WHERE teacher_id = ?
    ");
    $sentencia_actualizar_horas->execute([$total_hours, $fechaHora, $teacher_id]);

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
    error_log("Error: " . $exception->getMessage());
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
}
