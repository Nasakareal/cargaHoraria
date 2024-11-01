<?php

include_once('../../app/config.php');

/* Configuración del tiempo de ejecución */
set_time_limit(300);
$pdo->exec("DELETE FROM schedule_assignments WHERE estado = 'activo'");

/* Horarios para cada turno */
$horarios_disponibles = [
    'MATUTINO' => ['07:00:00' => '15:00:00'],
    'VESPERTINO' => ['12:00:00' => '20:00:00'],
    'MIXTO' => ['16:00:00' => '20:00:00', '07:00:00' => '18:00:00'],
    'ZINAPECUARO' => ['16:00:00' => '20:00:00', '07:00:00' => '18:00:00']
];

/* Días para la asignación */
$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

/* Obtener datos de grupos y materias activas */
$groups = $pdo->query("SELECT * FROM `groups` WHERE estado = '1'")->fetchAll(PDO::FETCH_ASSOC);
$subjects = $pdo->query("SELECT * FROM subjects WHERE estado = '1'")->fetchAll(PDO::FETCH_ASSOC);

/* Materias agrupadas por programa y término */
$subjects_by_program_and_term = [];
foreach ($subjects as $subject) {
    $subjects_by_program_and_term[$subject['program_id']][$subject['term_id']][] = $subject;
}

/* Función para asignar horas respetando el máximo consecutivo */
function asignarHoras($pdo, $subject, $group, $dia, $inicio, $fin, $tipo_espacio)
{
    $horas_restantes = $subject['weekly_hours'];
    $max_horas = $subject['lab_hours'] > 0 ? $subject['max_consecutive_lab_hours'] : $subject['max_consecutive_class_hours'];
    $current_start_time = strtotime($inicio);
    $bloque_fin = strtotime($fin);

    while ($horas_restantes > 0 && $current_start_time < $bloque_fin) {
        $horas_a_asignar = min($horas_restantes, $max_horas);
        $current_end_time = strtotime("+{$horas_a_asignar} hours", $current_start_time);

        if ($current_end_time > $bloque_fin) {
            $horas_a_asignar = ($bloque_fin - $current_start_time) / 3600;
            $current_end_time = $bloque_fin;
        }

        /* Formateo */
        $formatted_start_time = date('H:i:s', $current_start_time);
        $formatted_end_time = date('H:i:s', $current_end_time);

        /* Comprobar disponibilidad del horario para evitar duplicados */
        $check_availability = $pdo->prepare("
            SELECT COUNT(*) FROM schedule_assignments 
            WHERE group_id = :group_id 
            AND schedule_day = :schedule_day 
            AND (
                (start_time <= :start_time AND end_time > :start_time) OR 
                (start_time < :end_time AND end_time >= :end_time)
            )
        ");
        $check_availability->execute([
            ':group_id' => $group['group_id'],
            ':schedule_day' => $dia,
            ':start_time' => $formatted_start_time,
            ':end_time' => $formatted_end_time
        ]);
        if ($check_availability->fetchColumn() > 0) {
            break; // Saltar si el horario está ocupado
        }

        /* Insertar asignación */
        $sql_insert = "INSERT INTO schedule_assignments 
                       (subject_id, group_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion, tipo_espacio)
                       VALUES (:subject_id, :group_id, :classroom_id, :schedule_day, :start_time, :end_time, 'activo', NOW(), :tipo_espacio)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([
            ':subject_id' => $subject['subject_id'],
            ':group_id' => $group['group_id'],
            ':classroom_id' => $tipo_espacio === 'Laboratorio' ? null : $group['classroom_assigned'],
            ':schedule_day' => $dia,
            ':start_time' => $formatted_start_time,
            ':end_time' => $formatted_end_time,
            ':tipo_espacio' => $tipo_espacio
        ]);

        /* Reducir horas restantes */
        $horas_restantes -= $horas_a_asignar;
        $current_start_time = $current_end_time;
    }
}

/* Asignación principal */
foreach ($groups as $group) {
    $turno = $group['turn_id'] == 1 ? 'MATUTINO' : ($group['turn_id'] == 2 ? 'VESPERTINO' : ($group['turn_id'] == 3 ? 'MIXTO' : 'ZINAPECUARO'));
    $horario_turno = $horarios_disponibles[$turno];

    if (!isset($subjects_by_program_and_term[$group['program_id']][$group['term_id']])) {
        continue;
    }

    $subjects = $subjects_by_program_and_term[$group['program_id']][$group['term_id']];
    $dia_actual = 0;

    foreach ($subjects as $subject) {
        $tipo_espacio = $subject['lab_hours'] > 0 ? 'Laboratorio' : 'Aula';

        if (($turno == 'MIXTO' || $turno == 'ZINAPECUARO') && !$subject['lab_hours']) {
            continue;
        }

        $dia = $dias_semana[$dia_actual % count($dias_semana)];
        $dia_actual++;

        foreach ($horario_turno as $inicio => $fin) {
            asignarHoras($pdo, $subject, $group, $dia, $inicio, $fin, $tipo_espacio);
        }
    }
}
