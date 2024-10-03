<?php
include('../../app/config.php');


$sql_grupos = "SELECT g.group_id, g.group_name, COUNT(s.student_id) AS capacity 
               FROM `groups` g
               JOIN students s ON g.group_id = s.group_id
               GROUP BY g.group_id
               ORDER BY capacity DESC";
$query_grupos = $pdo->prepare($sql_grupos);
$query_grupos->execute();
$grupos = $query_grupos->fetchAll(PDO::FETCH_ASSOC);


$sql_salones = "SELECT * FROM classrooms WHERE estado = 'activo' ORDER BY capacity ASC";
$query_salones = $pdo->prepare($sql_salones);
$query_salones->execute();
$salones_disponibles = $query_salones->fetchAll(PDO::FETCH_ASSOC);


$salones_asignados = [];


foreach ($grupos as $grupo) {
    $capacidad_grupo = $grupo['capacity'];
    $salon_asignado = null;

    
    foreach ($salones_disponibles as $salon) {
        if ($salon['capacity'] >= $capacidad_grupo && !in_array($salon['classroom_id'], $salones_asignados)) {
            
            $sql_verificar = "SELECT COUNT(*) AS ocupado 
                              FROM schedules 
                              WHERE classroom_id = :classroom_id 
                              AND schedule_day = :schedule_day 
                              AND ((start_time <= :start_time AND end_time > :start_time) 
                              OR (start_time < :end_time AND end_time >= :end_time))";
            $query_verificar = $pdo->prepare($sql_verificar);
            $query_verificar->execute([
                ':classroom_id' => $salon['classroom_id'],
                ':schedule_day' => 'Lunes', 
                ':start_time' => '08:00:00', 
                ':end_time' => '09:00:00'    
            ]);
            $resultado = $query_verificar->fetch(PDO::FETCH_ASSOC);

            if ($resultado['ocupado'] == 0) { 
                $salon_asignado = $salon;
                $salones_asignados[] = $salon['classroom_id']; 
                break; 
            }
        }
    }

    
    if ($salon_asignado) {
        echo "Grupo: " . $grupo['group_name'] . " asignado al salón: " . $salon_asignado['classroom_name'] . "<br>";

        
        $sql_insertar_horario = "INSERT INTO schedules (teacher_subject_id, classroom_id, schedule_day, start_time, end_time, fyh_creacion, estado) 
                                 VALUES (:teacher_subject_id, :classroom_id, :schedule_day, :start_time, :end_time, NOW(), 'activo')";
        $query_insertar = $pdo->prepare($sql_insertar_horario);
        $query_insertar->execute([
            ':teacher_subject_id' => 1, 
            ':classroom_id' => $salon_asignado['classroom_id'],
            ':schedule_day' => 'Lunes', 
            ':start_time' => '08:00:00', 
            ':end_time' => '09:00:00'
        ]);
    } else {
        echo "Grupo: " . $grupo['group_name'] . " no tiene salón disponible.<br>";
    }
}
?>
