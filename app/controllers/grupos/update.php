<?php
include('../../../app/config.php');
require_once('../../../app/registro_eventos.php');

$group_id = $_POST['group_id'];
$group_name = $_POST['group_name'];
$program_id = $_POST['program_id'];
$term_id = $_POST['term_id'];
$volume = $_POST['volume'];
$turn_id = $_POST['turn_id'];
$nivel_id = $_POST['nivel_id'];
$classroom_assigned = $_POST['classroom_id'];

$group_name = mb_strtoupper($group_name, 'UTF-8');

// Validación básica
if (empty($group_name) || empty($program_id) || empty($term_id) || empty($turn_id) || empty($nivel_id)) {
    session_start();
    $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
    exit();
}

// Obtener el área asociada al programa
$consultaArea = $pdo->prepare("SELECT area FROM programs WHERE program_id = :program_id");
$consultaArea->bindParam(':program_id', $program_id);
$consultaArea->execute();
$area = $consultaArea->fetchColumn();

if (!$area) {
    session_start();
    $_SESSION['mensaje'] = "No se pudo obtener el área correspondiente al programa seleccionado.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
    exit();
}

$sentencia = $pdo->prepare("UPDATE `groups` 
                            SET group_name = :group_name, 
                                area = :area,
                                program_id = :program_id, 
                                term_id = :term_id, 
                                volume = :volume, 
                                turn_id = :turn_id, 
                                classroom_assigned = :classroom_assigned, 
                                fyh_actualizacion = NOW() 
                            WHERE group_id = :group_id");

$sentencia->bindParam(':group_name', $group_name);
$sentencia->bindParam(':area', $area);
$sentencia->bindParam(':program_id', $program_id);
$sentencia->bindParam(':term_id', $term_id);
$sentencia->bindParam(':volume', $volume);
$sentencia->bindParam(':turn_id', $turn_id);
$sentencia->bindParam(':classroom_assigned', $classroom_assigned);
$sentencia->bindParam(':group_id', $group_id);

try {
    $pdo->beginTransaction();

    if (!$sentencia->execute()) {
        throw new Exception("No se pudo actualizar la tabla `groups`.");
    }

    // Manejo de nivel educativo
    $sentencia_verificar = $pdo->prepare("SELECT * FROM `educational_levels` WHERE group_id = :group_id");
    $sentencia_verificar->bindParam(':group_id', $group_id);
    $sentencia_verificar->execute();
    $nivel_existente = $sentencia_verificar->fetch(PDO::FETCH_ASSOC);

    if ($nivel_existente) {
        $stmt_level_name = $pdo->prepare("SELECT level_name FROM `educational_levels` WHERE level_id = :nivel_id");
        $stmt_level_name->bindParam(':nivel_id', $nivel_id);
        $stmt_level_name->execute();
        $level_name = $stmt_level_name->fetchColumn();

        $sentencia_nivel = $pdo->prepare("UPDATE `educational_levels`
                                          SET level_name = :level_name
                                          WHERE group_id = :group_id");

        $sentencia_nivel->bindParam(':level_name', $level_name);
        $sentencia_nivel->bindParam(':group_id', $group_id);
    } else {
        $sentencia_nivel = $pdo->prepare("INSERT INTO `educational_levels` (level_name, group_id)
                                          SELECT level_name, :group_id 
                                          FROM `educational_levels` 
                                          WHERE level_id = :nivel_id");

        $sentencia_nivel->bindParam(':nivel_id', $nivel_id);
        $sentencia_nivel->bindParam(':group_id', $group_id);
    }

    if (!$sentencia_nivel->execute()) {
        throw new Exception("No se pudo actualizar o insertar el nivel educativo.");
    }

    // Eliminar materias anteriores y registrar nuevas
    $stmt_delete_subjects = $pdo->prepare("DELETE FROM group_subjects WHERE group_id = :group_id");
    $stmt_delete_subjects->bindParam(':group_id', $group_id);
    $stmt_delete_subjects->execute();

    $stmt_subjects = $pdo->prepare("SELECT subject_id FROM subjects WHERE program_id = :program_id AND term_id = :term_id");
    $stmt_subjects->execute([':program_id' => $program_id, ':term_id' => $term_id]);
    $subjects = $stmt_subjects->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subjects as $subject) {
        $stmt_group_subject = $pdo->prepare("INSERT INTO group_subjects (group_id, subject_id, fyh_creacion, estado) 
                                             VALUES (:group_id, :subject_id, NOW(), '1')");
        $stmt_group_subject->execute([':group_id' => $group_id, ':subject_id' => $subject['subject_id']]);
    }

    // Actualizar el aula en horarios
    $stmt_update_all_classrooms = $pdo->prepare("UPDATE schedule_assignments 
                                                 SET classroom_id = :classroom_id, fyh_actualizacion = NOW() 
                                                 WHERE group_id = :group_id AND tipo_espacio = 'Aula'");
    $stmt_update_all_classrooms->bindParam(':classroom_id', $classroom_assigned);
    $stmt_update_all_classrooms->bindParam(':group_id', $group_id);

    if (!$stmt_update_all_classrooms->execute()) {
        throw new Exception("No se pudo actualizar el salón en los horarios.");
    }

    // Registrar evento
    $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido@dominio.com';
    $accion = 'Actualización de grupo';
    $descripcion = "Se actualizó el grupo '$group_name' con ID $group_id. Área '$area', Programa ID $program_id, Cuatrimestre ID $term_id, Turno ID $turn_id, Salón asignado: $classroom_assigned.";

    registrarEvento($pdo, $usuario_email, $accion, $descripcion);

    $pdo->commit();

    $_SESSION['mensaje'] = "El grupo ha sido actualizado correctamente.";
    $_SESSION['icono'] = "success";
    header('Location:' . APP_URL . "/admin/grupos");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = "Error al actualizar el grupo: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/grupos/edit.php?id=" . $group_id);
    exit();
}
