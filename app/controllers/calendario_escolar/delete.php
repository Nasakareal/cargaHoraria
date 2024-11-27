<?php
include('../../../app/config.php');

/* Obtener el ID del calendario desde el formulario */
$id_calendario = filter_input(INPUT_POST, 'id_calendario', FILTER_VALIDATE_INT);

if (!$id_calendario) {
    session_start();
    $_SESSION['mensaje'] = "ID de calendario inválido.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/configuraciones/calendarios");
    exit();
}

try {
    /* Preparar la consulta para eliminar el registro */
    $query = $pdo->prepare("DELETE FROM calendario_escolar WHERE id = :id");
    $query->bindParam(':id', $id_calendario, PDO::PARAM_INT);

    /* Ejecutar la consulta */
    if ($query->execute()) {
        session_start();
        $_SESSION['mensaje'] = "El calendario ha sido eliminado con éxito.";
        $_SESSION['icono'] = "success";
        header('Location: ' . APP_URL . "/admin/configuraciones/calendarios");
        exit();
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se pudo eliminar el calendario. Por favor, intente de nuevo.";
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . "/admin/configuraciones/calendarios");
        exit();
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "Error al eliminar el calendario: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/configuraciones/calendarios");
    exit();
}
?>
