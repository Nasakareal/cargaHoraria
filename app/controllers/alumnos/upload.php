<?php
session_start();
include('../../../app/config.php');

if (isset($_FILES['file'])) {
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file']['tmp_name'];
        $mensajes = [];

        if (($handle = fopen($file, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $data = array_map('trim', $data);

                if (count($data) < 4) {
                    $mensajes[] = "Error: Línea con datos incompletos.";
                    continue;
                }

                $student_name = $data[0];
                $group_name = $data[1];
                $term_name = $data[2];
                $program_name = $data[3];

                /* Verificar si el grupo existe */
                $group_id_query = $pdo->prepare("SELECT group_id FROM `groups` WHERE group_name = :group_name");
                $group_id_query->execute(['group_name' => $group_name]);
                $group_id = $group_id_query->fetchColumn();

                /* Verificar si el cuatrimestre existe */
                $term_id_query = $pdo->prepare("SELECT term_id FROM terms WHERE term_name = :term_name");
                $term_id_query->execute(['term_name' => $term_name]);
                $term_id = $term_id_query->fetchColumn();

                /* Verificar si el programa existe */
                $program_id_query = $pdo->prepare("SELECT program_id FROM programs WHERE program_name = :program_name");
                $program_id_query->execute(['program_name' => $program_name]);
                $program_id = $program_id_query->fetchColumn();

                /* Validar existencia de grupo, cuatrimestre y programa */
                if (!$group_id || !$term_id || !$program_id) {
                    $mensajes[] = "Error: El grupo '$group_name', el cuatrimestre '$term_name' o el programa '$program_name' no existen.";
                    continue;
                }

                /* Inserción del estudiante */
                $insert_query = $pdo->prepare("INSERT INTO students (student_name, group_id, term_id, program_id, fyh_creacion, estado) VALUES (:student_name, :group_id, :term_id, :program_id, NOW(), '1')");
                if (
                    $insert_query->execute([
                        'student_name' => $student_name,
                        'group_id' => $group_id,
                        'term_id' => $term_id,
                        'program_id' => $program_id
                    ])
                ) {
                    $mensajes[] = "Estudiante '$student_name' agregado correctamente.";
                } else {
                    $mensajes[] = "Error al agregar el estudiante '$student_name'.";
                }
            }
            fclose($handle);
        } else {
            $mensajes[] = "Error: no se pudo abrir el archivo.";
        }

        $_SESSION['mensaje'] = implode("<br>", $mensajes);
        $_SESSION['icono'] = "success";
        header('Location:' . APP_URL . "/admin/alumnos");
        exit;
    } else {
        $_SESSION['mensaje'] = "Error en la carga: " . $_FILES['file']['error'];
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/alumnos");
        exit;
    }
} else {
    $_SESSION['mensaje'] = "No se ha seleccionado ningún archivo o hubo un error en la carga.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/alumnos");
    exit;
}
