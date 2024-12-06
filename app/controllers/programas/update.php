<?php
include('../../../app/config.php');

// Iniciar sesión si no ha sido iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que se reciban los datos requeridos
if (!isset($_POST['program_id'], $_POST['program_name']) || empty($_POST['program_id']) || empty(trim($_POST['program_name']))) {
    $_SESSION['mensaje'] = "Datos incompletos. Verifique que el nombre del programa esté presente.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . '/admin/programas');
    exit();
}

// Obtener los datos del formulario
$program_id = $_POST['program_id'];
$program_name = trim(mb_strtoupper($_POST['program_name'])); // Convertir a mayúsculas
$fechaHora = date('Y-m-d H:i:s'); // Fecha de actualización

try {
    // Preparar la consulta para actualizar el programa
    $query = $pdo->prepare("
        UPDATE programs 
        SET program_name = :program_name, 
            fyh_actualizacion = :fyh_actualizacion 
        WHERE program_id = :program_id
    ");
    $query->bindParam(':program_name', $program_name);
    $query->bindParam(':fyh_actualizacion', $fechaHora);
    $query->bindParam(':program_id', $program_id);

    // Ejecutar la consulta
    if ($query->execute()) {
        $_SESSION['mensaje'] = "El programa se actualizó correctamente.";
        $_SESSION['icono'] = "success";
    } else {
        throw new Exception("No se pudo actualizar el programa. Intente nuevamente.");
    }
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['icono'] = "error";
}

// Redirigir de vuelta a la lista de programas
header('Location: ' . APP_URL . '/admin/programas');
exit();
