<?php
include('../../../app/config.php');
require_once('../../../app/registro_eventos.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name']);
    $hours_consecutive = (int) $_POST['max_consecutive_class_hours'];
    $weekly_hours = (int) $_POST['weekly_hours'];

    /* Verifica si la materia ya existe */
    $query = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE subject_name = :subject_name");
    $query->bindParam(':subject_name', $subject_name);
    $query->execute();

    if ($query->fetchColumn() > 0) {
        $_SESSION['mensaje'] = "La materia \"$subject_name\" ya existe en la base de datos.";
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . "/admin/materias");
        exit;
    } else {
        /* Inserta la nueva materia */
        $sentencia = $pdo->prepare('INSERT INTO subjects (subject_name, max_consecutive_class_hours, weekly_hours) VALUES (:subject_name, :max_consecutive_class_hours, :weekly_hours)');
        $sentencia->bindParam(':subject_name', $subject_name);
        $sentencia->bindParam(':max_consecutive_class_hours', $hours_consecutive);
        $sentencia->bindParam(':weekly_hours', $weekly_hours);

        try {
            if ($sentencia->execute()) {
                session_start();

                $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';
                $accion = 'Registro de materia';
                $descripcion = "Se registró la materia '$subject_name' con $hours_consecutive horas consecutivas y $weekly_hours horas semanales. Especialización: " . ($is_specialization ? 'Sí' : 'No') . ".";

                registrarEvento($pdo, $usuario_email, $accion, $descripcion);

                $_SESSION['mensaje'] = "Se ha registrado la materia";
                $_SESSION['icono'] = "success";
                header('Location:' . APP_URL . "/admin/materias");
                exit;
            } else {
                session_start();
                $_SESSION['mensaje'] = "Error: no se ha podido registrar la materia, comuníquese con el área de IT";
                $_SESSION['icono'] = "error";
                header('Location: ' . APP_URL . "/admin/materias");
            }
        } catch (Exception $exception) {
            session_start();
            $_SESSION['mensaje'] = "Error al registrar: " . $exception->getMessage();
            $_SESSION['icono'] = "error";
            header('Location: ' . APP_URL . "/admin/materias");
        }
    }
}
?>
