<?php
session_start();

include_once('../../../app/config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(100);
ini_set('memory_limit', '356M');
error_log("Límite de memoria inicial: " . ini_get('memory_limit'));

// 1. Eliminar solo asignaciones de Aula existentes
try {
    $delete_aula = $pdo->exec("DELETE FROM schedule_assignments WHERE estado = 'activo' AND tipo_espacio = 'Aula'");
    error_log("Asignaciones de Aula eliminadas: $delete_aula filas.");
} catch (PDOException $e) {
    error_log("Error al eliminar asignaciones de Aula: " . $e->getMessage());
    $_SESSION['mensaje'] = "Error al eliminar asignaciones de Aula.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/horarios_grupos/");
    exit();
}

// 2. Restaurar asignaciones de laboratorio
function restaurarAsignacionesLaboratorio($pdo)
{
    try {
        error_log("Iniciando la restauración de asignaciones de laboratorio...");

        $sql_restore = "INSERT INTO schedule_assignments 
                        (subject_id, group_id, teacher_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion, tipo_espacio)
                        SELECT 
                            subject_id, 
                            group_id, 
                            NULL AS teacher_id, 
                            lab1_assigned AS classroom_id, 
                            schedule_day, 
                            start_time, 
                            end_time, 
                            'activo', 
                            NOW(), 
                            'Laboratorio'
                        FROM manual_schedule_assignments
                        WHERE tipo_espacio = 'Laboratorio'";

        $rows_affected = $pdo->exec($sql_restore);
        error_log("Consulta de restauración ejecutada: $sql_restore");

        if ($rows_affected > 0) {
            error_log("Asignaciones de laboratorio restauradas exitosamente: $rows_affected filas insertadas.");
        } else {
            error_log("No se encontraron asignaciones de laboratorio para restaurar.");
        }
    } catch (PDOException $e) {
        error_log("Error al restaurar asignaciones de laboratorio: " . $e->getMessage());
        $_SESSION['mensaje'] = "Error al restaurar asignaciones de laboratorio.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/horarios_grupos/");
        exit();
    }
}

restaurarAsignacionesLaboratorio($pdo);

// 3. Incluir archivos necesarios
include('../../../app/controllers/horarios_grupos/horarios_disponibles.php');

$mensajes_error = [];

// Función para eliminar acentos
function remove_accents($string)
{
    return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
}

// Mapeo de turnos
$turn_id_to_turno = [
    1 => 'MATUTINO',
    2 => 'VESPERTINO',
    3 => 'MIXTO',
    4 => 'ZINAPÉCUARO',
    5 => 'ENFERMERIA',
    6 => 'MATUTINO AVANZADO',
    7 => 'VESPERTINO AVANZADO',
];

// 4. Obtener grupos activos
try {
    $groups = $pdo->query("SELECT *, classroom_assigned, lab_assigned FROM `groups` WHERE estado = '1'")->fetchAll(PDO::FETCH_ASSOC);
    error_log("Grupos obtenidos: " . count($groups));
} catch (PDOException $e) {
    error_log("Error al obtener grupos: " . $e->getMessage());
    $_SESSION['mensaje'] = "Error al obtener grupos.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/horarios_grupos/");
    exit();
}

// Turnos excluidos
$excluded_turnos = ['MIXTO', 'ZINAPÉCUARO'];

// 5. Filtrar grupos excluyendo ciertos turnos
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
error_log("Grupos después del filtrado: " . count($groups));

// 6. Incluir materias por grupo
include('../../../app/controllers/grupos/materias_grupos.php');

// 7. Función para asignar bloques de horario
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

    // Verificar disponibilidad del espacio
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

    // Verificar disponibilidad del grupo
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

    // Mapear días al inglés
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
        // Verificar disponibilidad del profesor
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

            // Verificar si el profesor ya tiene una asignación en ese horario
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

    // Verificar duplicados (solo para 'Aula')
    if ($tipo_espacio === 'Aula') {
        $check_duplicate_sql = "SELECT COUNT(*) FROM schedule_assignments
            WHERE 
                group_id = :group_id
                AND schedule_day = :schedule_day
                AND start_time = :start_time
                AND end_time = :end_time
                AND tipo_espacio = 'Aula'";
        $check_duplicate_params = [
            ':group_id' => $group['group_id'],
            ':schedule_day' => $dia,
            ':start_time' => $formatted_start_time,
            ':end_time' => $formatted_end_time
        ];

        $check_duplicate_stmt = $pdo->prepare($check_duplicate_sql);
        $check_duplicate_stmt->execute($check_duplicate_params);

        if ($check_duplicate_stmt->fetchColumn() > 0) {
            error_log("Asignación duplicada detectada para el grupo ID: {$group['group_id']} en $dia de $formatted_start_time a $formatted_end_time.");
            return false;
        }
    }

    // Insertar la asignación
    $sql_insert = "INSERT INTO schedule_assignments 
                   (subject_id, group_id, teacher_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion, tipo_espacio)
                   VALUES (:subject_id, :group_id, :teacher_id, :classroom_id, :schedule_day, :start_time, :end_time, 'activo', NOW(), :tipo_espacio)";
    $stmt_insert = $pdo->prepare($sql_insert);

    try {
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
    } catch (PDOException $e) {
        error_log("Error al insertar asignación: " . $e->getMessage());
        $mensajes_error[] = "Error al asignar materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) al grupo ID: {$group['group_id']}.";
        return false;
    }
}

