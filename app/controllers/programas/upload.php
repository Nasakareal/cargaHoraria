<?php
include('../../../app/config.php');

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    /* Verificar si el archivo es un CSV */

    if (($handle = fopen($file, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $program_name = $data[0]; 

            /* Insertar en la base de datos */

            $sentencia = $pdo->prepare('INSERT INTO programs (program_name) VALUES (:program_name)');
            $sentencia->bindParam(':program_name', $program_name);

            try {
                $sentencia->execute();
            } catch (Exception $exception) {

                /* Manejo de errores */

                echo "Error al registrar: " . $exception->getMessage();
            }
        }
        fclose($handle);

        /* Mensaje de éxito */
        session_start();
        $_SESSION['mensaje'] = "Programas registrados con éxito.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/programas");
    }
} else {

    /* Manejo de errores si no se seleccionó ningún archivo */
    echo "No se ha seleccionado ningún archivo.";
}
?>
