<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/registro_eventos.php');

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

        // Consultar la asignación en manual_schedule_assignments y obtener datos clave para la eliminación en schedule_assignments
        if ($lab_id) {
            $consulta_existe = $pdo->prepare("
                SELECT * FROM manual_schedule_assignments 
                WHERE assignment_id = :assignment_id 
                  AND group_id = :group_id
                  AND (lab1_assigned = :lab_id OR lab2_assigned = :lab_id)
            ");
            $consulta_existe->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
        } else {
            $consulta_existe = $pdo->prepare("
                SELECT * FROM manual_schedule_assignments 
                WHERE assignment_id = :assignment_id 
                  AND group_id = :group_id
            ");
        }
        $consulta_existe->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        $consulta_existe->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $consulta_existe->execute();

        if ($consulta_existe->rowCount() == 0) {
            echo json_encode(['status' => 'error', 'message' => 'La asignación no existe o no está asociada al laboratorio especificado.']);
            $pdo->rollBack();
            exit;
        }

        // Almacenamos los datos para la eliminación en schedule_assignments
        $datos_manual = $consulta_existe->fetch(PDO::FETCH_ASSOC);
        $subject_id   = $datos_manual['subject_id'];
        $start_time   = $datos_manual['start_time'];
        $end_time     = $datos_manual['end_time'];
        $schedule_day = $datos_manual['schedule_day'];

        // Eliminar de manual_schedule_assignments
        if ($lab_id) {
            $consulta_delete_manual = $pdo->prepare("
                DELETE FROM manual_schedule_assignments 
                WHERE assignment_id = :assignment_id 
                  AND group_id = :group_id
                  AND (lab1_assigned = :lab_id OR lab2_assigned = :lab_id)
            ");
            $consulta_delete_manual->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
        } else {
            $consulta_delete_manual = $pdo->prepare("
                DELETE FROM manual_schedule_assignments 
                WHERE assignment_id = :assignment_id 
                  AND group_id = :group_id
            ");
        }
        $consulta_delete_manual->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        $consulta_delete_manual->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $consulta_delete_manual->execute();

        // Eliminar de schedule_assignments utilizando los datos obtenidos
        if ($lab_id) {
            $consulta_delete_schedule = $pdo->prepare("
                DELETE FROM schedule_assignments 
                WHERE group_id = :group_id 
                  AND subject_id = :subject_id 
                  AND start_time = :start_time 
                  AND end_time = :end_time 
                  AND schedule_day = :schedule_day
                  AND lab_id = :lab_id
            ");
            $consulta_delete_schedule->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
        } else {
            $consulta_delete_schedule = $pdo->prepare("
                DELETE FROM schedule_assignments 
                WHERE group_id = :group_id 
                  AND subject_id = :subject_id 
                  AND start_time = :start_time 
                  AND end_time = :end_time 
                  AND schedule_day = :schedule_day
            ");
        }
        $consulta_delete_schedule->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $consulta_delete_schedule->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
        $consulta_delete_schedule->bindParam(':start_time', $start_time, PDO::PARAM_STR);
        $consulta_delete_schedule->bindParam(':end_time', $end_time, PDO::PARAM_STR);
        $consulta_delete_schedule->bindParam(':schedule_day', $schedule_day, PDO::PARAM_STR);
        $consulta_delete_schedule->execute();

        // Verificar si se eliminó al menos un registro de alguna de las tablas
        $deletedManual = $consulta_delete_manual->rowCount();
        $deletedSchedule = $consulta_delete_schedule->rowCount();

        if ($deletedManual > 0 || $deletedSchedule > 0) {
            $pdo->commit();

            // Registrar el evento de eliminación
            $usuario_email = $_SESSION['sesion_email'] ?? 'mequihua@ut-morelia.edu.mx';
            $accion = 'Eliminación de asignación manual';
            $descripcion = "Se eliminó la asignación manual (ID $assignment_id, grupo $group_id) y la asignación de horario correspondiente (materia: $subject_id, día: $schedule_day, inicio: $start_time, fin: $end_time" . ($lab_id ? ", laboratorio: $lab_id" : "") . ").";

            registrarEvento($pdo, $usuario_email, $accion, $descripcion);

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
?>
