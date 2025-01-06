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

    $sql_delete_teacher_subjects = "
        DELETE FROM teacher_subjects
        WHERE teacher_id = ?
          AND subject_id IN ($placeholders_materias)
          AND group_id   IN ($placeholders_grupos)
    ";
    $stmt_delete_ts = $pdo->prepare($sql_delete_teacher_subjects);
    $eliminadas = $stmt_delete_ts->execute(array_merge([$teacher_id], $materia_ids, $grupo_ids));

    if (!$eliminadas) {
        throw new Exception("No se eliminaron filas en teacher_subjects. Verifica los datos enviados.");
    }

    $sql_update_schedule = "
        UPDATE schedule_assignments
        SET teacher_id = NULL,
            fyh_actualizacion = ?
        WHERE teacher_id = ?
          AND subject_id IN ($placeholders_materias)
          AND group_id   IN ($placeholders_grupos)
    ";
    $stmt_update_sa = $pdo->prepare($sql_update_schedule);
    $actualizadas = $stmt_update_sa->execute(array_merge([$fechaHora, $teacher_id], $materia_ids, $grupo_ids));

    if (!$actualizadas) {
    }

    $sql_update_manual = "
        UPDATE manual_schedule_assignments
        SET teacher_id = NULL,
            fyh_actualizacion = ?
        WHERE teacher_id = ?
          AND subject_id IN ($placeholders_materias)
          AND group_id   IN ($placeholders_grupos)
    ";
    $stmt_update_ma = $pdo->prepare($sql_update_manual);
    $actualizadas_manual = $stmt_update_ma->execute(array_merge([$fechaHora, $teacher_id], $materia_ids, $grupo_ids));

    if (!$actualizadas_manual) {

    }

    $sql_horas_actuales = "
        SELECT SUM(s.weekly_hours) AS total_hours
        FROM teacher_subjects ts
        JOIN subjects s ON ts.subject_id = s.subject_id
        WHERE ts.teacher_id = ?
    ";
    $stmt_horas = $pdo->prepare($sql_horas_actuales);
    $stmt_horas->execute([$teacher_id]);
    $horas_actuales = (int) $stmt_horas->fetchColumn();

    $sql_update_teacher = "
        UPDATE teachers
        SET hours = ?,
            fyh_actualizacion = ?
        WHERE teacher_id = ?
    ";
    $stmt_update_teachers = $pdo->prepare($sql_update_teacher);
    $stmt_update_teachers->execute([$horas_actuales, $fechaHora, $teacher_id]);

    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Las materias han sido eliminadas con éxito, y se actualizó Horarios Aumaticos y Manuales.";
    $_SESSION['icono']   = "success";
    header('Location: ' . APP_URL . "/admin/configuraciones/eliminar_materias_profesor");
    exit;

} catch (Exception $exception) {
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
