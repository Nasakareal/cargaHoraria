<?php
include('../../../app/config.php');

/* Iniciar sesión si no está activa */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Verificar si el usuario tiene permisos de administrador */
if (!isset($_SESSION['sesion_rol']) || $_SESSION['sesion_rol'] != 1) {
    $_SESSION['mensaje'] = "No tienes permisos para registrar laboratorios. Solo los administradores pueden realizar esta acción.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/laboratorios");
    exit();
}

/* Verificar si se enviaron los datos necesarios */
if (isset($_POST['lab_name'])) {
    
    $lab_name = strtoupper(trim($_POST['lab_name']));
    $description = strtoupper(trim($_POST['description'] ?? ''));

    try {
        /* Verificar si ya existe un laboratorio con el mismo nombre */
        $sql_check = "SELECT COUNT(*) FROM labs WHERE lab_name = :lab_name";
        $query_check = $pdo->prepare($sql_check);
        $query_check->execute([':lab_name' => $lab_name]);
        $exists = $query_check->fetchColumn();

        if ($exists > 0) {
            /* Si el nombre ya existe, mostrar mensaje de error */
            $_SESSION['mensaje'] = "El laboratorio con este nombre ya existe.";
            $_SESSION['icono'] = "error";
            header('Location: ' . APP_URL . "/admin/laboratorios");
            exit();
        }

        /* Consulta SQL para insertar el laboratorio */
        $sql_insert = "INSERT INTO labs (lab_name, description, fyh_creacion)
                       VALUES (:lab_name, :description, NOW())";

        $query_insert = $pdo->prepare($sql_insert);

        /* Ejecutar la consulta con los parámetros */
        $query_insert->execute([
            ':lab_name' => $lab_name,
            ':description' => $description,
        ]);

        /* Redirigir con mensaje de éxito */
        $_SESSION['mensaje'] = "Laboratorio registrado con éxito.";
        $_SESSION['icono'] = "success";
        header('Location: ' . APP_URL . "/admin/laboratorios");
        exit();
    } catch (PDOException $e) {
        /* Mostrar mensaje de error en caso de fallo */
        $_SESSION['mensaje'] = "Error al registrar el laboratorio: " . $e->getMessage();
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . "/admin/laboratorios");
        exit();
    }
} else {
    /* Redirigir si faltan datos */
    $_SESSION['mensaje'] = "Datos incompletos para registrar el laboratorio.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/laboratorios");
    exit();
}