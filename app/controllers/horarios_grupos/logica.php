<?php
session_start();

include_once('../../../app/config.php');

// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Aumentar el límite de tiempo de ejecución si es necesario
set_time_limit(800);

// Aumentar el límite de memoria
ini_set('memory_limit', '300M'); // Aumentado a 256M para mayor capacidad

// Eliminar asignaciones activas anteriores
$pdo->exec("DELETE FROM schedule_assignments WHERE estado = 'activo'");

include('../../../app/controllers/horarios_grupos/horarios_disponibles.php');

/* Inicializar un array para recolectar mensajes de error */
$mensajes_error = [];

/* Función para eliminar acentos de una cadena */
function remove_accents($string)
{
    return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
}

/* Definir el mapeo entre turn_id y nombre del turno */
$turn_id_to_turno = [
    1 => 'MATUTINO',
    2 => 'VESPERTINO',
    3 => 'MIXTO',
    4 => 'ZINAPÉCUARO',
    5 => 'ENFERMERIA',
    6 => 'MATUTINO AVANZADO',
    7 => 'VESPERTINO AVANZADO',
    // Añade más mapeos según tus turnos si existen
];

/* Obtener datos de grupos */
try {
    $groups = $pdo->query("SELECT *, classroom_assigned, lab_assigned FROM `groups` WHERE estado = '1'")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener grupos: " . $e->getMessage());
    $_SESSION['mensaje'] = "Error al obtener grupos.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/horarios_grupos/");
    exit();
}

// Definir los turnos a excluir
$excluded_turnos = ['MIXTO', 'ZINAPÉCUARO']; // Asegúrate de que estos nombres coincidan exactamente con los de tu base de datos

// Filtrar grupos para excluir "MIXTO" y "ZINAPÉCUARO"
$groups = array_filter($groups, function ($group) use ($turn_id_to_turno, $excluded_turnos) {
    $turno_id = $group['turn_id'];
    if (!isset($turn_id_to_turno[$turno_id])) {
        // Si no hay un mapeo definido, asumir 'MATUTINO' por defecto
        $turno = 'MATUTINO';
    } else {
        $turno = $turn_id_to_turno[$turno_id];
    }

    // Convertir ambos strings a minúsculas para una comparación insensible a mayúsculas
    $turno_lower = mb_strtolower($turno, 'UTF-8');
    $excluded_turnos_lower = array_map(function ($t) {
        return mb_strtolower($t, 'UTF-8');
    }, $excluded_turnos);

    // Comparar sin considerar tildes
    $turno_normalized = remove_accents($turno_lower);
    $excluded_normalized = array_map('remove_accents', $excluded_turnos_lower);

    return !in_array($turno_normalized, $excluded_normalized);
});

// Reindexar el arreglo después de filtrar
$groups = array_values($groups);

include('../../../app/controllers/grupos/materias_grupos.php');

/* Definir un número máximo de intentos para evitar bucles infinitos */
define('MAX_INTENTOS', 50);

