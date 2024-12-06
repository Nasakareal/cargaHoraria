<?php

include_once('../../../app/config.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


set_time_limit(300);


$pdo->exec("DELETE FROM schedule_assignments WHERE estado = 'activo'");

/* Definir los horarios disponibles por turno y día */
$horarios_disponibles = [
    'MATUTINO' => [
        'Lunes' => ['start' => '07:00:00', 'end' => '15:00:00'],
        'Martes' => ['start' => '07:00:00', 'end' => '15:00:00'],
        'Miércoles' => ['start' => '07:00:00', 'end' => '15:00:00'],
        'Jueves' => ['start' => '07:00:00', 'end' => '15:00:00'],
        'Viernes' => ['start' => '07:00:00', 'end' => '15:00:00'],
    ],
    'VESPERTINO' => [
        'Lunes' => ['start' => '12:00:00', 'end' => '20:00:00'],
        'Martes' => ['start' => '12:00:00', 'end' => '20:00:00'],
        'Miércoles' => ['start' => '12:00:00', 'end' => '20:00:00'],
        'Jueves' => ['start' => '12:00:00', 'end' => '20:00:00'],
        'Viernes' => ['start' => '12:00:00', 'end' => '20:00:00'],
    ],
    'MIXTO' => [
        'Viernes' => ['start' => '16:00:00', 'end' => '20:00:00'],
        'Sábado' => ['start' => '07:00:00', 'end' => '18:00:00']
    ],
    'ZINAPÉCUARO' => [
        'Viernes' => ['start' => '16:00:00', 'end' => '20:00:00'],
        'Sábado' => ['start' => '07:00:00', 'end' => '18:00:00']
    ]
];

$dias_semana = [
    'MATUTINO' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
    'VESPERTINO' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
    'MIXTO' => ['Viernes', 'Sábado'],
    'ZINAPÉCUARO' => ['Viernes', 'Sábado']
];

/* Obtener datos de grupos */
$groups = $pdo->query("SELECT *, classroom_assigned, lab_assigned FROM `groups` WHERE estado = '1'")->fetchAll(PDO::FETCH_ASSOC);

/* Obtener materias específicas de cada grupo desde `group_subjects` */
$subjects_by_group = [];
foreach ($groups as $group) {
    $stmt = $pdo->prepare("SELECT s.*, gs.group_id, g.classroom_assigned, g.lab_assigned, ts.teacher_id 
                           FROM subjects s 
                           JOIN group_subjects gs ON gs.subject_id = s.subject_id 
                           JOIN `groups` g ON g.group_id = gs.group_id
                           LEFT JOIN teacher_subjects ts ON ts.subject_id = s.subject_id AND ts.group_id = gs.group_id
                           WHERE gs.group_id = :group_id AND s.estado = '1'");
    $stmt->execute([':group_id' => $group['group_id']]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* Inicializar horas restantes por materia */
    foreach ($subjects as &$subject) {
        $subject['remaining_class_hours'] = $subject['class_hours'];
        $subject['remaining_lab_hours'] = $subject['lab_hours'];
        $subject['total_remaining_hours'] = $subject['class_hours'] + $subject['lab_hours'];

        /* Asignar horas mínimas consecutivas (si no existen, usar el máximo) */
        $subject['min_consecutive_class_hours'] = isset($subject['min_consecutive_class_hours']) ? (int) $subject['min_consecutive_class_hours'] : (int) $subject['max_consecutive_class_hours'];
        $subject['min_consecutive_lab_hours'] = isset($subject['min_consecutive_lab_hours']) ? (int) $subject['min_consecutive_lab_hours'] : (int) $subject['max_consecutive_lab_hours'];
    }
    $subjects_by_group[$group['group_id']] = $subjects;
}

/* Función para asignar un bloque horario */
function asignarBloqueHorario($pdo, $subject, $group, $dia, $start_time, $end_time, $tipo_espacio)
{
    /* Registrar intento de asignación */
    error_log("Intentando asignar materia ID: {$subject['subject_id']} al grupo ID: {$group['group_id']} en $dia de " . date('H:i:s', $start_time) . " a " . date('H:i:s', $end_time) . " en $tipo_espacio.");

    if (!isset($subject['subject_id'])) {
        error_log("Error: 'subject_id' no está definido.");
        return false;
    }
    $formatted_start_time = date('H:i:s', $start_time);
    $formatted_end_time = date('H:i:s', $end_time);

    /* Determinar el ID del espacio a utilizar (aula o laboratorio) */
    $espacio_id = $tipo_espacio === 'Laboratorio' ? $group['lab_assigned'] : $group['classroom_assigned'];

    /* Verificar disponibilidad del espacio */
    $check_availability_sql = "SELECT COUNT(*) FROM schedule_assignments 
        WHERE 
            (classroom_id = :espacio_id AND tipo_espacio = :tipo_espacio)
            AND schedule_day = :schedule_day 
            AND (
                (start_time < :end_time AND end_time > :start_time)
            )";
    $check_availability_params = [
        ':espacio_id' => $espacio_id,
        ':tipo_espacio' => $tipo_espacio,
        ':schedule_day' => $dia,
        ':start_time' => $formatted_start_time,
        ':end_time' => $formatted_end_time
    ];

    $check_availability = $pdo->prepare($check_availability_sql);
    $check_availability->execute($check_availability_params);

    if ($check_availability->fetchColumn() > 0) {
        error_log("Espacio $tipo_espacio ID: $espacio_id no disponible en $dia de $formatted_start_time a $formatted_end_time.");
        return false; /* Bloque no disponible */
    }

    /* Verificar si el grupo ya tiene asignada una materia en ese horario */
    $check_group_availability_sql = "SELECT COUNT(*) FROM schedule_assignments 
        WHERE 
            group_id = :group_id 
            AND schedule_day = :schedule_day 
            AND (
                (start_time < :end_time AND end_time > :start_time)
            )";
    $check_group_availability_params = [
        ':group_id' => $group['group_id'],
        ':schedule_day' => $dia,
        ':start_time' => $formatted_start_time,
        ':end_time' => $formatted_end_time
    ];

    $check_group_availability = $pdo->prepare($check_group_availability_sql);
    $check_group_availability->execute($check_group_availability_params);

    if ($check_group_availability->fetchColumn() > 0) {
        error_log("Grupo ID: {$group['group_id']} ya tiene una materia asignada en $dia de $formatted_start_time a $formatted_end_time.");
        return false; /* El grupo ya tiene una materia asignada en este horario */
    }

    /* Mapear el día de español a inglés para la disponibilidad del profesor */
    $day_name_mapping = [
        'Lunes' => 'Monday',
        'Martes' => 'Tuesday',
        'Miércoles' => 'Wednesday',
        'Jueves' => 'Thursday',
        'Viernes' => 'Friday',
        'Sábado' => 'Saturday',
        'Domingo' => 'Sunday',
    ];
    $dia_en_ing = $day_name_mapping[$dia] ?? $dia;

    /* Verificar disponibilidad del profesor si hay un profesor asignado */
    if (isset($subject['teacher_id']) && !empty($subject['teacher_id'])) {
        /* Verificar disponibilidad del profesor */
        $teacher_availability_sql = "SELECT COUNT(*) FROM teacher_availability
            WHERE teacher_id = :teacher_id
            AND day_of_week = :day_of_week
            AND start_time <= :start_time
            AND end_time >= :end_time";
        $teacher_availability_params = [
            ':teacher_id' => $subject['teacher_id'],
            ':day_of_week' => $dia_en_ing,
            ':start_time' => $formatted_start_time,
            ':end_time' => $formatted_end_time
        ];

        $teacher_availability_stmt = $pdo->prepare($teacher_availability_sql);
        $teacher_availability_stmt->execute($teacher_availability_params);

        if ($teacher_availability_stmt->fetchColumn() == 0) {
            error_log("Profesor ID: {$subject['teacher_id']} no disponible en $dia de $formatted_start_time a $formatted_end_time.");
            return false; /* El profesor no está disponible en este horario */
        }

        /* Verificar si el profesor ya tiene asignada una materia en ese horario */
        $check_teacher_schedule_sql = "SELECT COUNT(*) FROM schedule_assignments 
            WHERE 
                teacher_id = :teacher_id 
                AND schedule_day = :schedule_day 
                AND (
                    (start_time < :end_time AND end_time > :start_time)
                )";
        $check_teacher_schedule_params = [
            ':teacher_id' => $subject['teacher_id'],
            ':schedule_day' => $dia,
            ':start_time' => $formatted_start_time,
            ':end_time' => $formatted_end_time
        ];

        $check_teacher_schedule = $pdo->prepare($check_teacher_schedule_sql);
        $check_teacher_schedule->execute($check_teacher_schedule_params);

        if ($check_teacher_schedule->fetchColumn() > 0) {
            error_log("Profesor ID: {$subject['teacher_id']} ya tiene una materia asignada en $dia de $formatted_start_time a $formatted_end_time.");
            return false; /* El profesor ya tiene una materia asignada en este horario */
        }
    }

    /* Insertar el bloque horario en la base de datos */
    $sql_insert = "INSERT INTO schedule_assignments 
                   (subject_id, group_id, teacher_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion, tipo_espacio)
                   VALUES (:subject_id, :group_id, :teacher_id, :classroom_id, :schedule_day, :start_time, :end_time, 'activo', NOW(), :tipo_espacio)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([
        ':subject_id' => $subject['subject_id'],
        ':group_id' => $group['group_id'],
        ':teacher_id' => $subject['teacher_id'] ?? null,
        ':classroom_id' => $espacio_id,
        ':schedule_day' => $dia,
        ':start_time' => $formatted_start_time,
        ':end_time' => $formatted_end_time,
        ':tipo_espacio' => $tipo_espacio
    ]);

    error_log("Materia ID: {$subject['subject_id']} asignada al grupo ID: {$group['group_id']} en $dia de $formatted_start_time a $formatted_end_time en $tipo_espacio.");

    return true; /* Bloque asignado exitosamente */
}

/* Función para calcular la disponibilidad de días */
function calcularDisponibilidadDias($pdo, $group_id, $dias_turno, $inicio_turno, $fin_turno)
{
    $disponibilidad_dias = [];
    foreach ($dias_turno as $dia) {
        $horas_disponibles = 0;
        $inicio_actual = $inicio_turno;
        while ($inicio_actual < $fin_turno) {
            $formatted_start_time = date('H:i:s', $inicio_actual);
            $formatted_end_time = date('H:i:s', strtotime('+1 hour', $inicio_actual));

            /* Verificar si el grupo ya tiene asignada una materia en este horario */
            $check_group_availability_sql = "SELECT COUNT(*) FROM schedule_assignments 
                WHERE 
                    group_id = :group_id 
                    AND schedule_day = :schedule_day 
                    AND (
                        (start_time < :end_time AND end_time > :start_time)
                    )";
            $check_group_availability_params = [
                ':group_id' => $group_id,
                ':schedule_day' => $dia,
                ':start_time' => $formatted_start_time,
                ':end_time' => $formatted_end_time
            ];

            $check_group_availability = $pdo->prepare($check_group_availability_sql);
            $check_group_availability->execute($check_group_availability_params);

            if ($check_group_availability->fetchColumn() == 0) {
                $horas_disponibles++;
            }

            $inicio_actual = strtotime('+1 hour', $inicio_actual);
        }
        $disponibilidad_dias[$dia] = $horas_disponibles;
    }

    /* Ordenar los días por horas disponibles de mayor a menor */
    arsort($disponibilidad_dias);
    return array_keys($disponibilidad_dias);
}

/* Función para distribuir materias en la semana */
function distribuirMateriasEnSemana($pdo, $group, &$subjects, $horario_turno, $dias_turno)
{
    global $horarios_disponibles;

    /* Inicializar prioridad para turnos "MIXTO" y "ZINAPÉCUARO" */
    $turnos_prioritarios = ['MIXTO', 'ZINAPÉCUARO'];

    /* Obtener el nombre del turno del grupo */
    $grupo_turno = array_search($horario_turno, $horarios_disponibles, true);

    /* Inicializar registro de horas asignadas por materia y día */
    $horas_asignadas_por_materia_dia = [];

    /* Ordenar las materias para priorizar aquellas con más horas restantes */
    usort($subjects, function ($a, $b) {
        return $b['total_remaining_hours'] - $a['total_remaining_hours'];
    });

    /* Asignación principal de materias durante la semana */
    foreach ($dias_turno as $dia) {
        if (isset($horarios_disponibles[$grupo_turno][$dia])) {
            $start_time_str = $horarios_disponibles[$grupo_turno][$dia]['start'];
            $end_time_str = $horarios_disponibles[$grupo_turno][$dia]['end'];
            $inicio_turno = strtotime($start_time_str);
            $fin_turno = strtotime($end_time_str);
        } else {
            // Si no hay un horario definido para este día y turno, saltar
            continue;
        }

        $inicio_actual = $inicio_turno;

        while ($inicio_actual < $fin_turno) {
            $asignacion_realizada = false;

            foreach ($subjects as &$subject) {
                $subject_id = $subject['subject_id'];
                $remaining_class_hours = $subject['remaining_class_hours'];
                $remaining_lab_hours = $subject['remaining_lab_hours'];

                /* Inicializar horas asignadas en el día si no existe */
                if (!isset($horas_asignadas_por_materia_dia[$dia])) {
                    $horas_asignadas_por_materia_dia[$dia] = [];
                }
                if (!isset($horas_asignadas_por_materia_dia[$dia][$subject_id])) {
                    $horas_asignadas_por_materia_dia[$dia][$subject_id] = 0;
                }

                /* Determinar tipo de espacio y horas a asignar */
                $tipo_espacio = null;
                $horas_a_asignar = 0;

                /* Implementar prioridad para turnos "MIXTO" y "ZINAPÉCUARO" */
                if (in_array($grupo_turno, $turnos_prioritarios)) {
                    /* Solo asignar materias de laboratorio los viernes y sábados */
                    if (!in_array($dia, ['Viernes', 'Sábado'])) {
                        continue;
                    }
                    if ($remaining_lab_hours <= 0) {
                        continue; /* No hay horas de laboratorio para asignar */
                    }
                    $tipo_espacio = 'Laboratorio';
                    $max_consecutive_hours = max((int) $subject['max_consecutive_lab_hours'], 1);
                    $min_consecutive_hours = max((int) $subject['min_consecutive_lab_hours'], 1);
                    $horas_restantes_materia = $remaining_lab_hours;
                } else {
                    /* Asignar materias normalmente */
                    if ($remaining_class_hours > 0) {
                        $tipo_espacio = 'Aula';
                        $max_consecutive_hours = max((int) $subject['max_consecutive_class_hours'], 1);
                        $min_consecutive_hours = max((int) $subject['min_consecutive_class_hours'], 1);
                        $horas_restantes_materia = $remaining_class_hours;
                    } elseif ($remaining_lab_hours > 0) {
                        $tipo_espacio = 'Laboratorio';
                        $max_consecutive_hours = max((int) $subject['max_consecutive_lab_hours'], 1);
                        $min_consecutive_hours = max((int) $subject['min_consecutive_lab_hours'], 1);
                        $horas_restantes_materia = $remaining_lab_hours;
                    } else {
                        continue; /* No hay horas para asignar, pasar a la siguiente materia */
                    }
                }

                /* Calcular las horas que se pueden asignar sin exceder el máximo diario */
                $horas_disponibles_dia = $max_consecutive_hours - $horas_asignadas_por_materia_dia[$dia][$subject_id];
                $horas_disponibles_en_dia = floor(($fin_turno - $inicio_actual) / 3600);

                /* Respetar las horas mínimas consecutivas */
                $horas_a_asignar = min($horas_restantes_materia, $horas_disponibles_dia, $horas_disponibles_en_dia);
                if ($horas_a_asignar < $min_consecutive_hours) {
                    /* No hay suficiente tiempo para asignar el mínimo de horas consecutivas, avanzar una hora */
                    $inicio_actual = strtotime("+1 hour", $inicio_actual);
                    continue;
                } else {
                    /* Ajustar las horas a asignar para que sean al menos el mínimo y no excedan el máximo */
                    $horas_a_asignar = max($min_consecutive_hours, $horas_a_asignar);
                }

                $fin_bloque = strtotime("+{$horas_a_asignar} hours", $inicio_actual);

                if (asignarBloqueHorario($pdo, $subject, $group, $dia, $inicio_actual, $fin_bloque, $tipo_espacio)) {
                    /* Actualizar horas restantes y horas asignadas en el día */
                    if ($tipo_espacio === 'Aula') {
                        $subject['remaining_class_hours'] -= $horas_a_asignar;
                    } else {
                        $subject['remaining_lab_hours'] -= $horas_a_asignar;
                    }
                    $subject['total_remaining_hours'] -= $horas_a_asignar;

                    $horas_asignadas_por_materia_dia[$dia][$subject_id] += $horas_a_asignar;
                    $inicio_actual = $fin_bloque;
                    $asignacion_realizada = true;

                    /* Si las horas restantes son cero, continuar con la siguiente materia */
                    if ($subject['total_remaining_hours'] <= 0) {
                        continue;
                    }
                } else {
                    /* No se pudo asignar en este bloque, intentar en el siguiente */
                    $inicio_actual = strtotime("+1 hour", $inicio_actual);
                }

                /* Si hemos llegado al fin del turno, salir del bucle */
                if ($inicio_actual >= $fin_turno) {
                    break;
                }
            }

            if (!$asignacion_realizada) {
                /* No se pudo asignar ninguna materia en este bloque, avanzar una hora */
                $inicio_actual = strtotime("+1 hour", $inicio_actual);
            }
        }

        /* Asignación de horas restantes priorizando días con más disponibilidad */
        $dias_ordenados = calcularDisponibilidadDias($pdo, $group['group_id'], $dias_turno, $inicio_turno, $fin_turno);

        foreach ($subjects as &$subject) {
            $subject_id = $subject['subject_id'];
            $remaining_class_hours = $subject['remaining_class_hours'];
            $remaining_lab_hours = $subject['remaining_lab_hours'];
            $total_remaining_hours = $subject['total_remaining_hours'];

            if ($total_remaining_hours > 0) {
                /* Intentar asignar las horas restantes en los días con más disponibilidad */
                foreach ($dias_ordenados as $dia) {
                    if (isset($horarios_disponibles[$grupo_turno][$dia])) {
                        $start_time_str = $horarios_disponibles[$grupo_turno][$dia]['start'];
                        $end_time_str = $horarios_disponibles[$grupo_turno][$dia]['end'];
                        $inicio_turno = strtotime($start_time_str);
                        $fin_turno = strtotime($end_time_str);
                    } else {
                        // Si no hay un horario definido para este día y turno, saltar
                        continue;
                    }

                    $inicio_actual = $inicio_turno;

                    /* Inicializar horas asignadas en el día si no existe */
                    if (!isset($horas_asignadas_por_materia_dia[$dia])) {
                        $horas_asignadas_por_materia_dia[$dia] = [];
                    }
                    if (!isset($horas_asignadas_por_materia_dia[$dia][$subject_id])) {
                        $horas_asignadas_por_materia_dia[$dia][$subject_id] = 0;
                    }

                    $max_consecutive_hours_class = max((int) $subject['max_consecutive_class_hours'], 1);
                    $min_consecutive_hours_class = max((int) $subject['min_consecutive_class_hours'], 1);
                    $max_consecutive_hours_lab = max((int) $subject['max_consecutive_lab_hours'], 1);
                    $min_consecutive_hours_lab = max((int) $subject['min_consecutive_lab_hours'], 1);

                    while ($inicio_actual < $fin_turno && $subject['total_remaining_hours'] > 0) {
                        /* Determinar tipo de espacio y horas a asignar */
                        if ($remaining_class_hours > 0) {
                            $tipo_espacio = 'Aula';
                            $max_consecutive_hours = $max_consecutive_hours_class;
                            $min_consecutive_hours = $min_consecutive_hours_class;
                            $horas_restantes_materia = $remaining_class_hours;
                        } elseif ($remaining_lab_hours > 0) {
                            $tipo_espacio = 'Laboratorio';
                            $max_consecutive_hours = $max_consecutive_hours_lab;
                            $min_consecutive_hours = $min_consecutive_hours_lab;
                            $horas_restantes_materia = $remaining_lab_hours;
                        } else {
                            break; /* No hay horas restantes */
                        }

                        /* Calcular las horas que se pueden asignar sin exceder el máximo diario */
                        $horas_disponibles_dia = $max_consecutive_hours - $horas_asignadas_por_materia_dia[$dia][$subject_id];
                        $horas_disponibles_en_dia = floor(($fin_turno - $inicio_actual) / 3600);

                        /* Respetar las horas mínimas consecutivas */
                        $horas_a_asignar = min($horas_restantes_materia, $horas_disponibles_dia, $horas_disponibles_en_dia);
                        if ($horas_a_asignar < $min_consecutive_hours) {
                            /* No hay suficiente tiempo para asignar el mínimo de horas consecutivas, intentar asignar horas remanentes */
                            if ($horas_restantes_materia > 0) {
                                $horas_a_asignar = min($horas_restantes_materia, $horas_disponibles_dia, $horas_disponibles_en_dia);
                                if ($horas_a_asignar <= 0) {
                                    /* No se pueden asignar más horas, avanzar en el tiempo */
                                    $inicio_actual = strtotime("+1 hour", $inicio_actual);
                                    continue;
                                }
                            } else {
                                break; /* No hay horas restantes */
                            }
                        }

                        $fin_bloque = strtotime("+{$horas_a_asignar} hours", $inicio_actual);

                        if (asignarBloqueHorario($pdo, $subject, $group, $dia, $inicio_actual, $fin_bloque, $tipo_espacio)) {
                            /* Actualizar horas restantes */
                            if ($tipo_espacio === 'Aula') {
                                $subject['remaining_class_hours'] -= $horas_a_asignar;
                            } else {
                                $subject['remaining_lab_hours'] -= $horas_a_asignar;
                            }
                            $subject['total_remaining_hours'] -= $horas_a_asignar;

                            $horas_asignadas_por_materia_dia[$dia][$subject_id] += $horas_a_asignar;
                            $inicio_actual = $fin_bloque;
                        } else {
                            $inicio_actual = strtotime("+1 hour", $inicio_actual);
                        }
                    }

                    /* Si ya no quedan horas por asignar, salir del bucle */
                    if ($subject['total_remaining_hours'] <= 0) {
                        break;
                    }
                }
            }
        }
    }
}
/* Ejecutar la asignación para cada grupo */
foreach ($groups as $group) {
    $turno_id = $group['turn_id'];
    $turno_keys = array_keys($horarios_disponibles);
    $turno = isset($turno_keys[$turno_id - 1]) ? $turno_keys[$turno_id - 1] : 'MATUTINO';
    $horario_turno = $horarios_disponibles[$turno];
    $dias_turno = $dias_semana[$turno];

    if (!isset($subjects_by_group[$group['group_id']])) {
        continue;
    }

    $subjects = $subjects_by_group[$group['group_id']];
    distribuirMateriasEnSemana($pdo, $group, $subjects, $horario_turno, $dias_turno);
}

/* Redirigir al finalizar */
header('Location:' . APP_URL . "/admin/horarios_grupos/");
exit();

?>
