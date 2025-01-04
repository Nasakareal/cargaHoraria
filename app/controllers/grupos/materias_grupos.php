<?php
// Incluir el archivo de configuración de la base de datos
require_once '../../../app/config.php';

$subjects_by_group = [];

foreach ($groups as $group) {
    $group_id = $group['group_id'];

    // Paso 1: Obtener las asignaciones previas para el grupo actual (Aula y Laboratorio)
    $assignmentsStmt = $pdo->prepare("
        SELECT subject_id, SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)) AS assigned_hours
        FROM manual_schedule_assignments
        WHERE group_id = :group_id 
          AND estado = 'activo'
          AND tipo_espacio IN ('Aula', 'Laboratorio') -- Considerar ambos tipos de espacio
        GROUP BY subject_id
    ");
    $assignmentsStmt->execute([':group_id' => $group_id]);
    $assignments = $assignmentsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Paso 2: Obtener las materias del grupo actual
    $subjectsStmt = $pdo->prepare("
        SELECT s.*, gs.group_id, COALESCE(g.classroom_assigned, 0) AS classroom_assigned
        FROM subjects s 
        JOIN group_subjects gs ON gs.subject_id = s.subject_id 
        JOIN `groups` g ON g.group_id = gs.group_id
        WHERE gs.group_id = :group_id 
          AND s.estado = '1'
    ");
    $subjectsStmt->execute([':group_id' => $group_id]);
    $subjects = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subjects as $subject) {
        $subject_id = $subject['subject_id'];
        $total_hours = (int)$subject['weekly_hours'];

        // Obtener las horas ya asignadas para esta materia
        $assigned_hours = isset($assignments[$subject_id]) ? (int)$assignments[$subject_id] : 0;

        // Calcular horas restantes
        $remaining_hours = $total_hours - $assigned_hours;

        if ($remaining_hours > 0) {
            // Paso 3: Seleccionar el profesor con menos horas asignadas
            $teacherStmt = $pdo->prepare("
                SELECT ts.teacher_id
                FROM teacher_subjects ts
                WHERE ts.subject_id = :subject_id 
                  AND ts.group_id = :group_id 
                ORDER BY (
                    SELECT COUNT(*) 
                    FROM schedule_assignments sa 
                    WHERE sa.teacher_id = ts.teacher_id 
                ) ASC
                LIMIT 1
            ");
            $teacherStmt->execute([':subject_id' => $subject_id, ':group_id' => $group_id]);
            $teacher = $teacherStmt->fetch(PDO::FETCH_ASSOC);

            if ($teacher) {
                $teacher_id = $teacher['teacher_id'];
            } else {
                // Si no hay profesor asignado, continuar sin asignar
                $teacher_id = null;
            }

            $class_subject = [
                'subject_id' => $subject_id,
                'subject_name' => $subject['subject_name'] . " (Aula)",
                'teacher_id' => $teacher_id,
                'remaining_hours' => $remaining_hours,
                'type' => 'Aula',
                'max_consecutive_hours' => isset($subject['max_consecutive_class_hours']) ? (int)$subject['max_consecutive_class_hours'] : 2, // Valor por defecto si no existe
                'min_consecutive_hours' => 1,
            ];
            $subjects_by_group[$group_id][] = $class_subject;
        }
    }
}
?>
