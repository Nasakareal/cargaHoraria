<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/registro_eventos.php'); // Incluir la función de registro de eventos

// Iniciar sesión para acceder al email del usuario
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/app/controllers/asignacion_manual/debug.log');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assignment_id = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;
    $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    $lab_id = isset($_POST['lab_id']) ? intval($_POST['lab_id']) : null;

    if (empty($assignment_id) || empty($group_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Verificar si la asignación existe
        if ($lab_id) {
            $check_exists = $pdo->prepare("
                SELECT * FROM manual_schedule_assignments 
                WHERE assignment_id = :assignment_id 
                  AND group_id = :group_id
                  AND (lab1_assigned = :lab_id OR lab2_assigned = :lab_id)
            ");
            $check_exists->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
        } else {
            $check_exists = $pdo->prepare("
                SELECT * FROM manual_schedule_assignments 
                WHERE assignment_id = :assignment_id 
                  AND group_id = :group_id
            ");
        }

        $check_exists->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        $check_exists->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $check_exists->execute();

        if ($check_exists->rowCount() == 0) {
            echo json_encode(['status' => 'error', 'message' => 'La asignación no existe o no está asociada al laboratorio especificado.']);
            $pdo->rollBack();
            exit;
        }

        // Eliminar la asignación
        $consulta_delete = $pdo->prepare("
            DELETE FROM manual_schedule_assignments 
            WHERE assignment_id = :assignment_id 
              AND group_id = :group_id
        ");
        $consulta_delete->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        $consulta_delete->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $consulta_delete->execute();

        if ($consulta_delete->rowCount() > 0) {
            $pdo->commit();

            // Registrar el evento de eliminación
            $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com'; // Email del usuario autenticado
            $accion = 'Eliminación de asignación manual';
            $descripcion = "Se eliminó la asignación con ID $assignment_id del grupo ID $group_id.";

            registrarEvento($pdo, $usuario_email, $accion, $descripcion); // Registro del evento

            echo json_encode(['status' => 'success', 'message' => 'La asignación ha sido eliminada correctamente.']);
            exit;
        } else {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'No se encontró la asignación para eliminar.']);
            exit;
        }
    } catch (Exception $exception) {
        $pdo->rollBack();
        error_log("Error en delete.php: " . $exception->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la asignación.']);
        exit;
    }
}
