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

            $group_name = $data[0];
            $program_name = $data[1];
            $period = $data[2];  
            $year = $data[3];   
            $volume = $data[4];  

            
            $stmt_program = $pdo->prepare('SELECT program_id FROM programs WHERE program_name = :program_name');
            $stmt_program->bindParam(':program_name', $program_name);
            $stmt_program->execute();
            $program = $stmt_program->fetch(PDO::FETCH_ASSOC);
            if (!$program) {
                echo "Error: Programa no encontrado para el nombre: " . $program_name . "<br>";
                continue;
            }
            $program_id = $program['program_id'];

            
            $sentencia = $pdo->prepare('INSERT INTO `groups` (group_name, program_id, period, year, volume, fyh_creacion, estado) VALUES (:group_name, :program_id, :period, :year, :volume, NOW(), "1")');
            $sentencia->bindParam(':group_name', $group_name);
            $sentencia->bindParam(':program_id', $program_id);
            $sentencia->bindParam(':period', $period); 
            $sentencia->bindParam(':year', $year); 
            $sentencia->bindParam(':volume', $volume); 

            try {
                $sentencia->execute();
                echo "Grupo registrado: " . $group_name . "<br>";
            } catch (Exception $exception) {
                echo "Error al registrar el grupo: " . $exception->getMessage() . "<br>";
            }
        }
        fclose($handle);

        session_start();
        $_SESSION['mensaje'] = "Grupos registrados con exito.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/grupos");
        die();
    } else {
        echo "No se pudo abrir el archivo.";
    }
} else {
    echo "No se ha seleccionado ningún archivo.";
}
?>
