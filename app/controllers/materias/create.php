<?php
include('../../../app/config.php');
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name']);
    $hours_consecutive = (int) $_POST['hours_consecutive'];
    $is_specialization = isset($_POST['is_specialization']) ? 1 : 0;


    $query = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE subject_name = :subject_name");
    $query->bindParam(':subject_name', $subject_name);
    $query->execute();

    if ($query->fetchColumn() > 0) {
        $_SESSION['mensaje'] = "La materia \"$subject_name\" ya existe en la base de datos.";
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . "/admin/materias");
        exit;
    } else {
        
        $sentencia = $pdo->prepare('INSERT INTO subjects (subject_name, hours_consecutive, is_specialization) VALUES (:subject_name, :hours_consecutive, :is_specialization)');
        $sentencia->bindParam(':subject_name', $subject_name);
        $sentencia->bindParam(':hours_consecutive', $hours_consecutive);
        $sentencia->bindParam(':is_specialization', $is_specialization);

        try {
            $sentencia->execute();
            $_SESSION['mensaje'] = "Materia \"$subject_name\" creada con éxito.";
            $_SESSION['icono'] = "success";
            header('Location: ' . APP_URL . "/admin/materias");
            exit;
        } catch (Exception $exception) {
            $_SESSION['mensaje'] = "Ocurrió un error: " . $exception->getMessage();
            $_SESSION['icono'] = "error";
            header('Location: ' . APP_URL . "/admin/materias");
            exit;
        }
    }
}
?>
