<?php
include('../../../app/config.php');
require_once('../../../app/registro_eventos.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura y limpia los datos
    $subject_name = trim($_POST['subject_name']);
    $subject_name_clean = preg_replace('/\s+/', ' ', $subject_name);
    $hours_consecutive = (int) $_POST['max_consecutive_class_hours'];
    $weekly_hours = (int) $_POST['weekly_hours'];
    $program_id = (int) $_POST['program_id'];
    $term_id = (int) $_POST['term_id'];
    $unidades = (int) $_POST['unidades'];

    // Validación: ¿Ya existe la materia?
    $query = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE subject_name = :subject_name");
    $query->bindParam(':subject_name', $subject_name_clean);
    $query->execute();

    if ($query->fetchColumn() > 0) {
        $_SESSION['mensaje'] = "La materia \"$subject_name_clean\" ya existe en la base de datos.";
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . "/admin/materias");
        exit;
    }

    // Inserción en la base de datos
    $sentencia = $pdo->prepare('INSERT INTO subjects (
        subject_name, 
        max_consecutive_class_hours, 
        weekly_hours, 
        program_id, 
        term_id,
        unidades
    ) VALUES (
        :subject_name, 
        :max_consecutive_class_hours, 
        :weekly_hours, 
        :program_id, 
        :term_id,
        :unidades
    )');

    $sentencia->bindParam(':subject_name', $subject_name_clean);
    $sentencia->bindParam(':max_consecutive_class_hours', $hours_consecutive);
    $sentencia->bindParam(':weekly_hours', $weekly_hours);
    $sentencia->bindParam(':program_id', $program_id);
    $sentencia->bindParam(':term_id', $term_id);
    $sentencia->bindParam(':unidades', $unidades);

    try {
        if ($sentencia->execute()) {
            $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';
            $accion = 'Registro de materia';
            $descripcion = "Se registró la materia '$subject_name_clean' con $hours_consecutive horas consecutivas y $weekly_hours horas semanales.";

            registrarEvento($pdo, $usuario_email, $accion, $descripcion);

            $_SESSION['mensaje'] = "Se ha registrado la materia";
            $_SESSION['icono'] = "success";
            header('Location:' . APP_URL . "/admin/materias");
            exit;
        } else {
            $_SESSION['mensaje'] = "Error: no se ha podido registrar la materia, comuníquese con el área de IT";
            $_SESSION['icono'] = "error";
            header('Location: ' . APP_URL . "/admin/materias");
        }
    } catch (Exception $exception) {
        $_SESSION['mensaje'] = "Error al registrar: " . $exception->getMessage();
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . "/admin/materias");
    }
}
?>
