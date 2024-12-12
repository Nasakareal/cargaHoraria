<?php

/* Obtener materias específicas de cada grupo desde `group_subjects` y sus laboratorios desde `subject_labs` */
$subjects_by_group = [];
foreach ($groups as $group) {
    $stmt = $pdo->prepare("
        SELECT s.*, gs.group_id, g.classroom_assigned, g.lab_assigned, ts.teacher_id, sl.lab_id, sl.lab_hours
        FROM subjects s 
        JOIN group_subjects gs ON gs.subject_id = s.subject_id 
        JOIN `groups` g ON g.group_id = gs.group_id
        LEFT JOIN teacher_subjects ts ON ts.subject_id = s.subject_id AND ts.group_id = gs.group_id
        LEFT JOIN subject_labs sl ON sl.subject_id = s.subject_id
        WHERE gs.group_id = :group_id AND s.estado = '1'
    ");
    $stmt->execute([':group_id' => $group['group_id']]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* Inicializar horas restantes por materia */
    foreach ($subjects as $subject) {
        // Crear una entrada para las horas de clase
        if ($subject['class_hours'] > 0) {
            $class_subject = [
                'subject_id' => $subject['subject_id'],
                'subject_name' => $subject['subject_name'] . " (Clase)",
                'teacher_id' => $subject['teacher_id'],
                'remaining_hours' => $subject['class_hours'],
                'type' => 'Aula',
                'max_consecutive_hours' => (int) $subject['max_consecutive_class_hours'],
                'min_consecutive_hours' => 1, // Establecer mínimo a 1 para Aula
            ];
            $subjects_by_group[$group['group_id']][] = $class_subject;
        }

        // Crear una entrada para las horas de laboratorio
        if (isset($subject['lab_hours']) && $subject['lab_hours'] > 0) {
            $lab_subject = [
                'subject_id' => $subject['subject_id'],
                'subject_name' => $subject['subject_name'] . " (Laboratorio)",
                'teacher_id' => $subject['teacher_id'],
                'remaining_hours' => $subject['lab_hours'],
                'type' => 'Laboratorio',
                'max_consecutive_hours' => (int) $subject['max_consecutive_lab_hours'],
                'min_consecutive_hours' => isset($subject['min_consecutive_lab_hours']) ? (int) $subject['min_consecutive_lab_hours'] : (int) $subject['max_consecutive_lab_hours'],
            ];
            $subjects_by_group[$group['group_id']][] = $lab_subject;
        }
    }
}
?>
