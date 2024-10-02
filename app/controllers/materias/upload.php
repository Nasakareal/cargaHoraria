<?php
// Código: upload.php
include('../../../app/config.php');

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    echo "Archivo subido correctamente: " . $_FILES['file']['name'] . "<br>";

    /* Verificar si el archivo es un CSV */
    if (($handle = fopen($file, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            /* Asignar valores desde el CSV */
            $subject_name = isset($data[0]) ? trim($data[0]) : null; 
            $is_specialization = isset($data[1]) && $data[1] === '1' ? 1 : 0; 
            $hours_consecutive = isset($data[2]) ? intval(trim($data[2])) : 0; 

            /* Validar que el nombre de la materia no esté vacío */
            if (empty($subject_name)) {
                echo "Error: el nombre de la materia no puede estar vacío.<br>";
                continue; 
            }

            /* Insertar en la base de datos */
            $sentencia = $pdo->prepare('INSERT INTO subjects (subject_name, is_specialization, hours_consecutive, fyh_creacion, estado) VALUES (:subject_name, :is_specialization, :hours_consecutive, :fyh_creacion, :estado)');

            
            $fecha_creacion = date('Y-m-d H:i:s');
            $estado = '1'; 

            /* Vincular las variables */
            $sentencia->bindParam(':subject_name', $subject_name);
            $sentencia->bindParam(':is_specialization', $is_specialization);
            $sentencia->bindParam(':hours_consecutive', $hours_consecutive); 
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
