<?php
include_once('../../app/config.php');

/* Limpiar todas las asignaciones activas previas antes de asignar nuevas */
$pdo->exec("DELETE FROM schedule_assignments WHERE estado = 'activo'");

/* Obtener todos los grupos activos con sus programas y cuatrimestres */
$sql_groups = "SELECT g.group_id, g.group_name, g.turn_id, p.program_id, g.term_id
               FROM `groups` g
               JOIN programs p ON g.program_id = p.program_id
               WHERE g.estado = '1'";
$stmt_groups = $pdo->prepare($sql_groups);
$stmt_groups->execute();
$groups = $stmt_groups->fetchAll(PDO::FETCH_ASSOC);

/* Obtener todas las materias activas y agruparlas por programa y cuatrimestre */
$sql_subjects = "SELECT * FROM subjects WHERE estado = '1'";
$stmt_subjects = $pdo->prepare($sql_subjects);
$stmt_subjects->execute();
$all_subjects = $stmt_subjects->fetchAll(PDO::FETCH_ASSOC);

/* Agrupar materias por programa y cuatrimestre */
$subjects_by_program_and_term = [];
foreach ($all_subjects as $subject) {
    $subjects_by_program_and_term[$subject['program_id']][$subject['term_id']][] = $subject;
}

/* Definir horarios disponibles para cada turno */
$horarios_disponibles = [
    'MATUTINO' => [
        'Lunes' => [['start' => '07:00:00', 'end' => '15:00:00']],
        'Martes' => [['start' => '07:00:00', 'end' => '15:00:00']],
        'Miércoles' => [['start' => '07:00:00', 'end' => '15:00:00']],
        'Jueves' => [['start' => '07:00:00', 'end' => '15:00:00']],
        'Viernes' => [['start' => '07:00:00', 'end' => '12:00:00']], /* Matutino desaloja a las 12 */
    ],
    'VESPERTINO' => [
        'Lunes' => [['start' => '12:00:00', 'end' => '20:00:00']],
        'Martes' => [['start' => '12:00:00', 'end' => '20:00:00']],
        'Miércoles' => [['start' => '12:00:00', 'end' => '20:00:00']],
        'Jueves' => [['start' => '12:00:00', 'end' => '20:00:00']],
        'Viernes' => [['start' => '12:00:00', 'end' => '20:00:00']], /* Vespertino inicia a las 12 */
    ],
    'MIXTO' => [
        'Viernes' => [['start' => '16:00:00', 'end' => '20:00:00']],
        'Sábado' => [['start' => '07:00:00', 'end' => '18:00:00']],
    ]
];

/* Asignación de horarios para cada grupo */
foreach ($groups as $group) {
    $turno = $group['turn_id'] == 1 ? 'MATUTINO' : ($group['turn_id'] == 2 ? 'VESPERTINO' : 'MIXTO');
    $horario = $horarios_disponibles[$turno];
    $subjects = $subjects_by_program_and_term[$group['program_id']][$group['term_id']] ?? [];

    /* Filtrado y prioridad de asignación para turnos 'MIXTO' y 'ZINAPECUARO' */
    $turno_prioritario = in_array($turno, ['MIXTO', 'ZINAPECUARO']);

    foreach ($subjects as $subject) {
        $horas_restantes = $subject['weekly_hours'];
        $max_consecutive_hours = $subject['max_consecutive_class_hours'];

        foreach ($horario as $dia => $bloques) {
            $horas_asignadas_dia = 0;
            foreach ($bloques as $bloque) {
                $current_start_time = isset($hora_inicio_dia[$dia]) ? $hora_inicio_dia[$dia] : strtotime($bloque['start']);
                $end_time_block = strtotime($bloque['end']);

                /* Verificar si es laboratorio */
                $es_laboratorio = $subject['lab_hours'] > 0;
                if ($es_laboratorio) {
                    /* Comprobar si el laboratorio está ocupado en ese horario */
                    $sql_check_lab = "SELECT COUNT(*) FROM schedule_assignments 
                                      WHERE schedule_day = :dia 
                                      AND ((start_time BETWEEN :start_time AND :end_time) 
                                           OR (end_time BETWEEN :start_time AND :end_time))";
                    $stmt_check_lab = $pdo->prepare($sql_check_lab);
                    $stmt_check_lab->execute([
                        ':dia' => $dia,
                        ':start_time' => date('H:i:s', $current_start_time),
                        ':end_time' => date('H:i:s', $end_time_block)
                    ]);

                    $lab_occupied = $stmt_check_lab->fetchColumn() > 0;
                    if ($lab_occupied && !$turno_prioritario) {
                        continue;
                    }
                }

                while ($horas_restantes > 0 && $horas_asignadas_dia < 8 && $current_start_time < $end_time_block) {
                    $horas_disponibles_en_bloque = ($end_time_block - $current_start_time) / 3600;
                    $horas_a_asignar = min($horas_restantes, $max_consecutive_hours, $horas_disponibles_en_bloque);

                    if ($horas_a_asignar <= 0)
                        break;

                    // Asegurar el registro preciso de inicio y fin de cada materia en base de datos
                    $end_time_assignment = date('H:i:s', strtotime("+{$horas_a_asignar} hours", $current_start_time));

                    $sql_insert = "INSERT INTO schedule_assignments (subject_id, teacher_id, group_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion)
                                   VALUES (:subject_id, NULL, :group_id, NULL, :schedule_day, :start_time, :end_time, 'activo', NOW())";
                    $stmt_insert = $pdo->prepare($sql_insert);
                    $stmt_insert->execute([
                        ':subject_id' => $subject['subject_id'],
                        ':group_id' => $group['group_id'],
                        ':schedule_day' => $dia,
                        ':start_time' => date('H:i:s', $current_start_time),
                        ':end_time' => $end_time_assignment
                    ]);

                    $horas_restantes -= $horas_a_asignar;
                    $horas_asignadas_dia += $horas_a_asignar;
                    $current_start_time = strtotime($end_time_assignment);
                    $hora_inicio_dia[$dia] = $current_start_time;

                    if ($horas_asignadas_dia >= $max_consecutive_hours)
                        break;
                }

                if ($horas_restantes <= 0)
                    break;
            }
            if ($horas_restantes <= 0)
                break;
        }
    }
}
?>