/* Función para asignar un bloque horario */
function asignarBloqueHorario($pdo, $subject, $group, $dia, $start_time, $end_time, $tipo_espacio, &$mensajes_error)
{
    /* Registrar intento de asignación */
    error_log("Intentando asignar materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) al grupo ID: {$group['group_id']} en $dia de " . date('H:i:s', $start_time) . " a " . date('H:i:s', $end_time) . " en $tipo_espacio.");

    if (!isset($subject['subject_id'])) {
        error_log("Error: 'subject_id' no está definido.");
        $mensajes_error[] = "Error en la materia con ID desconocido para el grupo ID: {$group['group_id']}.";
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
        $mensajes_error[] = "Espacio $tipo_espacio (ID: $espacio_id) no disponible para la materia '{$subject['subject_name']}' del grupo ID: {$group['group_id']} el $dia de $formatted_start_time a $formatted_end_time.";
        return false;
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
        $mensajes_error[] = "El grupo ID: {$group['group_id']} ya tiene una materia asignada el $dia de $formatted_start_time a $formatted_end_time.";
        return false;
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
        /* Verificar si el profesor tiene alguna disponibilidad definida */
        $teacher_check_sql = "SELECT COUNT(*) FROM teacher_availability WHERE teacher_id = :teacher_id";
        $teacher_check_stmt = $pdo->prepare($teacher_check_sql);
        $teacher_check_stmt->execute([':teacher_id' => $subject['teacher_id']]);
        $teacher_has_availability = $teacher_check_stmt->fetchColumn() > 0;

        if ($teacher_has_availability) {
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
                $mensajes_error[] = "El profesor asignado (ID: {$subject['teacher_id']}) no está disponible el $dia de $formatted_start_time a $formatted_end_time para la materia '{$subject['subject_name']}' del grupo ID: {$group['group_id']}.";
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
                $mensajes_error[] = "El profesor (ID: {$subject['teacher_id']}) ya tiene una materia asignada el $dia de $formatted_start_time a $formatted_end_time.";
                return false; /* El profesor ya tiene una materia asignada en este horario */
            }
        }
    }

    /* Verificar si la asignación exacta ya existe para evitar duplicados */
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
        $mensajes_error[] = "El profesor (ID: {$subject['teacher_id']}) ya tiene una asignación el $dia de $formatted_start_time a $formatted_end_time.";
        return false;
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

    error_log("Materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) asignada al grupo ID: {$group['group_id']} en $dia de $formatted_start_time a $formatted_end_time en $tipo_espacio.");

    return true; /* Bloque asignado exitosamente */
}

