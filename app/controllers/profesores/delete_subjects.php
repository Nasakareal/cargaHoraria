<?php
include('../../../app/config.php');

$teacher_id    = $_POST['teacher_id'];
$materia_ids   = isset($_POST['materias_eliminar']) ? $_POST['materias_eliminar'] : [];
$grupo_ids     = isset($_POST['grupos_asignados']) ? array_filter($_POST['grupos_asignados']) : [];
$fechaHora     = date('Y-m-d H:i:s');

try {
    if (!$pdo->inTransaction()) {
        $pdo->beginTransaction();
    }

    if (empty($materia_ids)) {
        throw new Exception("Debe seleccionar al menos una materia para eliminar.");
    }

    if (empty($grupo_ids)) {
        throw new Exception("Debe seleccionar al menos un grupo asociado a las materias para eliminar.");
    }

    $placeholders_materias = implode(',', array_fill(0, count($materia_ids), '?'));
    $placeholders_grupos   = implode(',', array_fill(0, count($grupo_ids), '?'));

    // ---------------------------------------------------------------------------------------------
    // 1. Eliminar de teacher_subjects los registros que coincidan con teacher_id, subject_id y group_id
    // ---------------------------------------------------------------------------------------------
    $sql_delete_teacher_subjects = "
        DELETE FROM teacher_subjects
        WHERE teacher_id = ?
          AND subject_id IN ($placeholders_materias)
          AND group_id   IN ($placeholders_grupos)
    ";
    $stmt_delete_ts = $pdo->prepare($sql_delete_teacher_subjects);
    $stmt_delete_ts->execute(array_merge([$teacher_id], $materia_ids, $grupo_ids));

    if ($stmt_delete_ts->rowCount() === 0) {
        throw new Exception("No se eliminaron registros en teacher_subjects. Verifica los datos enviados.");
    }

    // ---------------------------------------------------------------------------------------------
    // 2. Poner en NULL el teacher_id de schedule_assignments Y manual_schedule_assignments
    //    cuando existan filas equivalentes (mismo subject_id, group_id, start_time, end_time, schedule_day).
    // ---------------------------------------------------------------------------------------------
    $sql_update_both = "
        UPDATE schedule_assignments s
        JOIN manual_schedule_assignments m ON 
             s.subject_id    = m.subject_id
         AND s.group_id      = m.group_id
         AND s.start_time    = m.start_time
         AND s.end_time      = m.end_time
         AND s.schedule_day  = m.schedule_day
        SET 
            s.teacher_id = NULL,
            m.teacher_id = NULL
        WHERE s.teacher_id = ?
          AND s.subject_id IN ($placeholders_materias)
          AND s.group_id   IN ($placeholders_grupos)
    ";
    $stmt_update_both = $pdo->prepare($sql_update_both);
    $stmt_update_both->execute(array_merge([$teacher_id], $materia_ids, $grupo_ids));

    // ---------------------------------------------------------------------------------------------
    // 3. Eliminar de schedule_assignments aquellas filas que NO tienen equivalente en manual_schedule_assignments.
    // ---------------------------------------------------------------------------------------------
    $sql_delete_solo_schedule = "
        DELETE s
        FROM schedule_assignments s
        WHERE s.teacher_id = ?
          AND s.subject_id IN ($placeholders_materias)
          AND s.group_id   IN ($placeholders_grupos)
          AND NOT EXISTS (
              SELECT 1 
              FROM manual_schedule_assignments m
              WHERE 
                  m.subject_id   = s.subject_id
              AND m.group_id     = s.group_id
              AND m.start_time   = s.start_time
              AND m.end_time     = s.end_time
              AND m.schedule_day = s.schedule_day
          )
    ";
    $stmt_delete_solo_schedule = $pdo->prepare($sql_delete_solo_schedule);
    $stmt_delete_solo_schedule->execute(array_merge([$teacher_id], $materia_ids, $grupo_ids));

    // ---------------------------------------------------------------------------------------------
    // 4. Calcular las horas actuales en teacher_subjects (ya actualizados tras el DELETE)
    // ---------------------------------------------------------------------------------------------
    $sql_horas_actuales = "
        SELECT COALESCE(SUM(s.weekly_hours), 0) AS total_hours
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
        SET hours            = ?,
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
