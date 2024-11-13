<?php
include('../../../app/config.php');

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo "Error al cargar el archivo.";
        die();
    }

    if (($handle = fopen($file, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            echo "Datos leídos: " . implode(", ", $data) . "<br>";

            $classroom_name = $data[0];
            $building = $data[1];
            $floor = $data[2];  

            /* Verificar si el salón ya existe */
            $stmt_check = $pdo->prepare('SELECT classroom_id FROM classrooms WHERE classroom_name = :classroom_name AND building = :building AND floor = :floor');
            $stmt_check->bindParam(':classroom_name', $classroom_name);
            $stmt_check->bindParam(':building', $building);
            $stmt_check->bindParam(':floor', $floor);
            $stmt_check->execute();
            $existing_classroom = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($existing_classroom) {
                echo "El salón ya existe: " . $classroom_name . "<br>";
                continue;
            }

            $sentencia = $pdo->prepare('INSERT INTO `classrooms` (classroom_name, building, floor, fyh_creacion, estado) VALUES (:classroom_name, :building, :floor, NOW(), "1")');
            $sentencia->bindParam(':classroom_name', $classroom_name);
            $sentencia->bindParam(':building', $building);
            $sentencia->bindParam(':floor', $floor);

            try {
                $sentencia->execute();
                echo "Salón registrado: " . $classroom_name . "<br>";
            } catch (Exception $exception) {
                echo "Error al registrar el salón: " . $exception->getMessage() . "<br>";
            }
        }
        fclose($handle);

        session_start();
        $_SESSION['mensaje'] = "Salones registrados con éxito.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/salones");
        die();
    } else {
        echo "No se pudo abrir el archivo.";
    }
} else {
    echo "No se ha seleccionado ningún archivo.";
}