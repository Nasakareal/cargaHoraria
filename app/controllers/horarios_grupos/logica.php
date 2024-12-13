<?php
session_start();

include_once('../../../app/config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(100);
ini_set('memory_limit', '356M');
error_log("Límite de memoria inicial: " . ini_get('memory_limit'));

$pdo->exec("DELETE FROM schedule_assignments WHERE estado = 'activo'");

include('../../../app/controllers/horarios_grupos/horarios_disponibles.php');

$mensajes_error = [];

function remove_accents($string)
{
    return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
}

$turn_id_to_turno = [
    1 => 'MATUTINO',
    2 => 'VESPERTINO',
    3 => 'MIXTO',
    4 => 'ZINAPÉCUARO',
    5 => 'ENFERMERIA',
    6 => 'MATUTINO AVANZADO',
    7 => 'VESPERTINO AVANZADO',
];

try {
    $groups = $pdo->query("SELECT *, classroom_assigned, lab_assigned FROM `groups` WHERE estado = '1'")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener grupos: " . $e->getMessage());
    $_SESSION['mensaje'] = "Error al obtener grupos.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/horarios_grupos/");
    exit();
}

$excluded_turnos = ['MIXTO', 'ZINAPÉCUARO'];

$groups = array_filter($groups, function ($group) use ($turn_id_to_turno, $excluded_turnos) {
    $turno_id = $group['turn_id'];
    $turno = $turn_id_to_turno[$turno_id] ?? 'MATUTINO';

    $turno_lower = mb_strtolower($turno, 'UTF-8');
    $excluded_turnos_lower = array_map('mb_strtolower', $excluded_turnos);

    $turno_normalized = remove_accents($turno_lower);
    $excluded_normalized = array_map('remove_accents', $excluded_turnos_lower);

    return !in_array($turno_normalized, $excluded_normalized);
});

$groups = array_values($groups);

include('../../../app/controllers/grupos/materias_grupos.php');

function asignarBloqueHorario($pdo, $subject, $group, $dia, $start_time, $end_time, $tipo_espacio, &$mensajes_error)
{
    error_log("Intentando asignar materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) al grupo ID: {$group['group_id']} en $dia de " . date('H:i:s', $start_time) . " a " . date('H:i:s', $end_time) . " en $tipo_espacio.");

    if (!isset($subject['subject_id'])) {
        error_log("Error: 'subject_id' no está definido.");
        $mensajes_error[] = "Error en la materia con ID desconocido para el grupo ID: {$group['group_id']}.";
        return false;
    }
    $formatted_start_time = date('H:i:s', $start_time);
    $formatted_end_time = date('H:i:s', $end_time);

    $espacio_id = $tipo_espacio === 'Laboratorio' ? $group['lab_assigned'] : $group['classroom_assigned'];

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
        return false;
    }

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
        return false;
    }

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

    if (isset($subject['teacher_id']) && !empty($subject['teacher_id'])) {
        $teacher_check_sql = "SELECT COUNT(*) FROM teacher_availability WHERE teacher_id = :teacher_id";
        $teacher_check_stmt = $pdo->prepare($teacher_check_sql);
        $teacher_check_stmt->execute([':teacher_id' => $subject['teacher_id']]);
        $teacher_has_availability = $teacher_check_stmt->fetchColumn() > 0;

        if ($teacher_has_availability) {
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
                return false;
            }

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
                return false;
            }
        }
    }

    $check_duplicate_sql = "SELECT COUNT(*) FROM schedule_assignments
        WHERE teacher_id = :teacher_id
        AND schedule_day = :schedule_day
        AND start_time = :start_time
        AND end_time = :end_time";
    $check_duplicate_params = [
        ':teacher_id' => $subject['teacher_id'],
        ':schedule_day' => $dia,
        ':start_time' => $formatted_start_time,
        ':end_time' => $formatted_end_time
    ];

    $check_duplicate_stmt = $pdo->prepare($check_duplicate_sql);
    $check_duplicate_stmt->execute($check_duplicate_params);

    if ($check_duplicate_stmt->fetchColumn() > 0) {
        error_log("Asignación duplicada detectada para el profesor ID: {$subject['teacher_id']} en $dia de $formatted_start_time a $formatted_end_time.");
        return false;
    }

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

    error_log("Materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) asignada al grupo ID: {$group['group_id']} en $dia de $formatted_start_time a $formatted_end_time en $tipo_espacio.");

    return true;
}

