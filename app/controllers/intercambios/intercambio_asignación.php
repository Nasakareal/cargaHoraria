<?php
include('../../../app/config.php');
session_start();

/* Verificar si el usuario tiene el rol de administrador */
$rol_id = $_SESSION['sesion_rol'] ?? null;

if ($rol_id !== '1') {
    $_SESSION['mensaje'] = "No tienes permiso para realizar esta acción.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . '/admin/intercambios');
    exit;
}

/* Capturar datos de la solicitud POST */
$assignment_id = filter_input(INPUT_POST, 'assignment_id', FILTER_VALIDATE_INT);
$new_start_time = filter_input(INPUT_POST, 'new_start_time', FILTER_SANITIZE_STRING);
$new_end_time = filter_input(INPUT_POST, 'new_end_time', FILTER_SANITIZE_STRING);

if (!$assignment_id || !$new_start_time || !$new_end_time) {
    $_SESSION['mensaje'] = "Error: Datos inválidos.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . '/admin/intercambios');
    exit;
}

$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    /* Obtener los datos actuales de la asignación */
    $query_actual = $pdo->prepare("SELECT group_id, schedule_day FROM schedule_assignments WHERE assignment_id = :assignment_id");
    $query_actual->bindParam(':assignment_id', $assignment_id);
    $query_actual->execute();
    $asignacion_actual = $query_actual->fetch(PDO::FETCH_ASSOC);

    if (!$asignacion_actual) {
        throw new Exception("No se encontró la asignación.");
    }

    $group_id = $asignacion_actual['group_id'];
    $schedule_day = $asignacion_actual['schedule_day'];

    /* Verificar conflictos con otras asignaciones */
    $query_conflicto = $pdo->prepare("
        SELECT COUNT(*) AS conflictos
        FROM schedule_assignments
        WHERE group_id = :group_id
        AND schedule_day = :schedule_day
        AND assignment_id != :assignment_id
        AND (
            (start_time < :new_end_time AND end_time > :new_start_time)
        )
    ");
    $query_conflicto->bindParam(':group_id', $group_id);
    $query_conflicto->bindParam(':schedule_day', $schedule_day);
    $query_conflicto->bindParam(':assignment_id', $assignment_id);
    $query_conflicto->bindParam(':new_start_time', $new_start_time);
    $query_conflicto->bindParam(':new_end_time', $new_end_time);
    $query_conflicto->execute();
    $conflictos = $query_conflicto->fetchColumn();

    if ($conflictos > 0) {
        throw new Exception("El horario seleccionado entra en conflicto con otras asignaciones.");
    }

    /* Actualizar el horario en la tabla */
    $query_actualizar = $pdo->prepare("
        UPDATE schedule_assignments
        SET start_time = :new_start_time,
            end_time = :new_end_time,
            fyh_actualizacion = :fyh_actualizacion
        WHERE assignment_id = :assignment_id
    ");
    $query_actualizar->bindParam(':new_start_time', $new_start_time);
    $query_actualizar->bindParam(':new_end_time', $new_end_time);
    $query_actualizar->bindParam(':fyh_actualizacion', $fechaHora);
    $query_actualizar->bindParam(':assignment_id', $assignment_id);
    $query_actualizar->execute();

    $pdo->commit();

    $_SESSION['mensaje'] = "Horario actualizado exitosamente.";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . '/admin/intercambios');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensaje'] = "Ocurrió un error: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . '/admin/intercambios');
    exit;
}
?>
