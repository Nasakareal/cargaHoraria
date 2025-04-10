<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/config.php');
require_once('../../../app/registro_eventos.php');

ini_set('log_errors', 1);
ini_set('error_log', 'C:/wamp/logs/php_error.log');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

date_default_timezone_set('America/Mexico_City');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Método de solicitud no permitido: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['status' => 'error', 'message' => 'Método de solicitud no permitido.']);
    exit;
}

$required_fields = ['assignment_id', 'schedule_day', 'start_time', 'end_time'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    error_log("Faltan campos en update_assignment.php: " . implode(', ', $missing_fields) . ". Datos recibidos: " . print_r($_POST, true));
    echo json_encode([
        'status'  => 'error',
        'message' => 'Faltan datos requeridos: ' . implode(', ', $missing_fields) . '.'
    ]);
    exit;
}

$assignment_id = intval($_POST['assignment_id']);
$schedule_day  = trim($_POST['schedule_day']);
$start_time    = trim($_POST['start_time']);
$end_time      = trim($_POST['end_time']);

if (!preg_match('/^(2[0-3]|[01]?[0-9]):([0-5][0-9]):([0-5][0-9])$/', $start_time)) {
    error_log("Formato de start_time inválido en update_assignment.php: " . $start_time);
    echo json_encode(['status' => 'error', 'message' => 'Formato de hora de inicio inválido.']);
    exit;
}

if (!preg_match('/^(2[0-3]|[01]?[0-9]):([0-5][0-9]):([0-5][0-9])$/', $end_time)) {
    error_log("Formato de end_time inválido en update_assignment.php: " . $end_time);
    echo json_encode(['status' => 'error', 'message' => 'Formato de hora de fin inválido.']);
    exit;
}

