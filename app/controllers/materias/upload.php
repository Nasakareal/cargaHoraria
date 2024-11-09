<?php
include('../../../app/config.php');

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        session_start();
        $_SESSION['mensaje'] = "Error al cargar el archivo.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/portal/materias");
        die();
    }

    $errores = [];  /* Array para acumular los errores */

    /* Validación de formato: Verificar si el archivo tiene el número correcto de columnas sin tomar datos */
    if (($handle = fopen($file, 'r')) !== FALSE) {
        /* Leer solo la primera fila para validar el número de columnas */
        $firstRow = fgetcsv($handle, 1000, ',');

        /* Verificamos que tenga exactamente 16 columnas */
        if ($firstRow === false || count($firstRow) !== 16) {
            fclose($handle);
            session_start();
            $_SESSION['mensaje'] = "El archivo no tiene el formato adecuado. Asegúrate de que tenga las columnas correctas.";
            $_SESSION['icono'] = "error";
            header('Location:' . APP_URL . "/portal/materias");
            die();
        }
        fclose($handle);
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se pudo abrir el archivo.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/portal/materias");
        die();
    }

    /* Si el archivo pasó la validación, procedemos a procesarlo */
    if (($handle = fopen($file, 'r')) !== FALSE) {
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $row++;

            /* Omitir las primeras 3 filas */
            if ($row <= 3) {
                continue;
            }

            /* Terminar si no hay más datos en la fila */
            if (empty($data[1]) && empty($data[2]) && empty($data[3])) {
                break;
            }

            /* Procesar columnas */
            $program_name = trim(mb_strtoupper($data[1])); /* Columna 2: Programa Educativo */
            $term_number = intval(trim($data[2]));         /* Columna 3: Cuatrimestre */
            $subject_name = trim($data[3]);                /* Columna 4: Asignatura */
            $weekly_hours = intval(trim($data[4]));        /* Columna 5: Horas semanales */
            $space_type = trim($data[5]);                  /* Columna 6: Espacio formativo */
            $class_hours = intval(trim($data[6]));         /* Columna 7: Horas en aula */
            $lab_hours = intval(trim($data[7]));           /* Columna 8: Horas en laboratorio */
            $lab1_name = trim($data[8]);                   /* Columna 9: Laboratorio 1 */
            $lab2_name = trim($data[9]);                   /* Columna 10: Laboratorio 2 */
            $lab3_name = trim($data[10]);                  /* Columna 11: Laboratorio 3 */
            $lab1_hours = intval(trim($data[11]));         /* Columna 12: Horas Laboratorio 1 */
            $lab2_hours = intval(trim($data[12]));         /* Columna 13: Horas Laboratorio 2 */
            $lab3_hours = intval(trim($data[13]));         /* Columna 14: Horas Laboratorio 3 */
            $max_class_block = intval(trim($data[14]));    /* Columna 15: Máx. horas en aula por bloque */
            $max_lab_block = intval(trim($data[15]));      /* Columna 16: Máx. horas en laboratorio por bloque */

            /* Buscar el programa educativo */
            $stmt_program = $pdo->prepare('SELECT program_id FROM programs WHERE program_name = :program_name');
            $stmt_program->bindParam(':program_name', $program_name);
            $stmt_program->execute();
            $program = $stmt_program->fetch(PDO::FETCH_ASSOC);

            if (!$program) {
                /* Si no existe el programa, omitir esta fila y acumular un error */
                $errores[] = "Error: Programa no encontrado: " . $program_name;
                continue;
            }
            $program_id = $program['program_id'];

            /* Insertar la materia en la tabla subjects */
            $sentencia_materia = $pdo->prepare('INSERT INTO `subjects` 
                (subject_name, program_id, term_id, weekly_hours, class_hours, lab_hours, max_consecutive_class_hours, max_consecutive_lab_hours, fyh_creacion, estado) 
                VALUES (:subject_name, :program_id, :term_id, :weekly_hours, :class_hours, :lab_hours, :max_class_block, :max_lab_block, NOW(), "1")');

            /* Vincular los parámetros */
            $sentencia_materia->bindParam(':subject_name', $subject_name);
            $sentencia_materia->bindParam(':program_id', $program_id);
            $sentencia_materia->bindParam(':term_id', $term_number);
            $sentencia_materia->bindParam(':weekly_hours', $weekly_hours);
            $sentencia_materia->bindParam(':class_hours', $class_hours);
            $sentencia_materia->bindParam(':lab_hours', $lab_hours);
            $sentencia_materia->bindParam(':max_class_block', $max_class_block);
            $sentencia_materia->bindParam(':max_lab_block', $max_lab_block);

            try {
                $sentencia_materia->execute();
                $subject_id = $pdo->lastInsertId();

                /* Insertar laboratorios si están definidos */
                if ($lab1_name) {
                    $lab_hours_to_use = $lab1_hours > 0 ? $lab1_hours : $lab_hours;
                    $stmt_lab1 = $pdo->prepare('INSERT INTO subject_labs (subject_id, lab_id, lab_hours) 
                                                SELECT :subject_id, lab_id, :lab_hours 
                                                FROM labs WHERE lab_name = :lab_name');
                    $stmt_lab1->execute([':subject_id' => $subject_id, ':lab_hours' => $lab_hours_to_use, ':lab_name' => $lab1_name]);
                }

                if ($lab2_name) {
                    $lab_hours_to_use = $lab2_hours > 0 ? $lab2_hours : $lab_hours;
                    $stmt_lab2 = $pdo->prepare('INSERT INTO subject_labs (subject_id, lab_id, lab_hours) 
                                                SELECT :subject_id, lab_id, :lab_hours 
                                                FROM labs WHERE lab_name = :lab_name');
                    $stmt_lab2->execute([':subject_id' => $subject_id, ':lab_hours' => $lab_hours_to_use, ':lab_name' => $lab2_name]);
                }

                if ($lab3_name) {
                    $lab_hours_to_use = $lab3_hours > 0 ? $lab3_hours : $lab_hours;
                    $stmt_lab3 = $pdo->prepare('INSERT INTO subject_labs (subject_id, lab_id, lab_hours) 
                                                SELECT :subject_id, lab_id, :lab_hours 
                                                FROM labs WHERE lab_name = :lab_name');
                    $stmt_lab3->execute([':subject_id' => $subject_id, ':lab_hours' => $lab_hours_to_use, ':lab_name' => $lab3_name]);
                }

                /* Insertar la relación en la tabla program_term_subjects */
                $stmt_relation = $pdo->prepare('INSERT INTO program_term_subjects (program_id, term_id, subject_id) VALUES (:program_id, :term_id, :subject_id)');
                $stmt_relation->bindParam(':program_id', $program_id);
                $stmt_relation->bindParam(':term_id', $term_number);
                $stmt_relation->bindParam(':subject_id', $subject_id);
                $stmt_relation->execute();

            } catch (Exception $exception) {
                $errores[] = "Error al registrar la materia o la relación: " . $exception->getMessage();
            }
        }
        fclose($handle);

        session_start();

        if (!empty($errores)) {
            $_SESSION['mensaje'] = implode("<br>", $errores);
            $_SESSION['icono'] = "error";
        } else {
            $_SESSION['mensaje'] = "Materias registradas con éxito.";
            $_SESSION['icono'] = "success";
        }

        header('Location:' . APP_URL . "/portal/materias");
        die();
    }
} else {
    session_start();
    $_SESSION['mensaje'] = "No se ha seleccionado ningún archivo.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/portal/materias");
}
