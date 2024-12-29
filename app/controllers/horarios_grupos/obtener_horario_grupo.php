<?php
function obtenerHorarioGrupo($group_id, $pdo)
{
    $sql_horarios = "SELECT 
                        sa.schedule_day AS day, 
                        sa.start_time AS start, 
                        sa.end_time AS end, 
                        sa.tipo_espacio,
                        s.subject_name, 
                        sh.shift_name,
                        r.classroom_name AS room_name,
                        RIGHT(r.building, 1) AS building_last_char,
                        t.teacher_name,
                        g.group_name
                     FROM 
                        schedule_assignments sa
                     JOIN 
                        subjects s ON sa.subject_id = s.subject_id
                     JOIN 
                        `groups` g ON sa.group_id = g.group_id
                     JOIN 
                        shifts sh ON g.turn_id = sh.shift_id
                     LEFT JOIN 
                        classrooms r ON sa.classroom_id = r.classroom_id
                     LEFT JOIN 
                        teachers t ON sa.teacher_id = t.teacher_id
                     WHERE 
                        sa.group_id = :group_id
                     ORDER BY sa.schedule_day, sa.start_time";

    $query_horarios = $pdo->prepare($sql_horarios);
    $query_horarios->execute([':group_id' => $group_id]);
    return $query_horarios->fetchAll(PDO::FETCH_ASSOC);
}
?>
