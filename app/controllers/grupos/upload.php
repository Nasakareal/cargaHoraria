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
            $term_name = $data[2];  // Cuatrimestre
            $year = $data[3];
            $volume = $data[4];
            $turn_name = $data[5];  // Turno

            // Buscar el ID del programa educativo
            $stmt_program = $pdo->prepare('SELECT program_id FROM programs WHERE program_name = :program_name');
            $stmt_program->bindParam(':program_name', $program_name);
            $stmt_program->execute();
            $program = $stmt_program->fetch(PDO::FETCH_ASSOC);
            if (!$program) {
                echo "Error: Programa no encontrado para el nombre: " . $program_name . "<br>";
                continue;
            }
            $program_id = $program['program_id'];

            // Buscar el ID del cuatrimestre
            $stmt_term = $pdo->prepare('SELECT term_id FROM terms WHERE term_name = :term_name');
            $stmt_term->bindParam(':term_name', $term_name);
            $stmt_term->execute();
            $term = $stmt_term->fetch(PDO::FETCH_ASSOC);
            if (!$term) {
                echo "Error: Cuatrimestre no encontrado para el nombre: " . $term_name . "<br>";
                continue;
            }
            $term_id = $term['term_id'];

            // Buscar el ID del turno
            $stmt_turn = $pdo->prepare('SELECT shift_id FROM shifts WHERE shift_name = :turn_name');
            $stmt_turn->bindParam(':turn_name', $turn_name);
            $stmt_turn->execute();
            $turn = $stmt_turn->fetch(PDO::FETCH_ASSOC);
            if (!$turn) {
                echo "Error: Turno no encontrado para el nombre: " . $turn_name . "<br>";
                continue;
            }
            $turn_id = $turn['shift_id'];

            // Preparar la consulta SQL para insertar el grupo
            $sentencia = $pdo->prepare('INSERT INTO `groups` 
                (group_name, program_id, term_id, year, volume, turn_id, fyh_creacion, estado) 
                VALUES (:group_name, :program_id, :term_id, :year, :volume, :turn_id, NOW(), "1")');

            // Vincular los parámetros
            $sentencia->bindParam(':group_name', $group_name);
            $sentencia->bindParam(':program_id', $program_id);
            $sentencia->bindParam(':term_id', $term_id);  // Cuatrimestre
            $sentencia->bindParam(':year', $year);
            $sentencia->bindParam(':volume', $volume);
            $sentencia->bindParam(':turn_id', $turn_id);  // Turno

            try {
                $sentencia->execute();
                echo "Grupo registrado: " . $group_name . "<br>";
            } catch (Exception $exception) {
                echo "Error al registrar el grupo: " . $exception->getMessage() . "<br>";
            }
        }
        fclose($handle);

        session_start();
        $_SESSION['mensaje'] = "Grupos registrados con éxito.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/grupos");
        die();
    } else {
        echo "No se pudo abrir el archivo.";
    }
} else {
    echo "No se ha seleccionado ningún archivo.";
}