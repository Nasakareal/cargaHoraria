<?php
include('../../../app/config.php');

set_time_limit(600);

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        session_start();
        $_SESSION['mensaje'] = "Error al cargar el archivo.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/materias");
        die();
    }

    $errores = [];  /* Array para acumular los errores */

    /* Validación de formato: Verificar si el archivo tiene al menos las columnas necesarias */
    if (($handle = fopen($file, 'r')) !== FALSE) {
        /* Omitir las primeras 3 filas y verificar la cantidad de columnas */
        for ($i = 0; $i < 3; $i++) {
            fgetcsv($handle, 1000, ',');
        }

        /* Leer una fila para validar la cantidad de columnas */
        $firstRow = fgetcsv($handle, 1000, ',');
        if ($firstRow === false || count($firstRow) < 13) {
            fclose($handle);
            session_start();
            $_SESSION['mensaje'] = "El archivo no tiene el formato adecuado. Asegúrate de que tenga al menos 13 columnas válidas.";
            $_SESSION['icono'] = "error";
            header('Location:' . APP_URL . "/admin/materias");
            die();
        }
        fclose($handle);
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se pudo abrir el archivo.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/materias");
        die();
    }

    /* Procesar el archivo */
    if (($handle = fopen($file, 'r')) !== FALSE) {
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $row++;

            /* Omitir las primeras 3 filas */
            if ($row <= 3) {
                continue;
            }

            /* Ignorar filas vacías */
            if (empty($data[1]) && empty($data[2]) && empty($data[3])) {
                break;
            }

            /* Extraer y procesar los datos relevantes, ignorando la primera columna */
            $program_name = trim(mb_strtoupper($data[1])); /* Columna 2: Programa Educativo */
            $term_number = intval(trim($data[2]));         /* Columna 3: Cuatrimestre */
            $subject_name = trim(mb_strtoupper($data[3])); /* Columna 4: Asignatura */
            $weekly_hours = intval(trim($data[5]));        /* Columna 6: Horas/Semana */
            $space_type = trim(mb_strtoupper($data[6]));   /* Columna 7: Espacio Formativo */
            $class_hours = intval(trim($data[7]));         /* Columna 8: Horas Aula */
            $lab1_name = trim(mb_strtoupper($data[8]));    /* Columna 9: Laboratorio 1 */
            $lab2_name = trim(mb_strtoupper($data[9]));    /* Columna 10: Laboratorio 2 */
            $lab1_hours = intval(trim($data[10]));         /* Columna 11: Horas Laboratorio 1 */
            $lab2_hours = intval(trim($data[11]));         /* Columna 12: Horas Laboratorio 2 */
            $max_class_block = intval(trim($data[12]));    /* Columna 13: Máx. Horas Bloque Aula */
            $max_lab_block = intval(trim($data[13]));      /* Columna 14: Máx. Horas Bloque Lab */

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
                (subject_name, program_id, term_id, weekly_hours, class_hours, lab_hours, lab1_hours, lab2_hours, max_consecutive_class_hours, max_consecutive_lab_hours, fyh_creacion, estado) 
                VALUES (:subject_name, :program_id, :term_id, :weekly_hours, :class_hours, :total_lab_hours, :lab1_hours, :lab2_hours, :max_class_block, :max_lab_block, NOW(), "1")');

            /* Calcular las horas totales de laboratorio */
            $total_lab_hours = $lab1_hours + $lab2_hours;

            /* Vincular los parámetros */
            $sentencia_materia->bindParam(':subject_name', $subject_name);
            $sentencia_materia->bindParam(':program_id', $program_id);
            $sentencia_materia->bindParam(':term_id', $term_number);
            $sentencia_materia->bindParam(':weekly_hours', $weekly_hours);
            $sentencia_materia->bindParam(':class_hours', $class_hours);
            $sentencia_materia->bindParam(':total_lab_hours', $total_lab_hours);
            $sentencia_materia->bindParam(':lab1_hours', $lab1_hours);
            $sentencia_materia->bindParam(':lab2_hours', $lab2_hours);
            $sentencia_materia->bindParam(':max_class_block', $max_class_block);
            $sentencia_materia->bindParam(':max_lab_block', $max_lab_block);

            try {
                $sentencia_materia->execute();
                $subject_id = $pdo->lastInsertId();

                /* Insertar laboratorios si están definidos */
                if ($lab1_name) {
                    $stmt_lab1 = $pdo->prepare('INSERT INTO subject_labs (subject_id, lab_id, lab_hours) 
                                                SELECT :subject_id, lab_id, :lab_hours 
                                                FROM labs WHERE lab_name = :lab_name');
                    $stmt_lab1->execute([':subject_id' => $subject_id, ':lab_hours' => $lab1_hours, ':lab_name' => $lab1_name]);
                }

                if ($lab2_name) {
                    $stmt_lab2 = $pdo->prepare('INSERT INTO subject_labs (subject_id, lab_id, lab_hours) 
                                                SELECT :subject_id, lab_id, :lab_hours 
                                                FROM labs WHERE lab_name = :lab_name');
                    $stmt_lab2->execute([':subject_id' => $subject_id, ':lab_hours' => $lab2_hours, ':lab_name' => $lab2_name]);
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

        header('Location:' . APP_URL . "/admin/materias");
        die();
    }
}
