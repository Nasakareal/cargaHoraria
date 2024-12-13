<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assignment_id = isset($_POST['assignment_id']) ? $_POST['assignment_id'] : '';
    $group_id = isset($_POST['group_id']) ? $_POST['group_id'] : '';

    // Agrega un log para verificar los valores recibidos
    file_put_contents("debug_delete.txt", "assignment_id=$assignment_id, group_id=$group_id\n", FILE_APPEND);

    if (empty($assignment_id) || empty($group_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
        exit;
    }

    try {
        // Verificar si existe la asignación
        $check_exists = $pdo->prepare("SELECT * FROM manual_schedule_assignments 
            WHERE assignment_id = :assignment_id AND group_id = :group_id");
        $check_exists->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        $check_exists->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $check_exists->execute();

        if ($check_exists->rowCount() == 0) {
            echo json_encode(['status' => 'error', 'message' => 'La asignación no existe.']);
            exit;
        }

        $pdo->beginTransaction();

        // Verifica que la consulta DELETE está correcta
        $consulta_delete = $pdo->prepare("DELETE FROM manual_schedule_assignments 
            WHERE assignment_id = :assignment_id AND group_id = :group_id");
        $consulta_delete->bindParam(':assignment_id', $assignment_id, PDO::PARAM_INT);
        $consulta_delete->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $consulta_delete->execute();

        if ($consulta_delete->rowCount() > 0) {
            // Si se eliminó alguna fila
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'La asignación ha sido eliminada correctamente.']);
        } else {
            // Si no se eliminó nada (verifica si assignment_id y group_id existen)
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'No se encontró la asignación para eliminar.']);
        }
    } catch (Exception $exception) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $exception->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se ha recibido una solicitud POST.']);
}
?>
