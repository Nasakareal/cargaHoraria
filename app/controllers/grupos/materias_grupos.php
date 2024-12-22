<?php

$subjects_by_group = [];
foreach ($groups as $group) {
    $stmt = $pdo->prepare("
        SELECT 
            s.*, 
            gs.group_id, 
            COALESCE(g.classroom_assigned, 0) AS classroom_assigned, 
            ts.teacher_id
        FROM subjects s 
        JOIN group_subjects gs ON gs.subject_id = s.subject_id 
        JOIN `groups` g ON g.group_id = gs.group_id
        LEFT JOIN teacher_subjects ts ON ts.subject_id = s.subject_id AND ts.group_id = gs.group_id
        WHERE gs.group_id = :group_id AND s.estado = '1'
    ");
    
    $stmt->execute([':group_id' => $group['group_id']]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subjects as $subject) {
        $total_hours = is_numeric($subject['weekly_hours']) ? (int)$subject['weekly_hours'] : 0;
        $lab_hours = is_numeric($subject['lab_hours']) ? (int)$subject['lab_hours'] : 0;

        $remaining_hours = $total_hours - $lab_hours;

        if ($remaining_hours <= 0) {
            error_log("Materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) para el grupo ID: {$group['group_id']} tiene horas restantes no válidas: $remaining_hours.");
            continue;
        }

        $max_consecutive_hours = isset($subject['max_consecutive_class_hours']) && is_numeric($subject['max_consecutive_class_hours']) 
            ? (int)$subject['max_consecutive_class_hours'] 
            : 0;

        if ($max_consecutive_hours < 1) {
            $max_consecutive_hours = 2;
            error_log("Materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) para el grupo ID: {$group['group_id']} tiene 'max_consecutive_class_hours' inválido: {$subject['max_consecutive_class_hours']}. Se establece a $max_consecutive_hours.");
        }

        $min_consecutive_hours = isset($subject['min_consecutive_hours']) && is_numeric($subject['min_consecutive_hours']) 
            ? (int)$subject['min_consecutive_hours'] 
            : 1;

        if ($min_consecutive_hours < 1) {
            $min_consecutive_hours = 1;
            error_log("Materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) para el grupo ID: {$group['group_id']} tiene 'min_consecutive_hours' inválido: {$subject['min_consecutive_hours']}. Se establece a 1.");
        }

        $teacher_id = isset($subject['teacher_id']) && !empty($subject['teacher_id']) ? $subject['teacher_id'] : null;
        if (!$teacher_id) {
            error_log("Materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) para el grupo ID: {$group['group_id']} no tiene un 'teacher_id' asignado.");

        }

        $class_subject = [
            'subject_id' => $subject['subject_id'],
            'subject_name' => $subject['subject_name'] . " (Aula)",
            'teacher_id' => $teacher_id,
            'remaining_hours' => $remaining_hours,
            'type' => 'Aula',
            'max_consecutive_hours' => $max_consecutive_hours,
            'min_consecutive_hours' => $min_consecutive_hours,
        ];

        if (!isset($subjects_by_group[$group['group_id']])) {
            $subjects_by_group[$group['group_id']] = [];
        }

        $subjects_by_group[$group['group_id']][] = $class_subject;
    }
}
?>
