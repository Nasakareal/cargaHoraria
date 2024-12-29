<?php
include('../../../app/config.php');

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Obtener y validar los datos del formulario
$subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT);
$subject_name = filter_input(INPUT_POST, 'subject_name', FILTER_SANITIZE_STRING);
$weekly_hours = filter_input(INPUT_POST, 'weekly_hours', FILTER_VALIDATE_INT);
$class_hours = filter_input(INPUT_POST, 'class_hours', FILTER_VALIDATE_INT);
$lab_hours = filter_input(INPUT_POST, 'lab_hours', FILTER_VALIDATE_INT);
$lab1_hours = filter_input(INPUT_POST, 'lab1_hours', FILTER_VALIDATE_INT);
$lab2_hours = filter_input(INPUT_POST, 'lab2_hours', FILTER_VALIDATE_INT);
$hours_consecutive = filter_input(INPUT_POST, 'hours_consecutive', FILTER_VALIDATE_INT);
$max_consecutive_lab_hours = filter_input(INPUT_POST, 'max_consecutive_lab_hours', FILTER_VALIDATE_INT);
$program_id = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
$term_id = filter_input(INPUT_POST, 'term_id', FILTER_VALIDATE_INT);

// Verificar que todos los campos requeridos estén presentes
if (
    !$subject_id || 
    !$subject_name || 
    $weekly_hours === false || 
    $class_hours === false || 
    $lab_hours === false || 
    $lab1_hours === false || 
    $lab2_hours === false || 
    $hours_consecutive === false || 
    $max_consecutive_lab_hours === false || 
    !$program_id || 
    !$term_id
) {
    $_SESSION['mensaje'] = "Error: Datos inválidos.";
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/materias");
    exit;
}

$fechaHora = date('Y-m-d H:i:s');

try {
    // Iniciar transacción
    $pdo->beginTransaction();

    // 1. Obtener el valor actual de weekly_hours antes de actualizar
    $query_old_hours = $pdo->prepare("SELECT weekly_hours FROM subjects WHERE subject_id = :subject_id");
    $query_old_hours->execute([':subject_id' => $subject_id]);
    $old_subject = $query_old_hours->fetch(PDO::FETCH_ASSOC);

    if (!$old_subject) {
        throw new Exception("Materia no encontrada.");
    }

    $old_weekly_hours = $old_subject['weekly_hours'];
    $difference = $weekly_hours - $old_weekly_hours;

    // 2. Actualizar materia
    $sentencia_actualizar = $pdo->prepare("UPDATE subjects
        SET subject_name = :subject_name,
            weekly_hours = :weekly_hours,
            class_hours = :class_hours,
            lab_hours = :lab_hours,
            lab1_hours = :lab1_hours,
            lab2_hours = :lab2_hours,
            max_consecutive_class_hours = :max_consecutive_class_hours,
            max_consecutive_lab_hours = :max_consecutive_lab_hours,
            fyh_actualizacion = :fyh_actualizacion,
            program_id = :program_id,
            term_id = :term_id
        WHERE subject_id = :subject_id");

    $sentencia_actualizar->bindParam(':subject_name', $subject_name);
    $sentencia_actualizar->bindParam(':weekly_hours', $weekly_hours, PDO::PARAM_INT);
    $sentencia_actualizar->bindParam(':class_hours', $class_hours, PDO::PARAM_INT);
    $sentencia_actualizar->bindParam(':lab_hours', $lab_hours, PDO::PARAM_INT);
    $sentencia_actualizar->bindParam(':lab1_hours', $lab1_hours, PDO::PARAM_INT);
    $sentencia_actualizar->bindParam(':lab2_hours', $lab2_hours, PDO::PARAM_INT);
    $sentencia_actualizar->bindParam(':max_consecutive_class_hours', $hours_consecutive, PDO::PARAM_INT);
    $sentencia_actualizar->bindParam(':max_consecutive_lab_hours', $max_consecutive_lab_hours, PDO::PARAM_INT);
    $sentencia_actualizar->bindParam(':fyh_actualizacion', $fechaHora);
    $sentencia_actualizar->bindParam(':program_id', $program_id, PDO::PARAM_INT);
    $sentencia_actualizar->bindParam(':term_id', $term_id, PDO::PARAM_INT);
    $sentencia_actualizar->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);

    $sentencia_actualizar->execute();

    // 3. Actualizar o insertar la relación en program_term_subjects
    $sentencia_relacion = $pdo->prepare("REPLACE INTO program_term_subjects (program_id, term_id, subject_id)
        VALUES (:program_id, :term_id, :subject_id)");

    $sentencia_relacion->bindParam(':program_id', $program_id, PDO::PARAM_INT);
    $sentencia_relacion->bindParam(':term_id', $term_id, PDO::PARAM_INT);
    $sentencia_relacion->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);

    $sentencia_relacion->execute();

    // 4. Actualizar las horas de los profesores si hay un cambio en weekly_hours
    if ($difference != 0) {
        // Obtener todos los profesores asignados a esta materia
        $query_teachers = $pdo->prepare("SELECT teacher_id FROM teacher_subjects WHERE subject_id = :subject_id");
        $query_teachers->execute([':subject_id' => $subject_id]);
        $teachers = $query_teachers->fetchAll(PDO::FETCH_ASSOC);

        foreach ($teachers as $teacher) {
            $teacher_id = $teacher['teacher_id'];

            // Actualizar las horas del profesor
            $update_teacher_hours = $pdo->prepare("UPDATE teachers
                SET hours = hours + :difference
                WHERE teacher_id = :teacher_id");

            $update_teacher_hours->bindParam(':difference', $difference, PDO::PARAM_INT);
            $update_teacher_hours->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);

            $update_teacher_hours->execute();
        }
    }

    // Confirmar transacción
    $pdo->commit();

    $_SESSION['mensaje'] = "Materia actualizada correctamente.";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/materias");
    exit;
} catch (Exception $exception) {
    // Revertir transacción en caso de error
    $pdo->rollBack();
    $_SESSION['mensaje'] = "Ocurrió un error: " . $exception->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/materias");
    exit;
}
?>
