<?php
include('../../../app/config.php');

/* Verificar si se enviaron los datos necesarios */
if (isset($_POST['lab_id'], $_POST['lab_name'])) {
    $lab_id = $_POST['lab_id'];
    $lab_name = $_POST['lab_name'];
    $description = $_POST['description'] ?? null;

    try {
        /* Verificar si ya existe un laboratorio con el mismo nombre */
        $sql_check = "SELECT COUNT(*) FROM labs WHERE lab_name = :lab_name AND lab_id != :lab_id";
        $query_check = $pdo->prepare($sql_check);
        $query_check->execute([
            ':lab_name' => $lab_name,
            ':lab_id' => $lab_id,
        ]);
        $exists = $query_check->fetchColumn();

        if ($exists > 0) {
            /* Si ya existe, enviar mensaje de error */
            session_start();
            $_SESSION['mensaje'] = "El laboratorio con este nombre ya existe.";
            $_SESSION['icono'] = "error";
            header('Location: ' . APP_URL . "/admin/laboratorios");
            exit;
        }

        /* Consulta SQL para actualizar los datos del laboratorio */
        $sql_update = "UPDATE labs
                       SET lab_name = :lab_name,
                           description = :description,
                           fyh_actualizacion = NOW()
                       WHERE lab_id = :lab_id";

        $query_update = $pdo->prepare($sql_update);

        /* Ejecutar la consulta con los parámetros correspondientes */
        $query_update->execute([
            ':lab_name' => $lab_name,
            ':description' => $description,
            ':lab_id' => $lab_id,
        ]);

        /* Mensaje de éxito */
        session_start();
        $_SESSION['mensaje'] = "Se ha actualizado con éxito";
        $_SESSION['icono'] = "success";
        header('Location: ' . APP_URL . "/admin/laboratorios");
        exit;
    } catch (PDOException $e) {
        session_start();
        $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
        $_SESSION['icono'] = "error";
        header('Location: ' . APP_URL . "/admin/laboratorios");
        exit;
    }
} else {
    /* Error por datos faltantes */
    session_start();
    $_SESSION['mensaje'] = "Datos incompletos para realizar la actualización.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/laboratorios");
    exit;
}
