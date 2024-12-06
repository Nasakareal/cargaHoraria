<?php
function listarAsignaciones($pdo)
{
    try {
        $sql = "SELECT 
                    sa.assignment_id, 
                    sa.schedule_day, 
                    sa.start_time, 
                    sa.end_time, 
                    s.subject_name, 
                    t.teacher_name, 
                    g.group_name, 
                    r.classroom_name
                FROM 
                    schedule_assignments sa
                LEFT JOIN 
                    subjects s ON sa.subject_id = s.subject_id
                LEFT JOIN 
                    teachers t ON sa.teacher_id = t.teacher_id
                LEFT JOIN 
                    `groups` g ON sa.group_id = g.group_id
                LEFT JOIN 
                    classrooms r ON sa.classroom_id = r.classroom_id
                ORDER BY 
                    sa.schedule_day, sa.start_time";

        $query = $pdo->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }
}
?>
