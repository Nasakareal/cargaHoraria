<?php
include('../../../app/config.php');

$subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT);
$nombres = filter_input(INPUT_POST, 'nombres', FILTER_SANITIZE_STRING);
$is_specialization = filter_input(INPUT_POST, 'is_specialization', FILTER_VALIDATE_INT);
$horas_consecutivas = filter_input(INPUT_POST, 'horas_consecutivas', FILTER_VALIDATE_INT);
$horas_semanales = filter_input(INPUT_POST, 'horas_semanales', FILTER_VALIDATE_INT);
$program_id = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
$term_id = filter_input(INPUT_POST, 'term_id', FILTER_VALIDATE_INT);

if (!$subject_id || !$nombres || !isset($is_specialization) || !$horas_consecutivas || !$horas_semanales || !$program_id || !$term_id) {
    session_start();
    $_SESSION['mensaje'] = "Error: Datos inválidos.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/materias");
    exit;
}

$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    /* Actualizar materia */
    $sentencia_actualizar = $pdo->prepare("UPDATE subjects
        SET subject_name = :nombres,
            hours_consecutive = :horas_consecutivas,
            weekly_hours = :horas_semanales,
            is_specialization = :is_specialization,
            program_id = :program_id,
            term_id = :term_id,
            fyh_actualizacion = :fyh_actualizacion
        WHERE subject_id = :subject_id");

    $sentencia_actualizar->bindParam(':nombres', $nombres);
    $sentencia_actualizar->bindParam(':horas_consecutivas', $horas_consecutivas);
    $sentencia_actualizar->bindParam(':horas_semanales', $horas_semanales);
    $sentencia_actualizar->bindParam(':is_specialization', $is_specialization);
    $sentencia_actualizar->bindParam(':program_id', $program_id);
    $sentencia_actualizar->bindParam(':term_id', $term_id);
    $sentencia_actualizar->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia_actualizar->bindParam(':subject_id', $subject_id);

    $sentencia_actualizar->execute();

    /* Actualiza o inserta la relación en program_term_subjects */
    $sentencia_relacion = $pdo->prepare("REPLACE INTO program_term_subjects (program_id, term_id, subject_id)
        VALUES (:program_id, :term_id, :subject_id)");

    $sentencia_relacion->bindParam(':program_id', $program_id);
    $sentencia_relacion->bindParam(':term_id', $term_id);
    $sentencia_relacion->bindParam(':subject_id', $subject_id);

    $sentencia_relacion->execute();

    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Materia actualizada";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/materias");
    exit;
} catch (Exception $exception) {
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/materias");
    exit;
}
?>
