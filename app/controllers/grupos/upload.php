<?php
include('../../../app/config.php');

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo "Error al cargar el archivo.";
        die();
    }

    $errores = [];  /* Array para acumular los errores */

    if (($handle = fopen($file, 'r')) !== FALSE) {
        $row = 0;  /* Contador para las filas */
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $row++;

            /* Omitir las primeras 3 filas */
            if ($row <= 3) {
                continue;
            }

            /* Ignorar las primeras 2 columnas, procesar desde la tercera */
            $abreviatura = trim($data[2]);    /* Columna 3: abreviatura */
            $program_name = trim(mb_strtoupper($data[3]));   /* Columna 4: carrera (programa educativo) en mayúsculas */
            $nivel_educativo = trim($data[4]);  /* Columna 5: nivel educativo */
            $term_number = intval(trim($data[5]));  /* Columna 6: cuatrimestre (número) */
            $group_suffix = trim($data[6]);   /* Columna 7: nombre del grupo (sufijo) */
            $turn_name = trim($data[7]);      /* Columna 8: turno */
            $volume = trim($data[8]);         /* Columna 9: volumen de alumnos */

            /* Concatenar la abreviatura con el nombre del grupo */
            $group_name = mb_strtoupper($abreviatura . '-' . $group_suffix, 'UTF-8');

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

            /* Normalizar cuatrimestre (term_number a 'Primero', 'Segundo', etc.) */
            $term_names = ['Primero', 'Segundo', 'Tercero', 'Cuarto', 'Quinto', 'Sexto', 'Séptimo', 'Octavo', 'Noveno', 'Decimo', 'Undécimo', 'Duodécimo', 'Decimotercero', 'Decimocuarto', 'Decimoquinto', 'Decimosexto', 'Decimoséptimo', 'Decimoctavo', 'Decimonoveno', 'Vigésimo'];
            $term_name = isset($term_names[$term_number - 1]) ? $term_names[$term_number - 1] : null;

            if (!$term_name) {
                $errores[] = "Error: Cuatrimestre inválido: " . $term_number;
                continue;
            }

            /* Buscar o insertar el cuatrimestre */
            $stmt_term = $pdo->prepare('SELECT term_id FROM terms WHERE term_name = :term_name');
            $stmt_term->bindParam(':term_name', $term_name);
            $stmt_term->execute();
            $term = $stmt_term->fetch(PDO::FETCH_ASSOC);

            if (!$term) {
                /* Si no existe el cuatrimestre, insertarlo */
                $insert_term = $pdo->prepare('INSERT INTO terms (term_name) VALUES (:term_name)');
                $insert_term->bindParam(':term_name', $term_name);
                if ($insert_term->execute()) {
                    $term_id = $pdo->lastInsertId();  /* Obtener el ID del nuevo cuatrimestre */
                } else {
                    $errores[] = "Error: No se pudo insertar el cuatrimestre: " . $term_name;
                    continue;
                }
            } else {
                $term_id = $term['term_id'];
            }

            /* Buscar el ID del turno */
            $stmt_turn = $pdo->prepare('SELECT shift_id FROM shifts WHERE shift_name = :turn_name');
            $stmt_turn->bindParam(':turn_name', $turn_name);
            $stmt_turn->execute();
            $turn = $stmt_turn->fetch(PDO::FETCH_ASSOC);
            if (!$turn) {
                $errores[] = "Error: Turno no encontrado para el nombre: " . $turn_name;
                continue;
            }
            $turn_id = $turn['shift_id'];

            /* Insertar el grupo en la tabla groups */
            $sentencia_grupo = $pdo->prepare('INSERT INTO `groups` 
                (group_name, program_id, term_id, volume, turn_id, fyh_creacion, estado) 
                VALUES (:group_name, :program_id, :term_id, :volume, :turn_id, NOW(), "1")');

            /* Vincular los parámetros */
            $sentencia_grupo->bindParam(':group_name', $group_name);
            $sentencia_grupo->bindParam(':program_id', $program_id);
            $sentencia_grupo->bindParam(':term_id', $term_id);
            $sentencia_grupo->bindParam(':volume', $volume);
            $sentencia_grupo->bindParam(':turn_id', $turn_id);

            try {
                $sentencia_grupo->execute();

                /* Obtener el ID del grupo insertado */
                $group_id = $pdo->lastInsertId();

                /* Insertar el nivel educativo en la tabla educational_levels */
                $sentencia_nivel = $pdo->prepare('INSERT INTO `educational_levels` 
                    (level_name, group_id) 
                    VALUES (:nivel_educativo, :group_id)');

                $sentencia_nivel->bindParam(':nivel_educativo', $nivel_educativo);
                $sentencia_nivel->bindParam(':group_id', $group_id);
                $sentencia_nivel->execute();

            } catch (Exception $exception) {
                $errores[] = "Error al registrar el grupo: " . $exception->getMessage();
            }
        }
        fclose($handle);

        session_start();

        if (!empty($errores)) {
            $_SESSION['mensaje'] = implode("<br>", $errores);  /* Mostrar todos los errores acumulados */
            $_SESSION['icono'] = "error";
        } else {
            $_SESSION['mensaje'] = "Grupos registrados con éxito.";
            $_SESSION['icono'] = "success";
        }

        header('Location:' . APP_URL . "/admin/grupos");
        die();
    } else {
        echo "No se pudo abrir el archivo.";
    }
} else {
    echo "No se ha seleccionado ningún archivo.";
}
