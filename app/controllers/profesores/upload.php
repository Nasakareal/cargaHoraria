<?php
include('../../../app/config.php');

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        session_start();
        $_SESSION['mensaje'] = "Error al cargar el archivo.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/profesores");
        die();
    }

    $errores = [];  /* Array para acumular errores */

    /* Validación de formato: Asegurarse de que el archivo tenga el número correcto de columnas */
    if (($handle = fopen($file, 'r')) !== FALSE) {
        /* Leer la primera fila (encabezados) y luego validar la estructura */
        $firstRow = fgetcsv($handle, 1000, ',');

        /* Verificamos que tenga exactamente 8 columnas (ajusta según el número exacto que necesites) */
        if ($firstRow === false || count($firstRow) !== 8) {
            fclose($handle);
            session_start();
            $_SESSION['mensaje'] = "El archivo no tiene el formato adecuado. Asegúrate de que tenga las columnas correctas.";
            $_SESSION['icono'] = "error";
            header('Location:' . APP_URL . "/admin/profesores");
            die();
        }

        /* Procesamos las filas restantes */
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $row++;

            /* Ignorar la primera columna y obtener los datos necesarios */
            $nombre = mb_strtoupper(trim($data[1])) . ' ' . mb_strtoupper(trim($data[2])) . ' ' . mb_strtoupper(trim($data[3]));
            $puesto = mb_strtoupper(trim($data[4]));
            $area = mb_strtoupper(trim($data[5]));
            $clasificacion = mb_strtoupper(trim($data[7]));

            /* Verificar si el área existe en la tabla `programs` */
            $stmt_area = $pdo->prepare('SELECT area FROM programs WHERE area = :area');
            $stmt_area->bindParam(':area', $area);
            $stmt_area->execute();
            $area_exists = $stmt_area->fetch(PDO::FETCH_ASSOC);

            /* Si el área no existe, establecemos el valor como NULL */
            $area_value = $area_exists ? $area : NULL;

            /* Insertar datos en la tabla de profesores */
            $stmt_profesor = $pdo->prepare('INSERT INTO teachers 
                (teacher_name, puesto, area, clasificacion, fyh_creacion, estado) 
                VALUES (:nombre, :puesto, :area, :clasificacion, NOW(), "1")');

            $stmt_profesor->bindParam(':nombre', $nombre);
            $stmt_profesor->bindParam(':puesto', $puesto);
            $stmt_profesor->bindParam(':area', $area_value);
            $stmt_profesor->bindParam(':clasificacion', $clasificacion);

            try {
                $stmt_profesor->execute();
            } catch (Exception $exception) {
                $errores[] = "Error al registrar el profesor en la fila $row: " . $exception->getMessage();
            }
        }
        fclose($handle);

        session_start();

        if (!empty($errores)) {
            $_SESSION['mensaje'] = implode("<br>", $errores);
            $_SESSION['icono'] = "error";
        } else {
            $_SESSION['mensaje'] = "Profesores registrados con éxito.";
            $_SESSION['icono'] = "success";
        }

        header('Location:' . APP_URL . "/admin/profesores");
        die();
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se pudo abrir el archivo.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/profesores");
        die();
    }
} else {
    session_start();
    $_SESSION['mensaje'] = "No se ha seleccionado ningún archivo.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/profesores");
}
