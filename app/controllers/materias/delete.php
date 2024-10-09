<?php
include('../../../app/config.php');

/* Captura y valida el subject_id */
$subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT);
if (!$subject_id) {
    session_start();
    $_SESSION['mensaje'] = "ID de materia inválido.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/materias");
    exit;
}

try {
    $sentencia = $pdo->prepare("DELETE FROM subjects WHERE subject_id = :subject_id");
    $sentencia->bindParam(':subject_id', $subject_id);

    if ($sentencia->execute()) {
        session_start();
        $_SESSION['mensaje'] = "Se ha eliminado la materia";
        $_SESSION['icono'] = "success";
    } else {
        throw new Exception("Error al eliminar la materia.");
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "No se ha podido eliminar la materia, comuníquese con el área de IT: " . $e->getMessage();
    $_SESSION['icono'] = "error";
}

header('Location: ' . APP_URL . "/admin/materias");
exit;
