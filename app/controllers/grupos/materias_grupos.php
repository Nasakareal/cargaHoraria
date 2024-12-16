<?php

$subjects_by_group = [];
foreach ($groups as $group) {
    $stmt = $pdo->prepare("SELECT s.*, gs.group_id, COALESCE(g.classroom_assigned, 0) AS classroom_assigned, ts.teacher_id
        FROM subjects s 
        JOIN group_subjects gs ON gs.subject_id = s.subject_id 
        JOIN `groups` g ON g.group_id = gs.group_id
        LEFT JOIN teacher_subjects ts ON ts.subject_id = s.subject_id AND ts.group_id = gs.group_id
        WHERE gs.group_id = :group_id AND s.estado = '1'");
    $stmt->execute([':group_id' => $group['group_id']]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subjects as $subject) {
        $total_hours = $subject['weekly_hours'];
        $lab_hours = $subject['lab_hours'];
        
        // Calcular horas restantes como aula
        $remaining_hours = $total_hours - $lab_hours;
        
        if ($remaining_hours > 0) {
            $class_subject = [
                'subject_id' => $subject['subject_id'],
                'subject_name' => $subject['subject_name'] . " (Aula)",
                'teacher_id' => $subject['teacher_id'],
                'remaining_hours' => $remaining_hours,
                'type' => 'Aula',
                'max_consecutive_hours' => (int) $subject['max_consecutive_class_hours'],
                'min_consecutive_hours' => 1,
            ];
            $subjects_by_group[$group['group_id']][] = $class_subject;
        }
    }
}
?>
