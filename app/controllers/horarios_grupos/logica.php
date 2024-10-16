<?php
/* Consulta para obtener materias y grupos activos */
$sql_groups = "SELECT g.group_id, g.group_name, g.turn_id, p.program_id 
               FROM `groups` g
               JOIN programs p ON g.program_id = p.program_id
               WHERE g.estado = '1'";
$stmt_groups = $pdo->prepare($sql_groups);
$stmt_groups->execute();
$groups = $stmt_groups->fetchAll(PDO::FETCH_ASSOC);

/* Obtener materias por programa */
$sql_subjects = "SELECT * FROM subjects WHERE estado = '1'";
$stmt_subjects = $pdo->prepare($sql_subjects);
$stmt_subjects->execute();
$all_subjects = $stmt_subjects->fetchAll(PDO::FETCH_ASSOC);

/* Horarios disponibles por turno */
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

/* Agrupar materias por programa */
$subjects_by_program = [];
foreach ($all_subjects as $subject) {
    $subjects_by_program[$subject['program_id']][] = $subject;
}

/* Lógica de asignación de horarios */
$asignaciones = [];
foreach ($groups as $group) {
    $turno = $group['turn_id'] == 1 ? 'MATUTINO' : ($group['turn_id'] == 2 ? 'VESPERTINO' : 'MIXTO');
    $horario = $horarios_disponibles[$turno];

    /* Obtener materias del grupo */
    $subjects = $subjects_by_program[$group['program_id']] ?? [];
    
    /* Control de horas asignadas por día */
    $horas_por_dia = [
        'Lunes' => 0,
        'Martes' => 0,
        'Miércoles' => 0,
        'Jueves' => 0,
        'Viernes' => 0,
        'Sábado' => 0
    ];

    $horarios_display = '';

    /* Asignación de horarios */
    foreach ($subjects as $subject) {
        $horas_necesarias = $subject['weekly_hours'];

        /* Recorremos los días de la semana y los bloques */
        foreach ($horario as $dia => $bloques) {
            foreach ($bloques as $index => $bloque) {
                $start_time = strtotime($bloque['start']);
                $end_time = strtotime($bloque['end']);
                $total_hours_available = ($end_time - $start_time) / 3600;

                /* Asignar horas mientras haya horas necesarias y espacio disponible */
                while ($horas_necesarias > 0 && $horas_por_dia[$dia] < 8 && $total_hours_available > 0) {
                    $horas_a_asignar = min($horas_necesarias, 8 - $horas_por_dia[$dia], $total_hours_available);

                    if ($horas_a_asignar > 0) {
                        /* Guardamos el horario asignado */
                        $horarios_display .= "$dia: " . date('H:i', $start_time) . " - " . date('H:i', strtotime("+{$horas_a_asignar} hours", $start_time)) .
                            " ({$subject['subject_name']} - $horas_a_asignar horas)<br>";

                        /* Reducir las horas necesarias, actualizar horas asignadas y avanzar el tiempo de inicio */
                        $horas_necesarias -= $horas_a_asignar;
                        $horas_por_dia[$dia] += $horas_a_asignar;
                        $total_hours_available -= $horas_a_asignar;
                        $start_time = strtotime("+{$horas_a_asignar} hours", $start_time);

                        /* Actualizamos el bloque con el nuevo `start_time` para que no se repita */
                        $horario[$dia][$index]['start'] = date('H:i:s', $start_time);
                    } else {
                        break;
                    }
                }
            }
        }
    }

    if (empty($horarios_display)) {
        $horarios_display = 'No hay horarios asignados';
    }

    $asignaciones[$group['group_name']] = $horarios_display;
}
?>
