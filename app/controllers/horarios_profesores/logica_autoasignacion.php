<?php
include('../../app/config.php');


$sql_profesores = "
    SELECT t.teacher_id, t.teacher_name, ts.subject_id, s.subject_name, s.weekly_hours 
    FROM teachers t
    JOIN teacher_subjects ts ON t.teacher_id = ts.teacher_id
    JOIN subjects s ON ts.subject_id = s.subject_id
    WHERE t.estado = '1' AND s.estado = '1'";
$stmt_profesores = $pdo->prepare($sql_profesores);
$stmt_profesores->execute();
$profesores = $stmt_profesores->fetchAll(PDO::FETCH_ASSOC);

if (!$profesores || count($profesores) === 0) {
    echo "No se encontraron profesores con materias asignadas.";
    exit;
}


$horarios_disponibles = [
    'MATUTINO' => ['07:00:00', '09:00:00', '11:00:00', '13:00:00'],
    'VESPERTINO' => ['12:00:00', '14:00:00', '16:00:00', '18:00:00', '20:00:00'],
    'MIXTO' => ['16:00:00', '18:00:00', '20:00:00', '07:00:00', '09:00:00']
];


$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

try {
    $pdo->beginTransaction();

    foreach ($profesores as $profesor) {
        
        $horas_restantes = $profesor['weekly_hours'];  
        while ($horas_restantes > 0) {
            $dia = $dias_semana[array_rand($dias_semana)];
            $turno = 'MATUTINO'; 

            
            $horario = $horarios_disponibles[$turno];
            $start_time = $horario[array_rand($horario)];
            $end_time = date('H:i:s', strtotime('+2 hours', strtotime($start_time))); 

            
            $sql_insert = "INSERT INTO schedule_assignments 
                (teacher_id, subject_id, schedule_day, start_time, end_time, fyh_creacion, estado) 
                VALUES (:teacher_id, :subject_id, :schedule_day, :start_time, :end_time, NOW(), 'activo')";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                ':teacher_id' => $profesor['teacher_id'],
                ':subject_id' => $profesor['subject_id'],
                ':schedule_day' => $dia,
                ':start_time' => $start_time,
                ':end_time' => $end_time
            ]);

           
            $horas_restantes -= 2;
        }
    }

    $pdo->commit();
    echo "Horarios asignados correctamente.";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
