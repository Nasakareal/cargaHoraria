<?php
include('../../../app/config.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_POST['program_id'], $_POST['program_name'], $_POST['area']) || empty($_POST['program_id']) || empty(trim($_POST['program_name'])) || empty(trim($_POST['area']))) {
    $_SESSION['mensaje'] = "Datos incompletos. Verifique que el nombre del programa y el área estén presentes.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . '/admin/programas');
    exit();
}

$program_id = $_POST['program_id'];
$program_name = trim(mb_strtoupper($_POST['program_name']));
$new_area = trim(mb_strtoupper($_POST['area']));
$fechaHora = date('Y-m-d H:i:s');

try {
    $query_area_actual = $pdo->prepare("
        SELECT area FROM programs WHERE program_id = :program_id
    ");
    $query_area_actual->bindParam(':program_id', $program_id);
    $query_area_actual->execute();
    $current_area = $query_area_actual->fetchColumn();

    $query_update_program = $pdo->prepare("
        UPDATE programs 
        SET program_name = :program_name, 
            area = :area, 
            fyh_actualizacion = :fyh_actualizacion 
        WHERE program_id = :program_id
    ");
    $query_update_program->bindParam(':program_name', $program_name);
    $query_update_program->bindParam(':area', $new_area);
    $query_update_program->bindParam(':fyh_actualizacion', $fechaHora);
    $query_update_program->bindParam(':program_id', $program_id);

    if (!$query_update_program->execute()) {
        throw new Exception("No se pudo actualizar el programa.");
    }

    if ($current_area !== $new_area) {
        $query_update_groups = $pdo->prepare("
            UPDATE `groups` 
            SET area = :new_area 
            WHERE area = :current_area
        ");
        $query_update_groups->bindParam(':new_area', $new_area);
        $query_update_groups->bindParam(':current_area', $current_area);

        if (!$query_update_groups->execute()) {
            throw new Exception("No se pudo actualizar la tabla groups.");
        }

        $query_update_teachers = $pdo->prepare("
            UPDATE teachers 
            SET area = :new_area 
            WHERE area = :current_area
        ");
        $query_update_teachers->bindParam(':new_area', $new_area);
        $query_update_teachers->bindParam(':current_area', $current_area);

        if (!$query_update_teachers->execute()) {
            throw new Exception("No se pudo actualizar la tabla teachers.");
        }
    }

    $_SESSION['mensaje'] = "El programa y sus referencias se actualizaron correctamente.";
    $_SESSION['icono'] = "success";
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['icono'] = "error";
}

header('Location: ' . APP_URL . '/admin/programas');
exit();
