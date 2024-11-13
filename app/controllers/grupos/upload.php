<?php
include('../../../app/config.php');

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        session_start();
        $_SESSION['mensaje'] = "Error al cargar el archivo.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/grupos");
        die();
    }

    $errores = [];

    /* Validación de formato: Verificar si el archivo tiene el número correcto de columnas sin tomar datos */
    if (($handle = fopen($file, 'r')) !== FALSE) {
        /* Leer solo la primera fila para validar el número de columnas */
        $firstRow = fgetcsv($handle, 1000, ',');

        /* Verificamos que tenga exactamente 9 columnas */
        if ($firstRow === false || count($firstRow) !== 9) {
            fclose($handle);
            session_start();
            $_SESSION['mensaje'] = "El archivo no tiene el formato adecuado. Asegúrate de que tenga las columnas correctas.";
            $_SESSION['icono'] = "error";
            header('Location:' . APP_URL . "/admin/grupos");
            die();
        }
        fclose($handle);
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se pudo abrir el archivo.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/grupos");
        die();
    }

    /* Si el archivo pasó la validación, procedemos a procesarlo */
    if (($handle = fopen($file, 'r')) !== FALSE) {
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $row++;
            if ($row <= 3) {
                continue;
            }

            $area = trim($data[1]);
            $abreviatura = trim($data[2]);
            $program_name = trim(mb_strtoupper($data[3]));
            $nivel_educativo = trim($data[4]);
            $term_number = intval(trim($data[5]));
            $group_suffix = trim($data[6]);
            $turn_name = trim($data[7]);
            $volume = trim($data[8]);

            $group_name = mb_strtoupper("{$abreviatura}-{$term_number}{$group_suffix}", 'UTF-8');

            /* Buscar el programa educativo */
            $stmt_program = $pdo->prepare('SELECT program_id, area FROM programs WHERE program_name = :program_name');
            $stmt_program->bindParam(':program_name', $program_name);
            $stmt_program->execute();
            $program = $stmt_program->fetch(PDO::FETCH_ASSOC);

            /* Asignar program_id y área, o NULL si no se encuentra el programa o no coincide el área */
            $program_id = $program ? $program['program_id'] : NULL;
            $area_value = ($program && $area === $program['area']) ? $area : NULL;

            /* Buscar el turno */
            $stmt_turn = $pdo->prepare('SELECT shift_id FROM shifts WHERE shift_name = :turn_name');
            $stmt_turn->bindParam(':turn_name', $turn_name);
            $stmt_turn->execute();
            $turn = $stmt_turn->fetch(PDO::FETCH_ASSOC);
            $turn_id = $turn ? $turn['shift_id'] : NULL;

            /* Insertar grupo */
            $sentencia_grupo = $pdo->prepare('INSERT INTO `groups` 
                (group_name, program_id, area, term_id, volume, turn_id, fyh_creacion, estado) 
                VALUES (:group_name, :program_id, :area, :term_id, :volume, :turn_id, NOW(), "1")');

            $sentencia_grupo->bindParam(':group_name', $group_name);
            $sentencia_grupo->bindParam(':program_id', $program_id);
            $sentencia_grupo->bindParam(':area', $area_value);
            $sentencia_grupo->bindParam(':term_id', $term_number);
            $sentencia_grupo->bindParam(':volume', $volume);
            $sentencia_grupo->bindParam(':turn_id', $turn_id);

            try {
                $sentencia_grupo->execute();
                $group_id = $pdo->lastInsertId();

                /* Insertar el nivel educativo */
                $sentencia_nivel = $pdo->prepare('INSERT INTO `educational_levels` 
                    (level_name, group_id) 
                    VALUES (:nivel_educativo, :group_id)');

                $sentencia_nivel->bindParam(':nivel_educativo', $nivel_educativo);
                $sentencia_nivel->bindParam(':group_id', $group_id);
                $sentencia_nivel->execute();

                /* Insertar materias en group_subjects */
                $stmt_subjects = $pdo->prepare('SELECT subject_id FROM subjects WHERE program_id = :program_id AND term_id = :term_id');
                $stmt_subjects->execute([':program_id' => $program_id, ':term_id' => $term_number]);
                $subjects = $stmt_subjects->fetchAll(PDO::FETCH_ASSOC);

                foreach ($subjects as $subject) {
                    $stmt_group_subject = $pdo->prepare('INSERT INTO group_subjects (group_id, subject_id, fyh_creacion, estado) VALUES (:group_id, :subject_id, NOW(), "1")');
                    $stmt_group_subject->execute([':group_id' => $group_id, ':subject_id' => $subject['subject_id']]);
                }
            } catch (Exception $exception) {
                $errores[] = "Error al registrar el grupo: " . $exception->getMessage();
            }
        }
        fclose($handle);

        session_start();

        if (!empty($errores)) {
            $_SESSION['mensaje'] = implode("<br>", $errores);
            $_SESSION['icono'] = "error";
        } else {
            $_SESSION['mensaje'] = "Grupos registrados con éxito.";
            $_SESSION['icono'] = "success";
        }

        header('Location:' . APP_URL . "/admin/grupos");
        die();
    }
} else {
    session_start();
    $_SESSION['mensaje'] = "No se ha seleccionado ningún archivo.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos");
}
