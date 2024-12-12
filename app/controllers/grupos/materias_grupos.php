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
    foreach ($subjects as &$subject) {
        $subject['remaining_class_hours'] = $subject['class_hours'];
        
        if (isset($subject['lab_hours']) && !is_null($subject['lab_hours'])) {
            $subject['remaining_lab_hours'] = $subject['lab_hours'];
        } else {
            $subject['remaining_lab_hours'] = 0;
        }
        $subject['total_remaining_hours'] = $subject['class_hours'] + $subject['remaining_lab_hours'];

        /* Asignar horas mínimas consecutivas (si no existen, usar el máximo) */
        $subject['min_consecutive_class_hours'] = isset($subject['min_consecutive_class_hours']) ? (int) $subject['min_consecutive_class_hours'] : (int) $subject['max_consecutive_class_hours'];
        $subject['min_consecutive_lab_hours'] = isset($subject['min_consecutive_lab_hours']) ? (int) $subject['min_consecutive_lab_hours'] : (int) $subject['max_consecutive_lab_hours'];
    }
    $subjects_by_group[$group['group_id']] = $subjects;
}