<?php
include('../../../app/config.php');
require_once('../../../app/registro_eventos.php');

/* Captura y valida el subject_id */
$subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT);
if (!$subject_id) {
    session_start();
    $_SESSION['mensaje'] = "ID de materia inválido.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/materias");
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Verificar si el usuario es admin */
if (!isset($_SESSION['sesion_rol']) || $_SESSION['sesion_rol'] != 1) {
    $_SESSION['mensaje'] = "No tienes permisos para eliminar materias. Solo los administradores pueden realizar esta acción.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/materias");
    exit();
}

try {
    $pdo->beginTransaction();

    /* Obtener el nombre de la materia antes de eliminar */
    $querySubjectName = $pdo->prepare("SELECT subject_name FROM subjects WHERE subject_id = :subject_id");
    $querySubjectName->bindParam(':subject_id', $subject_id);
    $querySubjectName->execute();
    $subject_name = $querySubjectName->fetchColumn();

    if (!$subject_name) {
        throw new Exception("Materia no encontrada.");
    }

    // 1. Eliminar registros relacionados en program_term_subjects
    $delete_relations = $pdo->prepare("DELETE FROM program_term_subjects WHERE subject_id = :subject_id");
    $delete_relations->bindParam(':subject_id', $subject_id);
    $delete_relations->execute();

    // 2. Eliminar la materia en subjects
    $sentencia = $pdo->prepare("DELETE FROM subjects WHERE subject_id = :subject_id");
    $sentencia->bindParam(':subject_id', $subject_id);
    $sentencia->execute();

    // 3. Registrar evento
    $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';
    $accion = 'Eliminación de materia';
    $descripcion = "Se eliminó la materia '$subject_name' con ID $subject_id.";

    registrarEvento($pdo, $usuario_email, $accion, $descripcion);

    $pdo->commit();

    $_SESSION['mensaje'] = "Se ha eliminado la materia correctamente.";
    $_SESSION['icono'] = "success";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['mensaje'] = "No se ha podido eliminar la materia, comuníquese con el área de IT: " . $e->getMessage();
    $_SESSION['icono'] = "error";
}

header('Location: ' . APP_URL . "/admin/materias");
exit();
?>
