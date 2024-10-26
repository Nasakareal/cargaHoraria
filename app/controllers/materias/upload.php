<?php
include('../../../app/config.php');

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo "Error al cargar el archivo.";
        die();
    }

    $errores = [];       // Array para acumular errores
    $mensajes = [];      // Array para mensajes de depuración

    if (($handle = fopen($file, 'r')) !== FALSE) {
        $row = 0;  // Contador para las filas
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $row++;

            // Ignorar las primeras 2 filas de encabezado y evitar el encabezado "Programa Educativo"
            if ($row <= 2 || trim($data[1]) == 'Programa Educativo') {
                continue;
            }

            // Asignar y limpiar columnas del CSV
            $program_name = trim($data[1]);
            $term_number = isset($data[2]) ? intval(trim($data[2])) : null;
            $subject_name = isset($data[3]) ? trim($data[3]) : null;
            $weekly_hours = isset($data[5]) ? intval(trim($data[5])) : 0;
            $class_hours = isset($data[7]) ? intval(trim($data[7])) : 0;
            $lab_hours = isset($data[8]) ? intval(trim($data[8])) : 0;
            $lab1_hours = isset($data[12]) ? intval(trim($data[12])) : 0;
            $lab2_hours = isset($data[13]) ? intval(trim($data[13])) : 0;
            $lab3_hours = isset($data[14]) ? intval(trim($data[14])) : 0;
            $max_consecutive_class_hours = isset($data[15]) ? intval(trim($data[15])) : 0;
            $max_consecutive_lab_hours = isset($data[16]) ? intval(trim($data[16])) : 0;

            // Validar campos requeridos
            if (empty($program_name) || empty($subject_name) || $term_number <= 0) {
                $errores[] = "Fila $row: Datos insuficientes. Programa: $program_name, Materia: $subject_name, Cuatrimestre: $term_number";
                continue;
            }

            // Convertir número de cuatrimestre a nombre
            $term_names = ['Primero', 'Segundo', 'Tercero', 'Cuarto', 'Quinto', 'Sexto', 'Séptimo', 'Octavo', 'Noveno', 'Decimo', 'Undécimo', 'Duodécimo', 'Decimotercero', 'Decimocuarto', 'Decimoquinto', 'Decimosexto', 'Decimoséptimo', 'Decimoctavo', 'Decimonoveno', 'Vigésimo'];
            $term_name = isset($term_names[$term_number - 1]) ? $term_names[$term_number - 1] : null;

            if (!$term_name) {
                $errores[] = "Error: Cuatrimestre inválido: " . $term_number;
                continue;
            }

            // Obtener el programa de la base de datos (sin duplicar)
            $stmt_program = $pdo->prepare('SELECT program_id FROM programs WHERE program_name = :program_name');
            $stmt_program->bindParam(':program_name', $program_name);
            $stmt_program->execute();
            $program = $stmt_program->fetch(PDO::FETCH_ASSOC);

            if ($program) {
                $program_id = $program['program_id'];
                $mensajes[] = "Programa existente: $program_name (ID: $program_id)";
            } else {
                $errores[] = "Fila $row: Programa '$program_name' no encontrado.";
                continue; // Omitir si el programa no existe
            }

            // Obtener el cuatrimestre de la base de datos (sin duplicar)
            $stmt_term = $pdo->prepare('SELECT term_id FROM terms WHERE term_name = :term_name');
            $stmt_term->bindParam(':term_name', $term_name);
            $stmt_term->execute();
            $term = $stmt_term->fetch(PDO::FETCH_ASSOC);

            if ($term) {
                $term_id = $term['term_id'];
                $mensajes[] = "Cuatrimestre existente: $term_name (ID: $term_id)";
            } else {
                $errores[] = "Fila $row: Cuatrimestre '$term_name' no encontrado.";
                continue; // Omitir si el cuatrimestre no existe
            }

            // Insertar materia con programa y cuatrimestre verificados
            $stmt_subject = $pdo->prepare('INSERT INTO subjects (subject_name, weekly_hours, class_hours, lab_hours, lab1_hours, lab2_hours, lab3_hours, max_consecutive_class_hours, max_consecutive_lab_hours, program_id, term_id, fyh_creacion, estado) 
                                           VALUES (:subject_name, :weekly_hours, :class_hours, :lab_hours, :lab1_hours, :lab2_hours, :lab3_hours, :max_class_hours, :max_lab_hours, :program_id, :term_id, NOW(), "1")');
            $stmt_subject->bindParam(':subject_name', $subject_name);
            $stmt_subject->bindParam(':weekly_hours', $weekly_hours);
            $stmt_subject->bindParam(':class_hours', $class_hours);
            $stmt_subject->bindParam(':lab_hours', $lab_hours);
            $stmt_subject->bindParam(':lab1_hours', $lab1_hours);
            $stmt_subject->bindParam(':lab2_hours', $lab2_hours);
            $stmt_subject->bindParam(':lab3_hours', $lab3_hours);
            $stmt_subject->bindParam(':max_class_hours', $max_consecutive_class_hours);
            $stmt_subject->bindParam(':max_lab_hours', $max_consecutive_lab_hours);
            $stmt_subject->bindParam(':program_id', $program_id);
            $stmt_subject->bindParam(':term_id', $term_id);

            try {
                $stmt_subject->execute();
                $subject_id = $pdo->lastInsertId();
                $mensajes[] = "Materia insertada: $subject_name (Programa ID: $program_id, Cuatrimestre ID: $term_id)";

                // Insertar en la tabla de relación program_term_subjects
                $stmt_relation = $pdo->prepare('INSERT INTO program_term_subjects (program_id, term_id, subject_id) VALUES (:program_id, :term_id, :subject_id)');
                $stmt_relation->bindParam(':program_id', $program_id);
                $stmt_relation->bindParam(':term_id', $term_id);
                $stmt_relation->bindParam(':subject_id', $subject_id);
                $stmt_relation->execute();

                $mensajes[] = "Relación insertada en program_term_subjects: Programa ID: $program_id, Cuatrimestre ID: $term_id, Materia ID: $subject_id";
            } catch (Exception $e) {
                $errores[] = "Error al insertar materia '$subject_name' o en la relación: " . $e->getMessage();
            }
        }
        fclose($handle);

        session_start();
        $_SESSION['mensajes_debug'] = implode("<br>", $mensajes); // Guardar mensajes de depuración en la sesión
        if (!empty($errores)) {
            $_SESSION['mensaje'] = implode("<br>", $errores);
            $_SESSION['icono'] = "error";
        } else {
            $_SESSION['mensaje'] = "Materias registradas con éxito.";
            $_SESSION['icono'] = "success";
        }

        header('Location:' . APP_URL . "/admin/materias");
        die();
    } else {
        echo "No se pudo abrir el archivo.";
    }
} else {
    echo "No se ha seleccionado ningún archivo.";
}
