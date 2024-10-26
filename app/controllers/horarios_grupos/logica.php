<?php
include_once('../../app/config.php');

// Consulta para obtener todos los grupos activos con su turno y programa
$sql_groups = "SELECT g.group_id, g.group_name, g.turn_id, p.program_id 
               FROM `groups` g
               JOIN programs p ON g.program_id = p.program_id
               WHERE g.estado = '1'";
$stmt_groups = $pdo->prepare($sql_groups);
$stmt_groups->execute();
$groups = $stmt_groups->fetchAll(PDO::FETCH_ASSOC);

if (!$groups) {
    echo "No se encontraron grupos activos.<br>";
    return;
}

// Obtener todas las materias activas en la base de datos
$sql_subjects = "SELECT * FROM subjects WHERE estado = '1'";
$stmt_subjects = $pdo->prepare($sql_subjects);
$stmt_subjects->execute();
$all_subjects = $stmt_subjects->fetchAll(PDO::FETCH_ASSOC);

if (!$all_subjects) {
    echo "No se encontraron materias activas.<br>";
    return;
}

// Definir horarios disponibles para cada turno
$horarios_disponibles = [
    'MATUTINO' => [
        'Lunes' => [['start' => '07:00:00', 'end' => '15:00:00']],
        'Martes' => [['start' => '07:00:00', 'end' => '15:00:00']],
        'Miércoles' => [['start' => '07:00:00', 'end' => '15:00:00']],
        'Jueves' => [['start' => '07:00:00', 'end' => '15:00:00']],
        'Viernes' => [['start' => '07:00:00', 'end' => '15:00:00']],
    ],
    'VESPERTINO' => [
        'Lunes' => [['start' => '12:00:00', 'end' => '20:00:00']],
        'Martes' => [['start' => '12:00:00', 'end' => '20:00:00']],
        'Miércoles' => [['start' => '12:00:00', 'end' => '20:00:00']],
        'Jueves' => [['start' => '12:00:00', 'end' => '20:00:00']],
        'Viernes' => [['start' => '12:00:00', 'end' => '20:00:00']],
    ],
    'MIXTO' => [
        'Viernes' => [['start' => '16:00:00', 'end' => '20:00:00']],
        'Sábado' => [['start' => '07:00:00', 'end' => '18:00:00']],
    ]
];

// Agrupar materias por programa
$subjects_by_program = [];
foreach ($all_subjects as $subject) {
    $subjects_by_program[$subject['program_id']][] = $subject;
}

// Lógica de asignación de horarios para cada grupo y sus materias
foreach ($groups as $group) {
    $turno = $group['turn_id'] == 1 ? 'MATUTINO' : ($group['turn_id'] == 2 ? 'VESPERTINO' : 'MIXTO');
    if (!isset($horarios_disponibles[$turno])) {
        echo "No hay horarios disponibles para el turno: " . $turno . "<br>";
        continue;
    }

    $horario = $horarios_disponibles[$turno];
    $subjects = $subjects_by_program[$group['program_id']] ?? [];

    if (empty($subjects)) {
        echo "No se encontraron materias para el programa con ID: " . $group['program_id'] . "<br>";
        continue;
    }

    echo "Procesando grupo: " . $group['group_name'] . "<br>";

    // Control de horas asignadas por día para evitar superposiciones
    $horas_por_dia = ['Lunes' => 0, 'Martes' => 0, 'Miércoles' => 0, 'Jueves' => 0, 'Viernes' => 0, 'Sábado' => 0];

    // Asignar horarios a cada materia del grupo
    foreach ($subjects as $subject) {
        $horas_necesarias = $subject['weekly_hours'];
        echo "Asignando materia: " . $subject['subject_name'] . "<br>";

        // Recorremos los días y bloques de horario del turno
        foreach ($horario as $dia => $bloques) {
            foreach ($bloques as $index => $bloque) {
                $start_time = strtotime($bloque['start']);
                $end_time = strtotime($bloque['end']);
                $total_hours_available = ($end_time - $start_time) / 3600;

                // Asignar horas mientras haya horas necesarias y espacio disponible
                while ($horas_necesarias > 0 && $horas_por_dia[$dia] < 8 && $total_hours_available > 0) {
                    $horas_a_asignar = min($horas_necesarias, 8 - $horas_por_dia[$dia], $total_hours_available);
                    if ($horas_a_asignar <= 0)
                        break;

                    $new_start_time = strtotime("+{$horas_a_asignar} hours", $start_time);
                    $end_time_insert = date('H:i:s', $new_start_time);

                    // Insertar el horario asignado en schedule_assignments
                    try {
                        $sql_insert = "INSERT INTO schedule_assignments (subject_id, teacher_id, group_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion)
                                       VALUES (:subject_id, NULL, :group_id, NULL, :schedule_day, :start_time, :end_time, 'activo', NOW())";
                        $stmt_insert = $pdo->prepare($sql_insert);

                        $stmt_insert->execute([
                            ':subject_id' => $subject['subject_id'],
                            ':group_id' => $group['group_id'],
                            ':schedule_day' => $dia,
                            ':start_time' => date('H:i:s', $start_time),
                            ':end_time' => $end_time_insert
                        ]);

                        echo "Horario asignado para la materia '{$subject['subject_name']}' del grupo '{$group['group_name']}' en {$dia} de " . date('H:i', $start_time) . " a " . date('H:i', $new_start_time) . "<br>";

                        $horas_necesarias -= $horas_a_asignar;
                        $horas_por_dia[$dia] += $horas_a_asignar;
                        $total_hours_available -= $horas_a_asignar;
                        $start_time = $new_start_time;

                    } catch (PDOException $e) {
                        echo "Error en la inserción de horario para '{$subject['subject_name']}' en '{$group['group_name']}': " . $e->getMessage() . "<br>";
                    }
                }
            }
        }
    }
}
?>