function distribuirMateriasEnSemana($pdo, $group, &$subjects, $horario_turno, $dias_turno, &$mensajes_error, $horarios_disponibles)
{
    $grupo_turno = $horario_turno;

    if (!isset($horarios_disponibles[$grupo_turno])) {
        error_log("Error: Horario de turno '$grupo_turno' no encontrado para el grupo ID: {$group['group_id']}");
        $mensajes_error[] = "Horario de turno '$grupo_turno' no encontrado para el grupo ID: {$group['group_id']}.";
        return;
    }

    $horas_asignadas_por_materia_dia = [];

    usort($subjects, function ($a, $b) {
        return $b['remaining_hours'] - $a['remaining_hours'];
    });

    foreach ($dias_turno as $dia) {
        if (!isset($horarios_disponibles[$grupo_turno][$dia])) {
            continue;
        }

        $start_time_str = $horarios_disponibles[$grupo_turno][$dia]['start'];
        $end_time_str = $horarios_disponibles[$grupo_turno][$dia]['end'];
        $inicio_turno = strtotime($start_time_str);
        $fin_turno = strtotime($end_time_str);

        for ($hora = $inicio_turno; $hora < $fin_turno; $hora += 3600) {
            foreach ($subjects as &$subject) {
                if ($subject['remaining_hours'] <= 0) {
                    continue;
                }

                $tipo_espacio = $subject['type'];
                $max_consecutive_hours = $subject['max_consecutive_hours'];
                $min_consecutive_hours = $subject['min_consecutive_hours'];

                // Initialize assigned hours
                if (!isset($horas_asignadas_por_materia_dia[$dia][$subject['subject_id']])) {
                    $horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] = 0;
                }

                // Check max consecutive hours
                if ($horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] >= $max_consecutive_hours) {
                    continue;
                }

                $next_hora = $hora + 3600;
                if ($next_hora > $fin_turno) {
                    $next_hora = $fin_turno;
                }

                if (asignarBloqueHorario($pdo, $subject, $group, $dia, $hora, $next_hora, $tipo_espacio, $mensajes_error)) {
                    $subject['remaining_hours'] -= 1;
                    $horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] += 1;
                    break;
                }
            }
        }
    }

    // Assign remaining hours by iterating again to fill gaps
    foreach ($dias_turno as $dia) {
        if (!isset($horarios_disponibles[$grupo_turno][$dia])) {
            continue;
        }

        $start_time_str = $horarios_disponibles[$grupo_turno][$dia]['start'];
        $end_time_str = $horarios_disponibles[$grupo_turno][$dia]['end'];
        $inicio_turno = strtotime($start_time_str);
        $fin_turno = strtotime($end_time_str);

        for ($hora = $inicio_turno; $hora < $fin_turno; $hora += 3600) {
            foreach ($subjects as &$subject) {
                if ($subject['remaining_hours'] <= 0) {
                    continue;
                }

                $tipo_espacio = $subject['type'];
                $max_consecutive_hours = $subject['max_consecutive_hours'];
                $min_consecutive_hours = $subject['min_consecutive_hours'];

                // Initialize assigned hours
                if (!isset($horas_asignadas_por_materia_dia[$dia][$subject['subject_id']])) {
                    $horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] = 0;
                }

                // Check max consecutive hours
                if ($horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] >= $max_consecutive_hours) {
                    continue;
                }

                $next_hora = $hora + 3600;
                if ($next_hora > $fin_turno) {
                    $next_hora = $fin_turno;
                }

                if (asignarBloqueHorario($pdo, $subject, $group, $dia, $hora, $next_hora, $tipo_espacio, $mensajes_error)) {
                    $subject['remaining_hours'] -= 1;
                    $horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] += 1;
                    break;
                }
            }
        }
    }
}

function todasMateriasAsignadas($subjects)
{
    foreach ($subjects as $subject) {
        if ($subject['remaining_hours'] > 0) {
            return false;
        }
    }
    return true;
}

foreach ($groups as $group) {
    $turno_id = $group['turn_id'];
    $turno = $turn_id_to_turno[$turno_id] ?? 'MATUTINO';

    if (!isset($horarios_disponibles[$turno])) {
        error_log("Horario de turno '$turno' no encontrado para el grupo ID: {$group['group_id']}");
        $mensajes_error[] = "Horario de turno '$turno' no encontrado para el grupo ID: {$group['group_id']}.";
        continue;
    }

    $horario_turno = $turno;
    $dias_turno = $dias_semana[$turno] ?? [];

    if (empty($dias_turno)) {
        error_log("No hay días definidos para el turno '$turno' del grupo ID: {$group['group_id']}");
        $mensajes_error[] = "No hay días definidos para el turno '$turno' del grupo ID: {$group['group_id']}.";
        continue;
    }

    if (!isset($subjects_by_group[$group['group_id']])) {
        error_log("No se encontraron materias para el grupo ID: {$group['group_id']}");
        $mensajes_error[] = "No se encontraron materias para el grupo ID: {$group['group_id']}.";
        continue;
    }

    try {
        $pdo->prepare("DELETE FROM schedule_assignments WHERE estado = 'activo' AND group_id = :group_id")
            ->execute([':group_id' => $group['group_id']]);

        $subjects = array_map(function ($subject) {
            return [
                'subject_id' => $subject['subject_id'],
                'subject_name' => $subject['subject_name'],
                'teacher_id' => $subject['teacher_id'],
                'remaining_hours' => $subject['remaining_hours'],
                'type' => $subject['type'],
                'max_consecutive_hours' => $subject['max_consecutive_hours'],
                'min_consecutive_hours' => $subject['min_consecutive_hours'],
            ];
        }, $subjects_by_group[$group['group_id']]);

        distribuirMateriasEnSemana($pdo, $group, $subjects, $horario_turno, $dias_turno, $mensajes_error, $horarios_disponibles);

        if (todasMateriasAsignadas($subjects)) {
            error_log("Todas las materias asignadas para el grupo ID: {$group['group_id']}.");
        } else {
            foreach ($subjects as $subject) {
                if ($subject['remaining_hours'] > 0) {
                    $mensajes_error[] = "No se pudieron asignar todas las horas para la materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) del grupo ID: {$group['group_id']}. Horas restantes: {$subject['remaining_hours']}.";
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Error durante la asignación para el grupo ID: {$group['group_id']}: " . $e->getMessage());
        $mensajes_error[] = "Error durante la asignación para el grupo ID: {$group['group_id']}.";
    }
}

if (!empty($mensajes_error)) {
    $_SESSION['mensaje'] = "Algunas materias no pudieron ser asignadas correctamente.";
    $_SESSION['icono'] = "error";
    $_SESSION['detalles_error'] = $mensajes_error;
} else {
    $_SESSION['mensaje'] = "Todas las materias fueron asignadas exitosamente.";
    $_SESSION['icono'] = "success";
}

header('Location:' . APP_URL . "/admin/horarios_grupos/");
exit();
?>
