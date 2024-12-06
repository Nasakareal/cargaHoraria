<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    include('../../../app/config.php');
    $group_id = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);

    if (!$group_id) {
        echo json_encode(['error' => 'ID de grupo inválido.']);
        exit;
    }

    try {
        $sql = "SELECT 
                    sa.assignment_id,
                    sa.schedule_day,
                    sa.start_time,
                    sa.end_time,
                    s.subject_name,
                    t.teacher_name,
                    CONCAT(COALESCE(RIGHT(r.building, 1), 'N/A'), '-', r.classroom_name) AS classroom_name,
                    sa.tipo_espacio
                FROM 
                    schedule_assignments sa
                LEFT JOIN 
                    subjects s ON sa.subject_id = s.subject_id
                LEFT JOIN 
                    teachers t ON sa.teacher_id = t.teacher_id
                LEFT JOIN 
                    classrooms r ON sa.classroom_id = r.classroom_id
                WHERE 
                    sa.group_id = :group_id
                ORDER BY 
                    sa.schedule_day, sa.start_time";

        $query = $pdo->prepare($sql);
        $query->execute([':group_id' => $group_id]);

        $horarios = $query->fetchAll(PDO::FETCH_ASSOC);

        if (empty($horarios)) {
            echo json_encode(['error' => 'No se encontraron asignaciones para el grupo seleccionado.']);
        } else {
            echo json_encode($horarios);
        }
    } catch (PDOException $e) {
        
        file_put_contents('error_log.txt', $e->getMessage(), FILE_APPEND);
        echo json_encode(['error' => 'Error al consultar la base de datos.']);
    }
    exit;
}
?>