/* Función para calcular la disponibilidad de días */
function calcularDisponibilidadDias($pdo, $group_id, $dias_turno, $horario_turno, $horarios_disponibles)
{
    // Verificar que $dias_turno sea un arreglo
    if (!is_array($dias_turno)) {
        error_log("Error: 'dias_turno' no es un arreglo.");
        return [];
    }

    $disponibilidad_dias = [];
    foreach ($dias_turno as $dia) {
        if (isset($horarios_disponibles[$horario_turno][$dia])) {
            $start_time_str = $horarios_disponibles[$horario_turno][$dia]['start'];
            $end_time_str = $horarios_disponibles[$horario_turno][$dia]['end'];
            $inicio_turno = strtotime($start_time_str);
            $fin_turno = strtotime($end_time_str);
        } else {
            // Si no hay un horario definido para este día y turno, saltar
            continue;
        }

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
function distribuirMateriasEnSemana($pdo, $group, &$subjects, $horario_turno, $dias_turno, &$mensajes_error, $horarios_disponibles)
{
    /* Inicializar prioridad para turnos "MIXTO" y "ZINAPÉCUARO" */
    $turnos_prioritarios = ['MIXTO', 'ZINAPÉCUARO'];

    /* Obtener el nombre del turno del grupo */
    // Aquí, $horario_turno ya es el nombre del turno, según el mapeo
    $grupo_turno = $horario_turno;

    /* Verificar si el turno es válido */
    if (!isset($horarios_disponibles[$grupo_turno])) {
        error_log("Error: Horario de turno '$grupo_turno' no encontrado para el grupo ID: {$group['group_id']}");
        $mensajes_error[] = "Horario de turno '$grupo_turno' no encontrado para el grupo ID: {$group['group_id']}.";
        return;
    }

    /* Inicializar registro de horas asignadas por materia y día */
    $horas_asignadas_por_materia_dia = [];

    /* Ordenar las materias para priorizar aquellas con más horas restantes */
    usort($subjects, function ($a, $b) {
        return $b['remaining_hours'] - $a['remaining_hours'];
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
                $remaining_hours = $subject['remaining_hours'];
                $tipo_espacio = $subject['type'];
                $max_consecutive_hours = $subject['max_consecutive_hours'];
                $min_consecutive_hours = $subject['min_consecutive_hours'];

                /* Inicializar horas asignadas en el día si no existe */
                if (!isset($horas_asignadas_por_materia_dia[$dia])) {
                    $horas_asignadas_por_materia_dia[$dia] = [];
                }
                if (!isset($horas_asignadas_por_materia_dia[$dia][$subject_id])) {
                    $horas_asignadas_por_materia_dia[$dia][$subject_id] = 0;
                }

                /* Determinar el tipo de espacio y validar según el turno */
                if (in_array($grupo_turno, $turnos_prioritarios)) {
                    // Solo asignar materias de laboratorio los viernes y sábados
                    if ($tipo_espacio !== 'Laboratorio' || !in_array($dia, ['Viernes', 'Sábado'])) {
                        continue;
                    }
                }

                /* Calcular las horas que se pueden asignar sin exceder el máximo */
                $horas_disponibles_dia = $max_consecutive_hours - ($horas_asignadas_por_materia_dia[$dia][$subject_id] ?? 0);
                $horas_disponibles_en_dia = floor(($fin_turno - $inicio_actual) / 3600);

                /*El aventurero */
                if ($tipo_espacio === 'Aula') {
                    $horas_a_asignar = max($min_consecutive_hours, min($remaining_hours, $horas_disponibles_dia, $horas_disponibles_en_dia));
                } else {
                    $horas_a_asignar = min($remaining_hours, $horas_disponibles_dia, $horas_disponibles_en_dia);
                }


                /* Respetar las horas mínimas consecutivas */
                // Ajustar min_consecutive_hours si las horas restantes son menores
                if ($horas_a_asignar < $min_consecutive_hours && $horas_a_asignar > 0) {
                    if ($tipo_espacio === 'Aula' && $horas_a_asignar <= $min_consecutive_hours) {
                        // Permitir asignar la hora restante para materias de Aula
                        // No modificar $min_consecutive_hours
                    } else {
                        // No cumplir con el mínimo, avanzar una hora
                        error_log("No se pudo asignar el mínimo de horas consecutivas para la materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) en el grupo ID: {$group['group_id']} el día $dia.");
                        $inicio_actual = strtotime("+1 hour", $inicio_actual);
                        continue;
                    }
                }

                /* Definir el fin del bloque */
                $fin_bloque = strtotime("+{$horas_a_asignar} hours", $inicio_actual);

                if ($fin_bloque > $fin_turno) {
                    /* Ajustar el fin del bloque al final del turno si excede */
                    $fin_bloque = $fin_turno;
                    $horas_a_asignar = floor(($fin_bloque - $inicio_actual) / 3600);
                    if ($horas_a_asignar < $min_consecutive_hours && $horas_a_asignar > 0) {
                        if ($tipo_espacio === 'Aula' && $horas_a_asignar <= $min_consecutive_hours) {
                            // Permitir asignar la hora restante para materias de Aula
                        } else {
                            // No cumplir con el mínimo, avanzar una hora
                            $inicio_actual = strtotime("+1 hour", $inicio_actual);
                            continue;
                        }
                    }
                }

                /* Intentar asignar el bloque horario */
                if ($horas_a_asignar > 0 && asignarBloqueHorario($pdo, $subject, $group, $dia, $inicio_actual, $fin_bloque, $tipo_espacio, $mensajes_error)) {
                    /* Actualizar horas restantes y horas asignadas en el día */
                    $subject['remaining_hours'] -= $horas_a_asignar;
                    $horas_asignadas_por_materia_dia[$dia][$subject_id] += $horas_a_asignar;
                    $inicio_actual = $fin_bloque;
                    $asignacion_realizada = true;

                    /* Si las horas restantes son cero, continuar con la siguiente materia */
                    if ($subject['remaining_hours'] <= 0) {
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
    }

    /* Asignación de horas restantes priorizando días con más disponibilidad */
    $dias_ordenados = calcularDisponibilidadDias($pdo, $group['group_id'], $dias_turno, $horario_turno, $horarios_disponibles);

    foreach ($subjects as &$subject) {
        $subject_id = $subject['subject_id'];
        $remaining_hours = $subject['remaining_hours'];
        $tipo_espacio = $subject['type'];
        $max_consecutive_hours = $subject['max_consecutive_hours'];
        $min_consecutive_hours = $subject['min_consecutive_hours'];

        if ($remaining_hours > 0) {
            /* Intentar asignar las horas restantes en los días con más disponibilidad */
            foreach ($dias_ordenados as $dia) {
                if (isset($horarios_disponibles[$grupo_turno][$dia])) {
                    $start_time_str = $horarios_disponibles[$grupo_turno][$dia]['start'];
                    $end_time_str = $horarios_disponibles[$grupo_turno][$dia]['end'];
                    $inicio_turno_dia = strtotime($start_time_str);
                    $fin_turno_dia = strtotime($end_time_str);
                } else {
                    // Si no hay un horario definido para este día y turno, saltar
                    continue;
                }

                $inicio_actual_dia = $inicio_turno_dia;

                /* Inicializar horas asignadas en el día si no existe */
                if (!isset($horas_asignadas_por_materia_dia[$dia])) {
                    $horas_asignadas_por_materia_dia[$dia] = [];
                }
                if (!isset($horas_asignadas_por_materia_dia[$dia][$subject_id])) {
                    $horas_asignadas_por_materia_dia[$dia][$subject_id] = 0;
                }

                while ($inicio_actual_dia < $fin_turno_dia && $subject['remaining_hours'] > 0) {
                    /* Calcular las horas disponibles para asignar */
                    $horas_disponibles_dia = $max_consecutive_hours - ($horas_asignadas_por_materia_dia[$dia][$subject_id] ?? 0);
                    $horas_disponibles_en_dia = floor(($fin_turno_dia - $inicio_actual_dia) / 3600);
                    $horas_a_asignar = min($subject['remaining_hours'], $horas_disponibles_dia, $horas_disponibles_en_dia);

                    /* Respetar las horas mínimas consecutivas */
                    if ($horas_a_asignar < $min_consecutive_hours && $horas_a_asignar > 0) {
                        if ($tipo_espacio === 'Aula' && $horas_a_asignar <= $min_consecutive_hours) {
                            // Permitir asignar la hora restante para materias de Aula
                        } else {
                            // No cumplir con el mínimo, avanzar una hora
                            error_log("No se pudo asignar el mínimo de horas consecutivas para la materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) en el grupo ID: {$group['group_id']} el día $dia.");
                            $inicio_actual_dia = strtotime("+1 hour", $inicio_actual_dia);
                            continue;
                        }
                    }

                    /* Definir el fin del bloque */
                    $fin_bloque_dia = strtotime("+{$horas_a_asignar} hours", $inicio_actual_dia);

                    if ($fin_bloque_dia > $fin_turno_dia) {
                        /* Ajustar el fin del bloque al final del turno si excede */
                        $fin_bloque_dia = $fin_turno_dia;
                        $horas_a_asignar = floor(($fin_bloque_dia - $inicio_actual_dia) / 3600);
                        if ($horas_a_asignar < $min_consecutive_hours && $horas_a_asignar > 0) {
                            if ($tipo_espacio === 'Aula' && $horas_a_asignar <= $min_consecutive_hours) {
                                // Permitir asignar la hora restante para materias de Aula
                            } else {
                                // No cumplir con el mínimo, avanzar una hora
                                $inicio_actual_dia = strtotime("+1 hour", $inicio_actual_dia);
                                continue;
                            }
                        }
                    }

                    /* Intentar asignar el bloque horario */
                    if ($horas_a_asignar > 0 && asignarBloqueHorario($pdo, $subject, $group, $dia, $inicio_actual_dia, $fin_bloque_dia, $tipo_espacio, $mensajes_error)) {
                        /* Actualizar horas restantes y horas asignadas en el día */
                        $subject['remaining_hours'] -= $horas_a_asignar;
                        $horas_asignadas_por_materia_dia[$dia][$subject_id] += $horas_a_asignar;
                        $inicio_actual_dia = $fin_bloque_dia;
                    } else {
                        /* No se pudo asignar en este bloque, intentar en el siguiente */
                        $inicio_actual_dia = strtotime("+1 hour", $inicio_actual_dia);
                    }
                }

                /* Si ya no quedan horas por asignar, salir del bucle */
                if ($subject['remaining_hours'] <= 0) {
                    break;
                }
            }
        }
    }
}

/* Función para verificar si todas las materias han sido asignadas */
function todasMateriasAsignadas($subjects)
{
    foreach ($subjects as $subject) {
        if ($subject['remaining_hours'] > 0) {
            return false;
        }
    }
    return true;
}

/* Ejecutar la asignación para cada grupo con bucle de reintentos */
foreach ($groups as $group) {
    $intento = 0;
    $asignacion_completa = false;

    while ($intento < MAX_INTENTOS && !$asignacion_completa) {
        $intento++;
        error_log("Intento $intento para el grupo ID: {$group['group_id']}");

        $turno_id = $group['turn_id'];
        if (!isset($turn_id_to_turno[$turno_id])) {
            $turno = 'MATUTINO'; // Por defecto
            error_log("Turno ID: $turno_id no mapeado. Usando 'MATUTINO' por defecto.");
        } else {
            $turno = $turn_id_to_turno[$turno_id];
        }

        if (!isset($horarios_disponibles[$turno])) {
            error_log("Horario de turno '$turno' no encontrado para el grupo ID: {$group['group_id']}");
            $mensajes_error[] = "Horario de turno '$turno' no encontrado para el grupo ID: {$group['group_id']}.";
            break;
        }

        $horario_turno = $turno;
        $dias_turno = $dias_semana[$turno] ?? []; // Asegurarse de que existe

        if (empty($dias_turno)) {
            error_log("No hay días definidos para el turno '$turno' del grupo ID: {$group['group_id']}");
            $mensajes_error[] = "No hay días definidos para el turno '$turno' del grupo ID: {$group['group_id']}.";
            break;
        }

        if (!isset($subjects_by_group[$group['group_id']])) {
            error_log("No se encontraron materias para el grupo ID: {$group['group_id']}");
            $mensajes_error[] = "No se encontraron materias para el grupo ID: {$group['group_id']}.";
            break;
        }

        try {
            // Eliminar asignaciones activas para este grupo antes de cada intento
            $pdo->prepare("DELETE FROM schedule_assignments WHERE estado = 'activo' AND group_id = :group_id")
                ->execute([':group_id' => $group['group_id']]);

            // Clonar el array de materias para no modificar el original en cada intento
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

            // Priorizar asignaciones de Laboratorio en turnos prioritarios
            usort($subjects, function ($a, $b) {
                // Priorizar Laboratorios
                if ($a['type'] === 'Laboratorio' && $b['type'] !== 'Laboratorio') {
                    return -1;
                }
                if ($a['type'] !== 'Laboratorio' && $b['type'] === 'Laboratorio') {
                    return 1;
                }
                // Luego, por más horas restantes
                return $b['remaining_hours'] - $a['remaining_hours'];
            });

            distribuirMateriasEnSemana($pdo, $group, $subjects, $horario_turno, $dias_turno, $mensajes_error, $horarios_disponibles);

            // Verificar si todas las materias han sido asignadas
            if (todasMateriasAsignadas($subjects)) {
                $asignacion_completa = true;
                error_log("Todas las materias asignadas para el grupo ID: {$group['group_id']} en $intento intento(s).");
            } else {
                error_log("Quedan materias por asignar para el grupo ID: {$group['group_id']} después del intento $intento.");
            }
        } catch (PDOException $e) {
            error_log("Error durante la asignación en el intento $intento para el grupo ID: {$group['group_id']}: " . $e->getMessage());
            $mensajes_error[] = "Error durante la asignación para el grupo ID: {$group['group_id']} en el intento $intento.";
            break;
        }
    }

    if (!$asignacion_completa) {
        error_log("No se pudieron asignar todas las materias para el grupo ID: {$group['group_id']} después de " . MAX_INTENTOS . " intentos.");
        $mensajes_error[] = "No se pudieron asignar todas las materias para el grupo ID: {$group['group_id']} después de " . MAX_INTENTOS . " intentos.";
    }
}

/* Verificar si hay mensajes de error y almacenarlos en la sesión */
if (!empty($mensajes_error)) {
    $_SESSION['mensaje'] = "Algunas materias no pudieron ser asignadas correctamente.";
    $_SESSION['icono'] = "error";
    $_SESSION['detalles_error'] = $mensajes_error;
} else {
    $_SESSION['mensaje'] = "Todas las materias fueron asignadas exitosamente.";
    $_SESSION['icono'] = "success";
}

/* Redirigir al finalizar */
header('Location:' . APP_URL . "/admin/horarios_grupos/");
exit();
?>