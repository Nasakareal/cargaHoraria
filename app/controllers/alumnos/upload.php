<?php
session_start();
include('../../../app/config.php');

if (isset($_FILES['file'])) {
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file']['tmp_name'];
        echo "Archivo subido correctamente: " . htmlspecialchars($_FILES['file']['name']) . "<br>";

        if (($handle = fopen($file, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // Limpieza de datos
                $data = array_map('trim', $data);

                // Extraer datos
                if (count($data) < 4) {
                    echo "Error: Línea con datos incompletos.<br>";
                    continue;
                }

                $student_name = $data[0]; // Nombres completos
                $group_name = $data[1];    // Grupo
                $term_name = $data[2];     // Cuatrimestre
                $program_name = $data[3];  // Programa

                // Verificar si el grupo existe
                $group_id_query = $pdo->prepare("SELECT group_id FROM `groups` WHERE group_name = :group_name");
                $group_id_query->execute(['group_name' => $group_name]);
                $group_id = $group_id_query->fetchColumn();

                // Verificar si el cuatrimestre existe
                $term_id_query = $pdo->prepare("SELECT term_id FROM terms WHERE term_name = :term_name");
                $term_id_query->execute(['term_name' => $term_name]);
                $term_id = $term_id_query->fetchColumn();

                // Verificar si el programa existe
                $program_id_query = $pdo->prepare("SELECT program_id FROM programs WHERE program_name = :program_name");
                $program_id_query->execute(['program_name' => $program_name]);
                $program_id = $program_id_query->fetchColumn();

                // Validar existencia de grupo, cuatrimestre y programa
                if (!$group_id || !$term_id || !$program_id) {
                    echo "Error: El grupo '$group_name', el cuatrimestre '$term_name' o el programa '$program_name' no existen.<br>";
                    continue;
                }

                // Inserción del estudiante
                $insert_query = $pdo->prepare("INSERT INTO students (student_name, group_id, term_id, program_id, fyh_creacion, estado) VALUES (:student_name, :group_id, :term_id, :program_id, NOW(), '1')");
                $insert_query->execute([
                    'student_name' => $student_name,
                    'group_id' => $group_id,
                    'term_id' => $term_id,
                    'program_id' => $program_id
                ]);

                echo "Estudiante '$student_name' agregado correctamente.<br>";
            }
            fclose($handle);
        } else {
            echo "Error: no se pudo abrir el archivo.";
        }
    } else {
        echo "Error en la carga: " . $_FILES['file']['error'];
    }
} else {
    echo "No se ha seleccionado ningún archivo o hubo un error en la carga.";
}
?>
