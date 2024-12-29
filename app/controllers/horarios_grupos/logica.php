<?php
session_start();

ob_start();

include_once('../../../app/config.php');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
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

function restaurarAsignacionesLaboratorio($pdo)
{
    try {
        error_log("Iniciando la restauración de asignaciones de laboratorio...");

        $sql_restore = "
            INSERT INTO schedule_assignments 
                (subject_id, group_id, teacher_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion, tipo_espacio)
            SELECT 
                m.subject_id, 
                m.group_id, 
                t.teacher_id AS teacher_id,
                c.classroom_id AS classroom_id,
                m.schedule_day, 
                m.start_time, 
                m.end_time, 
                'activo', 
                NOW(), 
                'Laboratorio'
            FROM manual_schedule_assignments m
            LEFT JOIN teachers t ON m.teacher_id = t.teacher_id
            LEFT JOIN classrooms c ON m.classroom_id = c.classroom_id
            WHERE m.estado = 'activo'
        ";

        error_log("Consulta de restauración ejecutada: $sql_restore");

        $stmt = $pdo->prepare($sql_restore);
        $stmt->execute();
        $rows_affected = $stmt->rowCount();

        if ($rows_affected > 0) {
            error_log("Asignaciones de laboratorio restauradas exitosamente: $rows_affected filas insertadas.");
        } else {
            error_log("No se encontraron asignaciones de laboratorio para restaurar o ya existen.");
        }
    } catch (PDOException $e) {
        error_log("Error al restaurar asignaciones de laboratorio: " . $e->getMessage());
        echo "Error al restaurar asignaciones de laboratorio: " . $e->getMessage();
        exit();
    }
}


restaurarAsignacionesLaboratorio($pdo);

// 3. Incluir archivos necesarios
include('../../../app/controllers/horarios_grupos/horarios_disponibles.php');

$mensajes_error = [];

// Función para eliminar acentos y normalizar cadenas
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
    // 4. Obtener grupos activos
    $groups = $pdo->query("SELECT *, classroom_assigned, lab_assigned FROM `groups` WHERE estado = '1'")->fetchAll(PDO::FETCH_ASSOC);
    error_log("Grupos obtenidos: " . count($groups));
} catch (PDOException $e) {
    error_log("Error al obtener grupos: " . $e->getMessage());
    $_SESSION['mensaje'] = "Error al obtener grupos.";
    $_SESSION['icono'] = "error";
    header('Location:' . APP_URL . "/admin/horarios_grupos/");
    exit();
}

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

// 6. Incluir materias por grupo y calcular horas restantes
include('../../../app/controllers/grupos/materias_grupos.php'); // Este archivo ya está bien según el usuario

