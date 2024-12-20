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
            WHERE m.tipo_espacio = 'Laboratorio'
        ";

        // Registrar la consulta para depuración
        error_log("Consulta de restauración ejecutada: $sql_restore");

        // Preparar y ejecutar la consulta para obtener el número de filas afectadas
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
        // **Solo para desarrollo:** Muestra el error en la página
        echo "Error al restaurar asignaciones de laboratorio: " . $e->getMessage();
        exit();

        /*
        // **Para producción:** Utiliza redirección y mensajes de sesión
        $_SESSION['mensaje'] = "Error al restaurar asignaciones de laboratorio.";
        $_SESSION['icono'] = "error";
        header('Location:' . APP_URL . "/admin/horarios_grupos/");
        exit();
        */
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

// 8. Función para distribuir materias en la semana con segunda pasada
function distribuirMateriasEnSemana($pdo, $group, &$subjects, $horario_turno, $dias_turno, &$mensajes_error, $horarios_disponibles)
{
    $grupo_turno = $horario_turno;
    $dias_sin_restriccion = ['jueves', 'viernes']; // Días sin restricción

    if (!isset($horarios_disponibles[$grupo_turno])) {
        error_log("Error: Horario de turno '$grupo_turno' no encontrado para el grupo ID: {$group['group_id']}");
        $mensajes_error[] = "Horario de turno '$grupo_turno' no encontrado para el grupo ID: {$group['group_id']}.";
        return;
    }

    // Ordenar las materias por horas restantes descendente
    usort($subjects, function ($a, $b) {
        return $b['remaining_hours'] - $a['remaining_hours'];
    });

    // Inicializar seguimiento de asignaciones por materia por día
    $horas_asignadas_por_materia_dia = [];

    // Primera Pasada: Asignación Respetando Restricciones
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
            $asignado = false;

            foreach ($subjects as &$subject) {
                if ($subject['remaining_hours'] <= 0) {
                    continue;
                }

                // Verificar si el día está sin restricción
                $sin_restriccion = in_array(strtolower($dia), $dias_sin_restriccion);

                // Determinar las horas que pueden ser asignadas en este bloque
                if ($sin_restriccion) {
                    // En días sin restricción, intentar asignar bloques de hasta 'max_consecutive_hours'
                    $bloque_duracion = min($subject['max_consecutive_hours'], $subject['remaining_hours']);
                } else {
                    // En días con restricción, respetar 'max_consecutive_hours'
                    $max_consecutive_hours = $subject['max_consecutive_hours'];
                    $min_consecutive_hours = $subject['min_consecutive_hours'];

                    // Obtener las horas ya asignadas para esta materia en este día
                    if (!isset($horas_asignadas_por_materia_dia[$dia][$subject['subject_id']])) {
                        $horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] = 0;
                    }

                    $bloque_duracion = min($max_consecutive_hours - $horas_asignadas_por_materia_dia[$dia][$subject['subject_id']], $subject['remaining_hours']);

                    if ($bloque_duracion < $min_consecutive_hours) {
                        continue; // No cumple con las horas mínimas
                    }
                }

                // Verificar que el bloque no exceda el horario del día
                $proxima_hora = $hora + ($bloque_duracion * 3600);
                if ($proxima_hora > $fin_turno) {
                    $bloque_duracion = ($fin_turno - $hora) / 3600;
                    if ($bloque_duracion < 1) {
                        continue; // No hay suficiente tiempo para asignar al menos una hora
                    }
                    $proxima_hora = $fin_turno;
                }

                // Intentar asignar el bloque
                if (asignarBloqueHorario($pdo, $subject, $group, $dia, $hora, $proxima_hora, $mensajes_error)) {
                    $subject['remaining_hours'] -= $bloque_duracion;
                    if (!$sin_restriccion) {
                        $horas_asignadas_por_materia_dia[$dia][$subject['subject_id']] += $bloque_duracion;
                    }
                    $hora += $bloque_duracion * 3600;
                    $asignado = true;
                    break; // Salir del bucle de materias para avanzar en el horario
                }
            }

            if (!$asignado) {
                // No se pudo asignar ninguna materia en este bloque, avanzar una hora
                $hora += 3600;
            }
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
                        continue; // Espacio no disponible
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
                        continue; // Grupo no disponible
                    }

                    // Determinar la duración que se puede asignar (hasta 1 hora en segunda pasada)
                    $bloque_duracion = 1; // Asignar una hora a la vez en la segunda pasada

                    // Intentar asignar el bloque
                    if (asignarBloqueHorario($pdo, $subject, $group, $dia, $hora, $hora + ($bloque_duracion * 3600), $mensajes_error)) {
                        $subject['remaining_hours'] -= $bloque_duracion;
                        $asignado = true;
                        break 2; // Salir de ambos bucles para intentar la siguiente hora restante
                    }

                    $hora += 3600;
                }
            }

            if (!$asignado) {
                // No se pudo asignar la hora restante en ningún lugar
                error_log("No se pudo asignar la hora restante para la materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) del grupo ID: {$group['group_id']}.");
                $mensajes_error[] = "No se pudo asignar una hora restante para la materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) del grupo ID: {$group['group_id']}.";
                break; // Salir del bucle while para evitar un bucle infinito
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

// 12. Redireccionar al usuario
header('Location:' . APP_URL . "/admin/horarios_grupos/");
exit();
?>
