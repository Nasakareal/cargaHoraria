<?php
include('../../../app/config.php');

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    /* Verificar si el archivo es un CSV */
    if (($handle = fopen($file, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $group_name = $data[0];
            $program_id = $data[1];
            $term_id = $data[2];
            $volume = $data[3];

            /* Inserción en la base de datos */
            $sentencia = $pdo->prepare('INSERT INTO `groups` (group_name, program_id, term_id, volumen_grupo) VALUES (:group_name, :program_id, :term_id, :volume)');
            $sentencia->bindParam(':group_name', $group_name);
            $sentencia->bindParam(':program_id', $program_id);
            $sentencia->bindParam(':term_id', $term_id);
            $sentencia->bindParam(':volume', $volume);

            try {
                $sentencia->execute();
            } catch (Exception $exception) {
                /* Manejo de errores */
                echo "Error al registrar el grupo: " . $exception->getMessage() . "<br>";
            }
        }
        fclose($handle);

        /* Mensaje de éxito */
        session_start();
        $_SESSION['mensaje'] = "Grupos registrados con éxito.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/grupos");
    }
} else {
    /* Manejo de errores si no se seleccionó ningún archivo */
    echo "No se ha seleccionado ningún archivo.";
}