<?php

include('../../../app/config.php');
require_once('../../../app/registro_eventos.php');

/* Validar que se reciba el ID del profesor */
$teacher_id = filter_input(INPUT_POST, 'teacher_id', FILTER_VALIDATE_INT);
if (!$teacher_id) {
    session_start();
    $_SESSION['mensaje'] = "ID de profesor inválido. Por favor, intenta nuevamente.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit();
}

/* Iniciar sesión si no está iniciada */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Verificar permisos de administrador */
if (!isset($_SESSION['sesion_rol']) || $_SESSION['sesion_rol'] != 1) {
    $_SESSION['mensaje'] = "No tienes permisos para eliminar profesores. Solo los administradores pueden realizar esta acción.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit();
}

try {
    /* Iniciar una transacción */
    $pdo->beginTransaction();

    /* Eliminar los registros relacionados en la tabla 'teacher_program_term' */
    $deleteRelated = $pdo->prepare("DELETE FROM teacher_program_term WHERE teacher_id = :teacher_id");
    $deleteRelated->bindParam(':teacher_id', $teacher_id);
    $deleteRelated->execute();

    /* Obtener el nombre del profesor antes de eliminar */
    $queryTeacherName = $pdo->prepare("SELECT teacher_name FROM teachers WHERE teacher_id = :teacher_id");
    $queryTeacherName->bindParam(':teacher_id', $teacher_id);
    $queryTeacherName->execute();
    $teacher_name = $queryTeacherName->fetchColumn();

    /* Ahora eliminar el profesor */
    $deleteTeacher = $pdo->prepare("DELETE FROM teachers WHERE teacher_id = :teacher_id");
    $deleteTeacher->bindParam(':teacher_id', $teacher_id);

    if ($deleteTeacher->execute()) {
        
        $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';
        $accion = 'Eliminación de profesor';
        $descripcion = "Se eliminó al profesor '$teacher_name' con ID $teacher_id y sus registros relacionados.";

        registrarEvento($pdo, $usuario_email, $accion, $descripcion);

        $_SESSION['mensaje'] = "El profesor y sus registros relacionados han sido eliminados correctamente.";
        $_SESSION['icono'] = "success";
    } else {
        throw new Exception("No se pudo eliminar el profesor. Intenta nuevamente.");
    }

    /* Confirmar la transacción */
    $pdo->commit();
} catch (Exception $e) {
    /* Revertir la transacción en caso de error */
    $pdo->rollBack();
    $_SESSION['mensaje'] = "Error al eliminar el profesor: " . $e->getMessage();
    $_SESSION['icono'] = "error";
}

/* Redirigir al índice de profesores */
header('Location: ' . APP_URL . "/admin/profesores");
exit();
