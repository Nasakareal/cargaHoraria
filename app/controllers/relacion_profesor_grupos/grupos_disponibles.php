<?php

include('../../config.php');

/* Iniciar sesión si no está ya iniciada */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/* Obtener datos de la sesión */
$email_sesion = $_SESSION['sesion_email'];
$rol_id = $_SESSION['sesion_rol']; // ID del rol
$teacher_id = filter_input(INPUT_GET, 'teacher_id', FILTER_VALIDATE_INT);

/* Validar el ID del profesor */
if (!$teacher_id) {
    echo "<option value=''>ID de profesor inválido</option>";
    exit;
}

try {
    $sql_area_usuario = "SELECT area FROM usuarios WHERE email = :email AND estado = '1'";
    $stmt_area = $pdo->prepare($sql_area_usuario);
    $stmt_area->bindParam(':email', $email_sesion);
    $stmt_area->execute();
    $usuario = $stmt_area->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "<option value=''>Usuario no encontrado</option>";
        exit;
    }

    $area_usuario = $usuario['area'];
    $areas = array_map('trim', explode(',', $area_usuario)); // Convertir a array y eliminar espacios

    if ($rol_id == 1) {
        $sql_grupos = "
            SELECT DISTINCT g.group_id, g.group_name, s.shift_name
            FROM `groups` g
            INNER JOIN `shifts` s ON g.turn_id = s.shift_id
            WHERE g.estado = '1'
        ";
        $stmt_grupos = $pdo->prepare($sql_grupos);
        $stmt_grupos->execute();
    } else {
        if (empty($areas)) {
            echo "<option value=''>No se han asignado áreas al usuario</option>";
            exit;
        }

        // Crear placeholders dinámicamente
        $placeholders = implode(',', array_fill(0, count($areas), '?'));

        $sql_grupos = "
            SELECT DISTINCT g.group_id, g.group_name, s.shift_name
            FROM `groups` g
            INNER JOIN `shifts` s ON g.turn_id = s.shift_id
            WHERE g.estado = '1'
              AND g.area IN ($placeholders)
        ";
        $stmt_grupos = $pdo->prepare($sql_grupos);
        
        // Ejecutar con el array de áreas
        $stmt_grupos->execute($areas);
    }

    $grupos_disponibles = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($grupos_disponibles)) {
        foreach ($grupos_disponibles as $grupo) {
            echo "<option value='" . htmlspecialchars($grupo['group_id']) . "'>" .
                htmlspecialchars($grupo['group_name']) . " - " .
                htmlspecialchars($grupo['shift_name']) .
                "</option>";
        }
    } else {
        echo "<option value=''>No hay grupos disponibles</option>";
    }
} catch (PDOException $e) {
    echo "<option value=''>Error al cargar grupos</option>";
    error_log("Error en la consulta: " . $e->getMessage());
}
?>
