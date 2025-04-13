<?php
include('../../../app/config.php');

// Recibimos el teacher_id, materias y grupos seleccionados
$teacher_id    = $_POST['teacher_id'];
$materia_ids   = isset($_POST['materias_eliminar']) ? $_POST['materias_eliminar'] : [];
$grupo_ids     = isset($_POST['grupos_asignados']) ? array_filter($_POST['grupos_asignados']) : [];
$fechaHora     = date('Y-m-d H:i:s');

try {
    // Iniciar una transacción si no se ha iniciado
    if (!$pdo->inTransaction()) {
        $pdo->beginTransaction();
    }

    if (empty($materia_ids)) {
        throw new Exception("Debe seleccionar al menos una materia para eliminar.");
    }

    if (empty($grupo_ids)) {
        throw new Exception("Debe seleccionar al menos un grupo asociado a las materias para eliminar.");
    }

    // Crear los placeholders para las consultas SQL usando IN
    $placeholders_materias = implode(',', array_fill(0, count($materia_ids), '?'));
    $placeholders_grupos   = implode(',', array_fill(0, count($grupo_ids), '?'));

    // ---------------------------------------------------------------------------------------------
    // 1. Actualizar teacher_subjects: Se pasa a NULL el teacher_id cuando coincida con subject_id y group_id
    // ---------------------------------------------------------------------------------------------
    $sql_update_teacher_subjects = "
        UPDATE teacher_subjects
        SET teacher_id = NULL
        WHERE teacher_id = ?
          AND subject_id IN ($placeholders_materias)
          AND group_id IN ($placeholders_grupos)
    ";
    $stmt_update_ts = $pdo->prepare($sql_update_teacher_subjects);
    $result_teacher_subjects = $stmt_update_ts->execute(array_merge([$teacher_id], $materia_ids, $grupo_ids));

    if (!$result_teacher_subjects) {
        throw new Exception("No se actualizó teacher_subjects. Verifica los datos enviados.");
    }

    // ---------------------------------------------------------------------------------------------
    // 2. Poner en NULL el teacher_id de schedule_assignments Y manual_schedule_assignments
    //    cuando existan filas equivalentes (mismo subject_id, group_id, start_time, end_time, schedule_day).
    //    Si en tu tabla manual_schedule_assignments efectivamente manejas un campo teacher_id,
    //    entonces lo actualizas; de lo contrario, omite la parte del update a manual.
    // ---------------------------------------------------------------------------------------------
    // Primero, programamos un UPDATE conjunto que afecte ambas tablas:
    $sql_update_both = "
        UPDATE schedule_assignments s
        JOIN manual_schedule_assignments m ON 
             s.subject_id = m.subject_id
             AND s.group_id = m.group_id
             AND s.start_time = m.start_time
             AND s.end_time   = m.end_time
             AND s.schedule_day = m.schedule_day
        SET 
            s.teacher_id = NULL,
            m.teacher_id = NULL  -- <-- solo si manual_schedule_assignments también maneja teacher_id
        WHERE s.teacher_id = ?
          AND s.subject_id IN ($placeholders_materias)
          AND s.group_id IN ($placeholders_grupos)
    ";
    $stmt_update_both = $pdo->prepare($sql_update_both);
    $params_update_both = array_merge([$teacher_id], $materia_ids, $grupo_ids);
    $stmt_update_both->execute($params_update_both);

    // ---------------------------------------------------------------------------------------------
    // 3. Eliminar de schedule_assignments aquellas filas (teacher_id, subject_id, group_id, etc.)
    //    que NO tienen equivalente en manual_schedule_assignments.
    //    Es decir, si no se encontró la misma asignación en manual, se borra completamente.
    // ---------------------------------------------------------------------------------------------
    $sql_delete_solo_schedule = "
        DELETE s
        FROM schedule_assignments s
        WHERE s.teacher_id = ?
          AND s.subject_id IN ($placeholders_materias)
          AND s.group_id   IN ($placeholders_grupos)
          -- si NO existe un registro en manual que coincida con las columnas relevantes,
          -- entonces se elimina
          AND NOT EXISTS (
              SELECT 1 
              FROM manual_schedule_assignments m
              WHERE 
                  m.subject_id = s.subject_id
                  AND m.group_id = s.group_id
                  AND m.start_time = s.start_time
                  AND m.end_time   = s.end_time
                  AND m.schedule_day = s.schedule_day
          )
    ";
    $stmt_delete_solo_schedule = $pdo->prepare($sql_delete_solo_schedule);
    $params_delete_solo_schedule = array_merge([$teacher_id], $materia_ids, $grupo_ids);
    $stmt_delete_solo_schedule->execute($params_delete_solo_schedule);

    // ---------------------------------------------------------------------------------------------
    // 4. Calcular las horas actuales
    // ---------------------------------------------------------------------------------------------
    $sql_horas_actuales = "
        SELECT SUM(s.weekly_hours) AS total_hours
        FROM teacher_subjects ts
        JOIN subjects s ON ts.subject_id = s.subject_id
        WHERE ts.teacher_id = ?
    ";
    $stmt_horas = $pdo->prepare($sql_horas_actuales);
    $stmt_horas->execute([$teacher_id]);
    $horas_actuales = (int) $stmt_horas->fetchColumn();

    // ---------------------------------------------------------------------------------------------
    // 5. Actualizar la tabla teachers con el nuevo total de horas
    // ---------------------------------------------------------------------------------------------
    $sql_update_teacher = "
        UPDATE teachers
        SET hours = ?,
            fyh_actualizacion = ?
        WHERE teacher_id = ?
    ";
    $stmt_update_teachers = $pdo->prepare($sql_update_teacher);
    $stmt_update_teachers->execute([$horas_actuales, $fechaHora, $teacher_id]);

    // Confirmar la transacción
    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Las materias y los horarios asociados se han procesado correctamente.";
    $_SESSION['icono']   = "success";
    header('Location: ' . APP_URL . "/admin/configuraciones/eliminar_materias_profesor");
    exit;

} catch (Exception $exception) {
    // En caso de error, revertir la transacción y notificar
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error: " . $exception->getMessage();
    $_SESSION['icono']   = "error";
    error_log("Error: " . $exception->getMessage());
    header('Location: ' . APP_URL . "/admin/configuraciones/eliminar_materias_profesor");
    exit;
}
?>
