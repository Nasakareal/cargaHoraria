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

    // 1. Eliminar las materias asignadas al profesor en teacher_subjects
    $sql_delete_teacher_subjects = "
        DELETE FROM teacher_subjects
        WHERE teacher_id = ?
          AND subject_id IN ($placeholders_materias)
          AND group_id   IN ($placeholders_grupos)
    ";
    $stmt_delete_ts = $pdo->prepare($sql_delete_teacher_subjects);
    $result_teacher_subjects = $stmt_delete_ts->execute(array_merge([$teacher_id], $materia_ids, $grupo_ids));

    if (!$result_teacher_subjects) {
        throw new Exception("No se eliminaron filas en teacher_subjects. Verifica los datos enviados.");
    }

    // 2. Eliminar las filas correspondientes de schedule_assignments
    $sql_delete_schedule = "
        DELETE FROM schedule_assignments
        WHERE teacher_id = ?
          AND subject_id IN ($placeholders_materias)
          AND group_id   IN ($placeholders_grupos)
    ";
    $stmt_delete_schedule = $pdo->prepare($sql_delete_schedule);
    $result_schedule = $stmt_delete_schedule->execute(array_merge([$teacher_id], $materia_ids, $grupo_ids));

    // 3. Eliminar las filas correspondientes de manual_schedule_assignments
    // Nota: Se remueve el filtro teacher_id porque en esta tabla el campo aparece como NULL.
    $sql_delete_manual = "
        DELETE FROM manual_schedule_assignments
        WHERE subject_id IN ($placeholders_materias)
          AND group_id   IN ($placeholders_grupos)
    ";
    $stmt_delete_manual = $pdo->prepare($sql_delete_manual);
    $result_manual = $stmt_delete_manual->execute(array_merge($materia_ids, $grupo_ids));

    // 4. Actualizar el total de horas asignadas al profesor basándose en los registros actuales en teacher_subjects
    $sql_horas_actuales = "
        SELECT SUM(s.weekly_hours) AS total_hours
        FROM teacher_subjects ts
        JOIN subjects s ON ts.subject_id = s.subject_id
        WHERE ts.teacher_id = ?
    ";
    $stmt_horas = $pdo->prepare($sql_horas_actuales);
    $stmt_horas->execute([$teacher_id]);
    $horas_actuales = (int) $stmt_horas->fetchColumn();

    // 5. Actualizar la tabla teachers con el nuevo total de horas
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
    $_SESSION['mensaje'] = "Las materias y los horarios asociados han sido eliminados con éxito.";
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
