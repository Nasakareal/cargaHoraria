<?php
include('../../../app/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $user_id = $_POST['user_id'];
    $subject = $_POST['report_subject'];
    $message = $_POST['report_message'];

    try {
        $stmt = $pdo->prepare("INSERT INTO reports (user_id, report_message, created_at, is_read) VALUES (:user_id, :message, NOW(), 0)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Reporte enviado correctamente.";
            $_SESSION['icono'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al enviar el reporte. Intente nuevamente.";
            $_SESSION['icono'] = "error";
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error: " . $e->getMessage();
        $_SESSION['icono'] = "error";
    }

    
    header('Location: ' . APP_URL . '/portal/reportes/');
    exit();
}