// 8. Función para distribuir materias en la semana
function distribuirMateriasEnSemana($pdo, $group, &$subjects, $horario_turno, $dias_turno, &$mensajes_error, $horarios_disponibles)
{
    $grupo_turno = $horario_turno;

    if (!isset($horarios_disponibles[$grupo_turno])) {
        error_log("Error: Horario de turno '$grupo_turno' no encontrado para el grupo ID: {$group['group_id']}");
        $mensajes_error[] = "Horario de turno '$grupo_turno' no encontrado para el grupo ID: {$group['group_id']}.";
        return;
    }

    $horas_asignadas_por_materia_dia = [];

    // Ordenar las materias por horas restantes descendente
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

        for ($hora = $inicio_turno; $hora < $fin_turno; ) {
            $asignado = false;

            foreach ($subjects as &$subject) {
                if ($subject['remaining_hours'] <= 0) {
                    continue;
                }

                $tipo_espacio = $subject['type'];
                $max_consecutive_hours = $subject['max_consecutive_hours'];
                $min_consecutive_hours = $subject['min_consecutive_hours'];

                if (!isset($horas_asignadas_por_materia_dia[$dia][$subject['subject_id']])) {
                    $horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] = 0;
                }

                if ($horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] >= $max_consecutive_hours) {
                    continue;
                }

                $bloque_duracion = ($tipo_espacio === 'Aula') ? 1 : 2;

                $proxima_hora = $hora + ($bloque_duracion * 3600);
                if ($proxima_hora > $fin_turno) {
                    $bloque_duracion = ($fin_turno - $hora) / 3600;
                    $proxima_hora = $fin_turno;
                }

                if ($bloque_duracion < $min_consecutive_hours) {
                    continue;
                }

                if (asignarBloqueHorario($pdo, $subject, $group, $dia, $hora, $proxima_hora, $tipo_espacio, $mensajes_error)) {
                    $subject['remaining_hours'] -= $bloque_duracion;
                    $horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] += $bloque_duracion;
                    $hora += $bloque_duracion * 3600;
                    $asignado = true;
                    break;
                }
            }

            if (!$asignado) {
                $hora += 3600;
            }
        }
    }
}

// 9. Función para verificar si todas las materias han sido asignadas
function todasMateriasAsignadas($subjects)
{
    foreach ($subjects as $subject) {
        if ($subject['remaining_hours'] > 0) {
            return false;
        }
    }
    return true;
}

// 10. Iterar sobre cada grupo y asignar materias
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
        // Obtener y preparar las materias del grupo
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

        // Asignar materias en la semana
        distribuirMateriasEnSemana($pdo, $group, $subjects, $horario_turno, $dias_turno, $mensajes_error, $horarios_disponibles);

        // Verificar si todas las materias han sido asignadas
        if (todasMateriasAsignadas($subjects)) {
            error_log("Todas las materias asignadas para el grupo ID: {$group['group_id']}.");
        } else {
            foreach ($subjects as $subject) {
                if ($subject['remaining_hours'] > 0) {
                    $mensajes_error[] = "No se pudieron asignar todas las horas para la materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) del grupo ID: {$group['group_id']}. Horas restantes: {$subject['remaining_hours']}.";
                    error_log("Materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) del grupo ID: {$group['group_id']} tiene {$subject['remaining_hours']} horas restantes.");
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Error durante la asignación para el grupo ID: {$group['group_id']}: " . $e->getMessage());
        $mensajes_error[] = "Error durante la asignación para el grupo ID: {$group['group_id']}.";
    }
}

// 11. Configurar mensajes de sesión según el resultado
if (!empty($mensajes_error)) {
    $_SESSION['mensaje'] = "Algunas materias no pudieron ser asignadas correctamente.";
    $_SESSION['icono'] = "error";
    $_SESSION['detalles_error'] = $mensajes_error;
} else {
    $_SESSION['mensaje'] = "Todas las materias fueron asignadas exitosamente.";
    $_SESSION['icono'] = "success";
}

// 12. Redireccionar al usuario
header('Location:' . APP_URL . "/admin/horarios_grupos/");
exit();
?>
