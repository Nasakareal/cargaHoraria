<?php

include('../../../app/config.php');

$teacher_id = $_POST['teacher_id'];
$materia_ids = isset($_POST['materias_asignadas']) ? $_POST['materias_asignadas'] : [];
$grupo_ids = isset($_POST['grupos_asignados']) ? array_filter($_POST['grupos_asignados']) : [];
$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    /* Validar que haya al menos un grupo seleccionado */
    if (empty($grupo_ids)) {
        throw new Exception("Debe seleccionar al menos un grupo para asignar materias.");
    }

    /* Insertar nuevas asignaciones de materias y grupos, evitando duplicados */
    foreach ($grupo_ids as $grupo_id) {
        foreach ($materia_ids as $materia_id) {
            // Verificar si ya existe esta asignación
            $sentencia_verificar = $pdo->prepare("
                SELECT COUNT(*) FROM teacher_subjects 
                WHERE teacher_id = ? AND subject_id = ? AND group_id = ?
            ");
            $sentencia_verificar->execute([$teacher_id, $materia_id, $grupo_id]);
            $existe_asignacion = $sentencia_verificar->fetchColumn();

            // Si no existe, insertar la nueva asignación
            if ($existe_asignacion == 0) {
                $sentencia_insertar = $pdo->prepare("
                    INSERT INTO teacher_subjects (teacher_id, subject_id, group_id, fyh_creacion, fyh_actualizacion) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $sentencia_insertar->execute([$teacher_id, $materia_id, $grupo_id, $fechaHora, $fechaHora]);
            }
        }
    }

    /* Calcular y actualizar las horas totales del profesor */
    $sentencia_horas_totales = $pdo->prepare("
        SELECT SUM(s.weekly_hours) AS total_hours
        FROM teacher_subjects ts
        JOIN subjects s ON ts.subject_id = s.subject_id
        WHERE ts.teacher_id = ?
    ");
    $sentencia_horas_totales->execute([$teacher_id]);
    $total_hours = (int) $sentencia_horas_totales->fetchColumn();

    $sentencia_actualizar_horas = $pdo->prepare("
        UPDATE teachers SET hours = ?, fyh_actualizacion = ? WHERE teacher_id = ?
    ");
    $sentencia_actualizar_horas->execute([$total_hours, $fechaHora, $teacher_id]);

    $pdo->commit();

    session_start();
    $_SESSION['mensaje'] = "Se han añadido las materias con éxito.";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
} catch (Exception $exception) {
    $pdo->rollBack();
    session_start();
    $_SESSION['mensaje'] = $exception->getMessage();
    $_SESSION['icono'] = "error";
    error_log("Error: " . $exception->getMessage());
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
}
