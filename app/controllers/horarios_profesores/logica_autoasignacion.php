<?php

/* Obtener el ID de asignación de horario y el ID del profesor */
$assignment_id = filter_input(INPUT_GET, 'assignment_id', FILTER_VALIDATE_INT);
$teacher_id = filter_input(INPUT_GET, 'teacher_id', FILTER_VALIDATE_INT);

if (!$assignment_id || !$teacher_id) {
    echo "Datos inválidos.";
    exit;
}

/* Actualizar la asignación de horario con el ID del profesor */
$sql = "UPDATE schedule_assignments SET teacher_id = :teacher_id WHERE assignment_id = :assignment_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':teacher_id' => $teacher_id, ':assignment_id' => $assignment_id]);

echo "Horario asignado exitosamente.";
header("Location: asignar_horario_profesor.php?teacher_id=$teacher_id");
exit;