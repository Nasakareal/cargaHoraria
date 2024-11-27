<?php

include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$materia_ids = isset($_POST['materias_eliminar']) ? $_POST['materias_eliminar'] : [];
$grupo_ids = isset($_POST['grupos_asignados']) ? array_filter($_POST['grupos_asignados']) : [];
$fechaHora = date('Y-m-d H:i:s');

try {
    // Iniciar transacción
    if (!$pdo->inTransaction()) {
        $pdo->beginTransaction();
    }

    /* Validar que haya al menos una materia seleccionada */
    if (empty($materia_ids)) {
        throw new Exception("Debe seleccionar al menos una materia para eliminar.");
    }

    /* Validar que haya al menos un grupo seleccionado */
    if (empty($grupo_ids)) {
        throw new Exception("Debe seleccionar al menos un grupo asociado a las materias para eliminar.");
    }

    /* Crear placeholders para materias y grupos */
    $placeholders_materias = implode(',', array_fill(0, count($materia_ids), '?'));
    $placeholders_grupos = implode(',', array_fill(0, count($grupo_ids), '?'));

    /* Eliminar las filas de `teacher_subjects` */
    $sentencia_eliminar_teacher_subjects = $pdo->prepare("
        DELETE FROM teacher_subjects 
        WHERE teacher_id = ? 
        AND subject_id IN ($placeholders_materias) 
        AND group_id IN ($placeholders_grupos)
    ");
    $eliminadas = $sentencia_eliminar_teacher_subjects->execute(array_merge([$teacher_id], $materia_ids, $grupo_ids));

    if (!$eliminadas) {
        throw new Exception("No se eliminaron filas en teacher_subjects. Verifica los datos enviados.");
    }

    /* Actualizar la tabla `schedule_assignments` */
    $sentencia_actualizar_schedule = $pdo->prepare("
        UPDATE schedule_assignments 
        SET teacher_id = NULL 
        WHERE teacher_id = ? 
        AND subject_id IN ($placeholders_materias) 
        AND group_id IN ($placeholders_grupos)
    ");
    $actualizadas = $sentencia_actualizar_schedule->execute(array_merge([$teacher_id], $materia_ids, $grupo_ids));

    if (!$actualizadas) {
        throw new Exception("No se actualizó ningún registro en schedule_assignments.");
    }

    /* Recalcular las horas totales del profesor */
    $sentencia_horas_actuales = $pdo->prepare("
        SELECT SUM(s.weekly_hours) AS total_hours
        FROM teacher_subjects ts
        JOIN subjects s ON ts.subject_id = s.subject_id
        WHERE ts.teacher_id = ?
    ");
    $sentencia_horas_actuales->execute([$teacher_id]);
    $horas_actuales = (int) $sentencia_horas_actuales->fetchColumn();

    /* Actualizar las horas totales del profesor */
    $sentencia_actualizar_horas = $pdo->prepare("
        UPDATE teachers 
        SET hours = ?, fyh_actualizacion = ? 
        WHERE teacher_id = ?
    ");
    $sentencia_actualizar_horas->execute([$horas_actuales, $fechaHora, $teacher_id]);

    // Confirmar transacción
    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Las materias han sido eliminadas con éxito.";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/configuraciones/eliminar_materias_profesor");
    exit;

} catch (Exception $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    error_log("Error: " . $exception->getMessage());
    header('Location: ' . APP_URL . "/admin/configuraciones/eliminar_materias_profesor");
    exit;
}
