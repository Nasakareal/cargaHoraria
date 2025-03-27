<?php
include('../../../config.php');
require_once('../../../registro_eventos.php');

session_start();

$assignment_id = $_POST['assignment_id'] ?? null;
$quarter_name_en = trim($_POST['quarter_name_en'] ?? '');

if (!$assignment_id || $quarter_name_en === '') {
    $_SESSION['mensaje'] = "El campo Cuatrimestre (en inglés) es obligatorio.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/configuraciones/horarios/edit.php?id=" . $assignment_id);
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE schedule_history 
                           SET quarter_name_en = :quarter_name_en, 
                               fyh_actualizacion = NOW() 
                           WHERE assignment_id = :assignment_id");
    $stmt->bindParam(':quarter_name_en', $quarter_name_en);
    $stmt->bindParam(':assignment_id', $assignment_id);

    if (!$stmt->execute()) {
        throw new Exception("No se pudo actualizar el cuatrimestre.");
    }

    // Registrar evento
    $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';
    $accion = 'Edición de cuatrimestre';
    $descripcion = "Se actualizó el cuatrimestre en inglés a '$quarter_name_en' para el registro con assignment_id $assignment_id.";
    registrarEvento($pdo, $usuario_email, $accion, $descripcion);

    $pdo->commit();

    $_SESSION['mensaje'] = "El cuatrimestre fue actualizado correctamente.";
    $_SESSION['icono'] = "success";
    header('Location:' . APP_URL . "/admin/configuraciones/horarios/index.php");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/configuraciones/horarios/edit.php?id=" . $assignment_id);
    exit();
}
