<?php

$sql = "
    SELECT 
        g.group_id, 
        g.group_name, 
        s.schedule_day,
        s.start_time,
        s.end_time,
        sub.subject_name
    FROM 
        `groups` g 
    LEFT JOIN 
        `schedules` s ON g.group_id = s.group_id
    LEFT JOIN 
        `teacher_subjects` ts ON s.teacher_subject_id = ts.teacher_subject_id
    LEFT JOIN 
        `subjects` sub ON ts.subject_id = sub.subject_id
    WHERE 
        g.estado = 'activo'";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
