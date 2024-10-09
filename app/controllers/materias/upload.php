<?php

include('../../../app/config.php');

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    echo "Archivo subido correctamente: " . htmlspecialchars($_FILES['file']['name']) . "<br>";

    /* Verificar si el archivo es un CSV */
    if (($handle = fopen($file, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            /* Asignar valores desde el CSV */
            $subject_name = isset($data[0]) ? trim($data[0]) : null; 
            $is_specialization = isset($data[1]) && $data[1] === '1' ? 1 : 0; 
            $hours_consecutive = isset($data[2]) ? intval(trim($data[2])) : 0; 
            $weekly_hours = isset($data[3]) ? intval(trim($data[3])) : 0;
            $program_id = isset($data[4]) ? intval(trim($data[4])) : null; // Nuevo campo para el ID del programa
            $term_id = isset($data[5]) ? intval(trim($data[5])) : null; // Nuevo campo para el ID del cuatrimestre

            /* Validar que el nombre de la materia no esté vacío */
            if (empty($subject_name)) {
                echo "Error: el nombre de la materia no puede estar vacío.<br>";
                continue; 
            }

            /* Validar que el program_id y term_id existan si están presentes */
            $valid_program = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE program_id = :program_id");
            $valid_program->bindParam(':program_id', $program_id);
            $valid_program->execute();
            $program_exists = $valid_program->fetchColumn();

            $valid_term = $pdo->prepare("SELECT COUNT(*) FROM terms WHERE term_id = :term_id");
            $valid_term->bindParam(':term_id', $term_id);
            $valid_term->execute();
            $term_exists = $valid_term->fetchColumn();

            if ($program_id && !$program_exists) {
                echo "Error: el programa con ID $program_id no existe.<br>";
                continue; 
            }

            if ($term_id && !$term_exists) {
                echo "Error: el cuatrimestre con ID $term_id no existe.<br>";
                continue; 
            }

            /* Insertar en la base de datos */
            $sentencia = $pdo->prepare('INSERT INTO subjects (subject_name, is_specialization, hours_consecutive, weekly_hours, program_id, term_id, fyh_creacion, estado) VALUES (:subject_name, :is_specialization, :hours_consecutive, :weekly_hours, :program_id, :term_id, :fyh_creacion, :estado)');

            $fecha_creacion = date('Y-m-d H:i:s');
            $estado = '1'; 

            /* Vincular las variables */
            $sentencia->bindParam(':subject_name', $subject_name);
            $sentencia->bindParam(':is_specialization', $is_specialization);
            $sentencia->bindParam(':hours_consecutive', $hours_consecutive); 
            $sentencia->bindParam(':weekly_hours', $weekly_hours);
            $sentencia->bindParam(':program_id', $program_id);
            $sentencia->bindParam(':term_id', $term_id);
            $sentencia->bindParam(':fyh_creacion', $fecha_creacion);
            $sentencia->bindParam(':estado', $estado);

            try {
                $sentencia->execute();
            } catch (Exception $exception) {
                echo "Error al registrar la materia: " . $exception->getMessage() . "<br>";
            }
        }
        fclose($handle);
        
        session_start();
        $_SESSION['mensaje'] = "Materias registradas con éxito.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/materias");
        exit; 
    } else {
        echo "Error: no se pudo abrir el archivo.";
    }
} else {
    echo "No se ha seleccionado ningún archivo.";
}
?>
