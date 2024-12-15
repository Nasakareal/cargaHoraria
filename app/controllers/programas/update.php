<?php
include('../../../app/config.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_POST['program_id'], $_POST['program_name']) || empty($_POST['program_id']) || empty(trim($_POST['program_name']))) {
    $_SESSION['mensaje'] = "Datos incompletos. Verifique que el nombre del programa esté presente.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . '/admin/programas');
    exit();
}

$program_id = $_POST['program_id'];
$program_name = trim(mb_strtoupper($_POST['program_name']));
$fechaHora = date('Y-m-d H:i:s');

try {
    $query = $pdo->prepare("
        UPDATE programs 
        SET program_name = :program_name, 
            fyh_actualizacion = :fyh_actualizacion 
        WHERE program_id = :program_id
    ");
    $query->bindParam(':program_name', $program_name);
    $query->bindParam(':fyh_actualizacion', $fechaHora);
    $query->bindParam(':program_id', $program_id);

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

header('Location: ' . APP_URL . '/admin/programas');
exit();