$valid_days = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
if (!in_array(strtolower($schedule_day), $valid_days)) {
    error_log("Día inválido en update_assignment.php: " . $schedule_day);
    echo json_encode(['status' => 'error', 'message' => 'Día inválido proporcionado.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Obtener el registro actual de schedule_assignments y bloquearlo
    $query_get_assignment = $pdo->prepare("
        SELECT * 
        FROM schedule_assignments 
        WHERE assignment_id = :assignment_id
        FOR UPDATE
    ");
    $query_get_assignment->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
    $query_get_assignment->execute();
    $current_assignment = $query_get_assignment->fetch(PDO::FETCH_ASSOC);

    if (!$current_assignment) {
        error_log("Asignación no encontrada en update_assignment.php: ID " . $assignment_id);
        echo json_encode(['status' => 'error', 'message' => 'La asignación actual no existe.']);
        $pdo->rollBack();
        exit;
    }

    // Guardar los valores antiguos que usaremos como criterio para actualizar la tabla manual_schedule_assignments
    $old_schedule_day = $current_assignment['schedule_day'];
    $old_start_time   = $current_assignment['start_time'];
    
    // Datos del registro actual
    $subject_id   = intval($current_assignment['subject_id']);
    $group_id     = intval($current_assignment['group_id']);
    $tipo_espacio = strtolower($current_assignment['tipo_espacio']);
    $lab_id       = intval($current_assignment['lab_id']);
    $aula_id      = intval($current_assignment['classroom_id']);
    $teacher_id   = intval($current_assignment['teacher_id']);

    // Verificar conflictos de espacio (en laboratorio o aula)
    if ($tipo_espacio === 'laboratorio') {
        $query_verificar = $pdo->prepare("
            SELECT assignment_id 
            FROM schedule_assignments 
            WHERE schedule_day = :schedule_day 
              AND ((:start_time < end_time AND :end_time > start_time))
              AND lab_id = :lab_id
              AND assignment_id != :assignment_id
              AND estado = 'activo'
        ");
        $query_verificar->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
    } elseif ($tipo_espacio === 'aula') {
        $query_verificar = $pdo->prepare("
            SELECT assignment_id 
            FROM schedule_assignments 
            WHERE schedule_day = :schedule_day 
              AND ((:start_time < end_time AND :end_time > start_time))
              AND classroom_id = :aula_id
              AND assignment_id != :assignment_id
              AND estado = 'activo'
        ");
        $query_verificar->bindParam(':aula_id', $aula_id, PDO::PARAM_INT);
    } else {
        error_log("Tipo de espacio inválido en update_assignment.php: " . $tipo_espacio);
        echo json_encode(['status' => 'error', 'message' => 'Tipo de espacio inválido.']);
        $pdo->rollBack();
        exit;
    }

    $query_verificar->bindParam(':schedule_day', $schedule_day, PDO::PARAM_STR);
    $query_verificar->bindParam(':start_time', $start_time, PDO::PARAM_STR);
    $query_verificar->bindParam(':end_time', $end_time, PDO::PARAM_STR);
    $query_verificar->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
    $query_verificar->execute();

    if ($query_verificar->rowCount() > 0) {
        error_log("Conflicto de espacio en update_assignment.php: Asignación ID " . $assignment_id);
        echo json_encode([
            'status'  => 'error',
            'message' => 'El espacio seleccionado ya está ocupado en el horario indicado.'
        ]);
        $pdo->rollBack();
        exit;
    }

    if ($teacher_id > 0) {
        $query_teacher_conflict = $pdo->prepare("
            SELECT assignment_id 
            FROM schedule_assignments 
            WHERE teacher_id = :teacher_id
              AND schedule_day = :schedule_day
              AND ((:start_time < end_time AND :end_time > start_time))
              AND estado = 'activo'
              AND assignment_id != :assignment_id
        ");
        $query_teacher_conflict->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
        $query_teacher_conflict->bindParam(':schedule_day', $schedule_day, PDO::PARAM_STR);
        $query_teacher_conflict->bindParam(':start_time', $start_time, PDO::PARAM_STR);
        $query_teacher_conflict->bindParam(':end_time', $end_time, PDO::PARAM_STR);
        $query_teacher_conflict->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        $query_teacher_conflict->execute();

        if ($query_teacher_conflict->rowCount() > 0) {
            error_log("Conflicto de profesor en update_assignment.php: Asignación ID " . $assignment_id);
            echo json_encode([
                'status'  => 'error',
                'message' => 'El profesor ya tiene una asignación en el horario seleccionado.'
            ]);
            $pdo->rollBack();
            exit;
        }
    }

    // Actualizar schedule_assignments usando el assignment_id
    $query_update = $pdo->prepare("
        UPDATE schedule_assignments 
        SET schedule_day      = :new_schedule_day,
            start_time        = :new_start_time,
            end_time          = :new_end_time,
            fyh_actualizacion = :fyh_actualizacion
        WHERE assignment_id   = :assignment_id
    ");

    $current_datetime = date('Y-m-d H:i:s');
    $query_update->bindParam(':new_schedule_day', $schedule_day, PDO::PARAM_STR);
    $query_update->bindParam(':new_start_time', $start_time, PDO::PARAM_STR);
    $query_update->bindParam(':new_end_time', $end_time, PDO::PARAM_STR);
    $query_update->bindParam(':fyh_actualizacion', $current_datetime, PDO::PARAM_STR);
    $query_update->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
    $query_update->execute();

    // *** NUEVO: Actualizar manual_schedule_assignments usando subject_id, group_id, y los valores antiguos de schedule_day y start_time ***
    $query_update_manual = $pdo->prepare("
        UPDATE manual_schedule_assignments
        SET schedule_day      = :new_schedule_day,
            start_time        = :new_start_time,
            end_time          = :new_end_time,
            fyh_actualizacion = :fyh_actualizacion
        WHERE subject_id = :subject_id
          AND group_id   = :group_id
          AND schedule_day = :old_schedule_day
          AND start_time   = :old_start_time
          AND estado       = 'activo'
        LIMIT 1
    ");
    $query_update_manual->bindParam(':new_schedule_day', $schedule_day, PDO::PARAM_STR);
    $query_update_manual->bindParam(':new_start_time', $start_time, PDO::PARAM_STR);
    $query_update_manual->bindParam(':new_end_time', $end_time, PDO::PARAM_STR);
    $query_update_manual->bindParam(':fyh_actualizacion', $current_datetime, PDO::PARAM_STR);
    $query_update_manual->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $query_update_manual->bindParam(':group_id', $group_id, PDO::PARAM_INT);
    $query_update_manual->bindParam(':old_schedule_day', $old_schedule_day, PDO::PARAM_STR);
    $query_update_manual->bindParam(':old_start_time', $old_start_time, PDO::PARAM_STR);
    $query_update_manual->execute();
    // *** FIN NUEVO ***

    $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';
    $accion        = 'Actualización de asignación';
    $descripcion   = "Se actualizó la asignación ID $assignment_id. Nuevo día: $schedule_day, Hora de inicio: $start_time, Hora de fin: $end_time.";

    registrarEvento($pdo, $usuario_email, $accion, $descripcion);

    $pdo->commit();
    error_log("Asignación actualizada correctamente en update_assignment.php: ID " . $assignment_id . " con start_time " . $start_time . " y end_time " . $end_time);

    echo json_encode([
        'status'  => 'success',
        'message' => 'La asignación se ha movido correctamente.'
    ]);
    exit;

} catch (Exception $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error en update_assignment.php: " . $exception->getMessage());

    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al mover la asignación.'
    ]);
    exit;
}
