<?php
include('../../../app/config.php');

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    /* Verificar si el archivo es un CSV */
    if (($handle = fopen($file, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $teacher_name = $data[0]; 
            $subject_name = $data[1]; 
            $hours_weekly = $data[2]; 

            /* Insertar o obtener el id del profesor */
            $sentencia = $pdo->prepare('INSERT INTO teachers (teacher_name) VALUES (:teacher_name) ON DUPLICATE KEY UPDATE teacher_id=LAST_INSERT_ID(teacher_id)');
            $sentencia->bindParam(':teacher_name', $teacher_name);
            $sentencia->execute();
            $teacher_id = $pdo->lastInsertId();
            
            /* Verificar si la materia ya existe */
            $sentencia = $pdo->prepare('SELECT subject_id FROM subjects WHERE subject_name = :subject_name');
            $sentencia->bindParam(':subject_name', $subject_name);
            $sentencia->execute();
            $subject = $sentencia->fetch();

            if (!$subject) {
                /* Si no existe, insertar la nueva materia */
                $sentencia = $pdo->prepare('INSERT INTO subjects (subject_name) VALUES (:subject_name)');
                $sentencia->bindParam(':subject_name', $subject_name);
                $sentencia->execute();
                $subject_id = $pdo->lastInsertId();
            } else {
                $subject_id = $subject['subject_id'];
            }

            /* Insertar la relación en teacher_subjects */
            $sentencia = $pdo->prepare('INSERT INTO teacher_subjects (teacher_id, subject_id, weekly_hours) VALUES (:teacher_id, :subject_id, :weekly_hours)');
            $sentencia->bindParam(':teacher_id', $teacher_id);
            $sentencia->bindParam(':subject_id', $subject_id);
            $sentencia->bindParam(':weekly_hours', $hours_weekly);

            try {
                $sentencia->execute();
            } catch (Exception $exception) {
                
                echo "Error al registrar: " . $exception->getMessage();
            }
        }
        fclose($handle);

        
        session_start();
        $_SESSION['mensaje'] = "Profesores registrados con éxito.";
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/profesores");
        exit; 
    }
} else {
    /* Manejo de errores si no se seleccionó ningún archivo */
    echo "No se ha seleccionado ningún archivo.";
}
?>
