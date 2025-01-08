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

$required_fields = ['event_id'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    error_log("Faltan campos en eliminar_evento.php: " . implode(', ', $missing_fields) . ". Datos recibidos: " . print_r($_POST, true));
    echo json_encode([
        'status'  => 'error',
        'message' => 'Faltan datos requeridos: ' . implode(', ', $missing_fields) . '.'
    ]);
    exit;
}

$event_id = intval($_POST['event_id']);

if ($event_id <= 0) {
    error_log("ID de evento inválido en eliminar_evento.php: " . $event_id);
    echo json_encode(['status' => 'error', 'message' => 'ID de evento inválido.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $query_get_assignment = $pdo->prepare("
        SELECT * 
        FROM schedule_assignments 
        WHERE assignment_id = :assignment_id
        FOR UPDATE
    ");
    $query_get_assignment->bindParam(':assignment_id', $event_id, PDO::PARAM_INT);
    $query_get_assignment->execute();
    $current_assignment = $query_get_assignment->fetch(PDO::FETCH_ASSOC);

    if (!$current_assignment) {
        error_log("Asignación no encontrada en eliminar_evento.php: ID " . $event_id);
        echo json_encode(['status' => 'error', 'message' => 'La asignación no existe.']);
        $pdo->rollBack();
        exit;
    }

    session_start();
    $usuario_email = $_SESSION['sesion_email'] ?? null;

    if (!$usuario_email) {
        error_log("Usuario no autenticado en eliminar_evento.php.");
        echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado.']);
        $pdo->rollBack();
        exit;
    }

    $query_delete = $pdo->prepare("
        DELETE FROM schedule_assignments 
        WHERE assignment_id = :assignment_id
    ");
    $query_delete->bindParam(':assignment_id', $event_id, PDO::PARAM_INT);
    $query_delete->execute();

    if ($query_delete->rowCount() === 0) {
        error_log("No se pudo eliminar la asignación en eliminar_evento.php: ID " . $event_id);
        echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la asignación.']);
        $pdo->rollBack();
        exit;
    }

    $accion      = 'Eliminación de asignación';
    $descripcion = "Se eliminó la asignación ID $event_id: " . $current_assignment['subject_name'] . " - Grupo " . $current_assignment['group_name'] . ".";
    registrarEvento($pdo, $usuario_email, $accion, $descripcion);

    $pdo->commit();
    error_log("Asignación eliminada correctamente en eliminar_evento.php: ID " . $event_id);

    echo json_encode([
        'status'  => 'success',
        'message' => 'La asignación se ha eliminado correctamente.'
    ]);
    exit;

} catch (Exception $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error en eliminar_evento.php: " . $exception->getMessage());

    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al eliminar la asignación.'
    ]);
    exit;
}
?>
