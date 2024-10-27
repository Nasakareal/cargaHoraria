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

            // Ignorar las primeras 3 filas de encabezado
            if ($row <= 3) {
                continue;
            }

            // Asignar y limpiar columnas del CSV, omitiendo la quinta columna
            $program_name = trim($data[0]);
            $term_number = isset($data[1]) ? intval(trim($data[1])) : null;
            $subject_name = isset($data[2]) ? trim($data[2]) : null;
            $weekly_hours = isset($data[3]) ? intval(trim($data[3])) : 0;
            $class_hours = isset($data[5]) ? intval(trim($data[5])) : 0;
            $lab_hours = isset($data[6]) ? intval(trim($data[6])) : 0;
            $max_consecutive_class_hours = isset($data[13]) ? intval(trim($data[13])) : 0;
            $max_consecutive_lab_hours = isset($data[14]) ? intval(trim($data[14])) : 0;

            // Validar campos requeridos
            if (empty($program_name) || empty($subject_name) || $term_number <= 0) {
                $errores[] = "Fila $row: Datos insuficientes. Programa: $program_name, Materia: $subject_name, Cuatrimestre: $term_number";
                continue;
            }

            // Convertir número de cuatrimestre a nombre
            $term_names = ['Primero', 'Segundo', 'Tercero', 'Cuarto', 'Quinto', 'Sexto', 'Séptimo', 'Octavo', 'Noveno', 'Décimo'];
            $term_name = $term_names[$term_number - 1] ?? null;

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
                continue;
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
                continue;
            }

            // Insertar materia con programa y cuatrimestre verificados
            $stmt_subject = $pdo->prepare('INSERT INTO subjects (subject_name, weekly_hours, class_hours, lab_hours, max_consecutive_class_hours, max_consecutive_lab_hours, program_id, term_id, fyh_creacion, estado) 
                                           VALUES (:subject_name, :weekly_hours, :class_hours, :lab_hours, :max_class_hours, :max_lab_hours, :program_id, :term_id, NOW(), "1")');
            $stmt_subject->bindParam(':subject_name', $subject_name);
            $stmt_subject->bindParam(':weekly_hours', $weekly_hours);
            $stmt_subject->bindParam(':class_hours', $class_hours);
            $stmt_subject->bindParam(':lab_hours', $lab_hours);
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

                // **Nueva inserción en `group_subjects`**
                // Relaciona la materia con los grupos correspondientes
                $stmt_group_subjects = $pdo->prepare('INSERT INTO group_subjects (group_id, subject_id)
                                                      SELECT g.group_id, :subject_id
                                                      FROM `groups` g 
                                                      WHERE g.program_id = :program_id');
                $stmt_group_subjects->execute([
                    ':subject_id' => $subject_id,
                    ':program_id' => $program_id
                ]);

                $mensajes[] = "Relación insertada en group_subjects para Programa ID: $program_id y Materia ID: $subject_id";
            } catch (Exception $e) {
                $errores[] = "Error al insertar materia '$subject_name' o en la relación: " . $e->getMessage();
            }
        }
        fclose($handle);

        session_start();
        $_SESSION['mensajes_debug'] = implode("<br>", $mensajes);
        if (!empty($errores)) {
            $_SESSION['mensaje'] = implode("<br>", $errores);
            $_SESSION['icono'] = "error";
        } else {
            $_SESSION['mensaje'] = "Materias y relaciones registradas con éxito.";
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
?>
