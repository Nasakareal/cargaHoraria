<?php
include('../../../app/config.php');

/* Leer datos enviados */
$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'];
$estado = $data['estado'];

/* Actualizar el estado del salón */
$query = $pdo->prepare("UPDATE reparaciones_salones SET estado = :estado WHERE id_salon = :id");
$query->bindParam(':estado', $estado);
$query->bindParam(':id', $id);

if ($query->execute()) {
    http_response_code(200);
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado.']);
}
?>