// 7. Función para asignar bloques de horario (Solo Aula)
function asignarBloqueHorario($pdo, $subject, $group, $dia, $start_time, $end_time, &$mensajes_error)
{
    $tipo_espacio = 'Aula';
    error_log("Intentando asignar materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) al grupo ID: {$group['group_id']} en $dia de " . date('H:i:s', $start_time) . " a " . date('H:i:s', $end_time) . " en $tipo_espacio.");

    if (!isset($subject['subject_id'])) {
        error_log("Error: 'subject_id' no está definido.");
        $mensajes_error[] = "Error en la materia con ID desconocido para el grupo ID: {$group['group_id']}.";
        return false;
    }

    $formatted_start_time = date('H:i:s', $start_time);
    $formatted_end_time = date('H:i:s', $end_time);

    $espacio_id = $group['classroom_assigned'];
    // Para 'Aula', asegurarse de que 'teacher_id' sea NULL
    $teacher_id = null;

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

    // Insertar la asignación
    $sql_insert = "INSERT INTO schedule_assignments 
                   (subject_id, group_id, teacher_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion, tipo_espacio)
                   VALUES (:subject_id, :group_id, :teacher_id, :classroom_id, :schedule_day, :start_time, :end_time, 'activo', NOW(), :tipo_espacio)";
    $stmt_insert = $pdo->prepare($sql_insert);

    try {
        $stmt_insert->execute([
            ':subject_id' => $subject['subject_id'],
            ':group_id' => $group['group_id'],
            ':teacher_id' => $teacher_id, // Siempre NULL para 'Aula'
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

// 8. Función para distribuir materias en la semana con segunda pasada
function distribuirMateriasEnSemana($pdo, $group, &$subjects, $horario_turno, $dias_turno, &$mensajes_error, $horarios_disponibles)
{
    $grupo_turno = $horario_turno;
    $dias_sin_restriccion = ['jueves', 'viernes'];

    if (!isset($horarios_disponibles[$grupo_turno])) {
        error_log("Error: Horario de turno '$grupo_turno' no encontrado para el grupo ID: {$group['group_id']}");
        $mensajes_error[] = "Horario de turno '$grupo_turno' no encontrado para el grupo ID: {$group['group_id']}.";
        return;
    }

    // Inicializar seguimiento de asignaciones por materia por día
    $horas_asignadas_por_materia_dia = [];

    // Ordenar las materias por *más* horas restantes primero para evitar que se queden sin asignar
    usort($subjects, function ($a, $b) {
        return $b['remaining_hours'] - $a['remaining_hours'];
    });

    // Asignar bloques de tiempo consecutivos
    foreach ($dias_turno as $dia) {
        if (!isset($horarios_disponibles[$grupo_turno][$dia])) {
            continue;
        }

        $start_time_str = $horarios_disponibles[$grupo_turno][$dia]['start'];
        $end_time_str = $horarios_disponibles[$grupo_turno][$dia]['end'];
        $inicio_turno = strtotime($start_time_str);
        $fin_turno = strtotime($end_time_str);

        $hora = $inicio_turno;

        while ($hora < $fin_turno) {
            foreach ($subjects as &$subject) {
                if ($subject['remaining_hours'] <= 0) {
                    continue;
                }

                // Verificar si el día está sin restricción
                $sin_restriccion = in_array(strtolower($dia), $dias_sin_restriccion);

                // Verificar si se puede asignar sin exceder el máximo de horas consecutivas
                $max_consecutive_hours = $subject['max_consecutive_hours'] ?? 2; // Valor por defecto
                $min_consecutive_hours = $subject['min_consecutive_hours'] ?? 1;

                if (!isset($horas_asignadas_por_materia_dia[$dia][$subject['subject_id']])) {
                    $horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] = 0;
                }

                if ($horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] >= $max_consecutive_hours) {
                    continue;
                }

                // Definir la duración del bloque (1 hora)
                $bloque_duracion = 1;

                // Verificar que el bloque no exceda el horario del día
                $proxima_hora = $hora + ($bloque_duracion * 3600);
                if ($proxima_hora > $fin_turno) {
                    continue;
                }

                // Intentar asignar el bloque
                if (asignarBloqueHorario($pdo, $subject, $group, $dia, $hora, $proxima_hora, $mensajes_error)) {
                    $subject['remaining_hours'] -= $bloque_duracion;
                    $horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] += $bloque_duracion;
                    $hora += $bloque_duracion * 3600;
                    break; // Avanzar al siguiente bloque de tiempo
                }
            }

            $hora += 3600; // Avanzar una hora si no se pudo asignar ninguna materia
        }
    }

    // Segunda Pasada: Asignar Horas Restantes en Cualquier Espacio Disponible
    foreach ($subjects as &$subject) {
        while ($subject['remaining_hours'] > 0) {
            $asignado = false;

            // Buscar cualquier día y hora disponible
            foreach ($dias_turno as $dia) {
                if (!isset($horarios_disponibles[$grupo_turno][$dia])) {
                    continue;
                }

                $start_time_str = $horarios_disponibles[$grupo_turno][$dia]['start'];
                $end_time_str = $horarios_disponibles[$grupo_turno][$dia]['end'];
                $inicio_turno = strtotime($start_time_str);
                $fin_turno = strtotime($end_time_str);

                $hora = $inicio_turno;

                while ($hora < $fin_turno) {
                    // Verificar si el bloque está libre
                    $formatted_start_time = date('H:i:s', $hora);
                    $formatted_end_time = date('H:i:s', $hora + 3600);

                    // Verificar disponibilidad del espacio
                    $check_availability_sql = "SELECT COUNT(*) FROM schedule_assignments 
                        WHERE 
                            (classroom_id = :espacio_id AND tipo_espacio = 'Aula')
                            AND schedule_day = :schedule_day 
                            AND (
                                (start_time < :end_time AND end_time > :start_time)
                            )";
                    $check_availability_params = [
                        ':espacio_id' => $group['classroom_assigned'],
                        ':schedule_day' => $dia,
                        ':start_time' => $formatted_start_time,
                        ':end_time' => $formatted_end_time
                    ];

                    $check_availability = $pdo->prepare($check_availability_sql);
                    $check_availability->execute($check_availability_params);

                    if ($check_availability->fetchColumn() > 0) {
                        $hora += 3600;
                        continue;
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
                        $hora += 3600;
                        continue;
                    }

                    // Definir la duración del bloque (1 hora)
                    $bloque_duracion = 1;

                    // Verificar que el bloque no exceda el horario del día
                    $proxima_hora = $hora + ($bloque_duracion * 3600);
                    if ($proxima_hora > $fin_turno) {
                        $hora += 3600;
                        continue;
                    }

                    // Intentar asignar el bloque
                    if (asignarBloqueHorario($pdo, $subject, $group, $dia, $hora, $proxima_hora, $mensajes_error)) {
                        $subject['remaining_hours'] -= $bloque_duracion;
                        $asignado = true;
                        break 2; // Salir de los dos bucles
                    }

                    $hora += 3600;
                }
            }

            if (!$asignado) {
                // No se pudo asignar la hora restante en ningún lugar
                error_log("No se pudo asignar la hora restante para la materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) del grupo ID: {$group['group_id']}.");
                $mensajes_error[] = "No se pudo asignar una hora restante para la materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) del grupo ID: {$group['group_id']}.";
                break;
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

        // Asignar materias en la semana con segunda pasada
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

// Finalizar el buffer de salida y limpiarlo
ob_end_clean();

// 12. Redireccionar al usuario
header('Location:' . APP_URL . "/admin/horarios_grupos/");
exit();
?>
