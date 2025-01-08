<?php
session_start();

ob_start();

include_once('../../../app/config.php');

// Configuración de errores y límites de tiempo/memoria
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

set_time_limit(200);
ini_set('memory_limit', '512M');
error_log("Límite de memoria inicial: " . ini_get('memory_limit'));

// 2. Restaurar asignaciones desde manual_schedule_assignments
function restaurarAsignaciones($pdo)
{
    try {
        error_log("Iniciando la restauración de todas las asignaciones...");

        $sql_restore = "
            INSERT INTO schedule_assignments 
                (subject_id, group_id, teacher_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion, tipo_espacio, lab_id)
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
                m.tipo_espacio,
                l.lab_id  -- Usar el lab_id válido de labs
            FROM manual_schedule_assignments m
            LEFT JOIN teachers t ON m.teacher_id = t.teacher_id
            LEFT JOIN classrooms c ON m.classroom_id = c.classroom_id
            LEFT JOIN labs l ON m.lab1_assigned = l.lab_id  -- Asegura que lab_id existe
            WHERE NOT EXISTS (
                SELECT 1 FROM schedule_assignments sa
                WHERE sa.group_id = m.group_id
                  AND sa.schedule_day = m.schedule_day
                  AND sa.start_time = m.start_time
            )
        ";

        error_log("Consulta de restauración ejecutada: $sql_restore");

        $stmt = $pdo->prepare($sql_restore);
        $stmt->execute();
        $rows_affected = $stmt->rowCount();

        if ($rows_affected > 0) {
            error_log("Todas las asignaciones restauradas exitosamente: $rows_affected filas insertadas.");
        } else {
            error_log("No se encontraron asignaciones nuevas para restaurar o ya existen.");
        }
    } catch (PDOException $e) {
        error_log("Error al restaurar asignaciones: " . $e->getMessage());
        echo "Error al restaurar asignaciones: " . $e->getMessage();
        exit();
    }
}

restaurarAsignaciones($pdo);

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

// Si algunos turnos no quieres que se asignen, los pones aquí
$excluded_turnos = ['ZINAPÉCUARO'];

// 5. Filtrar grupos excluyendo ciertos turnos
$groups = array_filter($groups, function ($group) use ($turn_id_to_turno, $excluded_turnos) {
    $turno_id = $group['turn_id'];
    $turno    = $turn_id_to_turno[$turno_id] ?? 'MATUTINO';

    $turno_lower           = mb_strtolower($turno, 'UTF-8');
    $excluded_turnos_lower = array_map('mb_strtolower', $excluded_turnos);

    $turno_normalized     = remove_accents($turno_lower);
    $excluded_normalized  = array_map('remove_accents', $excluded_turnos_lower);

    return !in_array($turno_normalized, $excluded_normalized);
});

$groups = array_values($groups);
error_log("Grupos después del filtrado: " . count($groups));

// 6. Incluir materias por grupo y calcular horas restantes
//    Esto llena $subjects_by_group según tu lógica interna.
include('../../../app/controllers/grupos/materias_grupos.php');

// 7. Función para asignar bloques de horario (Sólo Aula)
function asignarBloqueHorario($pdo, $subject, $group, $dia, $start_time, $end_time, &$mensajes_error)
{
    $tipo_espacio = 'Aula';
    error_log("Intentando asignar materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) al grupo ID: {$group['group_id']} en $dia de " 
        . date('H:i:s', $start_time) . " a " . date('H:i:s', $end_time) . " en $tipo_espacio.");

    if (!isset($subject['subject_id'])) {
        error_log("Error: 'subject_id' no está definido.");
        $mensajes_error[] = "Error en la materia con ID desconocido para el grupo ID: {$group['group_id']}.";
        return false;
    }

    $formatted_start_time = date('H:i:s', $start_time);
    $formatted_end_time   = date('H:i:s', $end_time);

    $espacio_id = $group['classroom_assigned'];
    $teacher_id = $subject['teacher_id'] ?? null;

    // Verificar disponibilidad del grupo
    $check_group_sql = "SELECT COUNT(*) FROM schedule_assignments 
        WHERE group_id = :group_id 
          AND schedule_day = :schedule_day 
          AND (
              (start_time < :end_time AND end_time > :start_time)
          )";
    $check_group_params = [
        ':group_id'     => $group['group_id'],
        ':schedule_day' => $dia,
        ':start_time'   => $formatted_start_time,
        ':end_time'     => $formatted_end_time
    ];

    $stmt_group = $pdo->prepare($check_group_sql);
    $stmt_group->execute($check_group_params);
    if ($stmt_group->fetchColumn() > 0) {
        error_log("Grupo ID: {$group['group_id']} ya tiene una materia asignada en $dia de $formatted_start_time a $formatted_end_time.");
        return false;
    }

    // Verificar disponibilidad del profesor (si hay profesor asignado)
    if ($teacher_id) {
        $check_teacher_sql = "SELECT COUNT(*) FROM schedule_assignments 
            WHERE teacher_id = :teacher_id
              AND schedule_day = :schedule_day 
              AND (
                  (start_time < :end_time AND end_time > :start_time)
              )";
        $check_teacher_params = [
            ':teacher_id'   => $teacher_id,
            ':schedule_day' => $dia,
            ':start_time'   => $formatted_start_time,
            ':end_time'     => $formatted_end_time
        ];

        $stmt_teacher = $pdo->prepare($check_teacher_sql);
        $stmt_teacher->execute($check_teacher_params);
        if ($stmt_teacher->fetchColumn() > 0) {
            error_log("Profesor ID: $teacher_id no disponible en $dia de $formatted_start_time a $formatted_end_time.");
            return false;
        }
    }

    // Insertar la asignación
    $sql_insert = "INSERT INTO schedule_assignments 
                   (subject_id, group_id, teacher_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion, tipo_espacio)
                   VALUES (:s, :g, :t, :c, :d, :st, :et, 'activo', NOW(), :esp)";
    $stmt_insert = $pdo->prepare($sql_insert);

    try {
        $stmt_insert->execute([
            ':s'   => $subject['subject_id'],
            ':g'   => $group['group_id'],
            ':t'   => $teacher_id,
            ':c'   => $espacio_id,
            ':d'   => $dia,
            ':st'  => $formatted_start_time,
            ':et'  => $formatted_end_time,
            ':esp' => $tipo_espacio
        ]);

        error_log("Materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) asignada al grupo ID: {$group['group_id']} "
                  . "en $dia de $formatted_start_time a $formatted_end_time en $tipo_espacio.");
        return true;
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { // Violación de integridad (ej. duplicado)
            if (strpos($e->getMessage(), 'unique_schedule') !== false) {
                error_log("Duplicado detectado: Grupo ID: {$group['group_id']}, Día: $dia, Hora: $formatted_start_time.");
                return false;
            }
        }
        error_log("Error al insertar asignación: " . $e->getMessage());
        $mensajes_error[] = "Error al asignar materia '{$subject['subject_name']}' "
                          . "(ID: {$subject['subject_id']}) al grupo ID: {$group['group_id']}.";
        return false;
    }
}

// 8. Función para distribuir materias en la semana
function distribuirMateriasEnSemana($pdo, $group, &$subjects, $horario_turno, $dias_turno, &$mensajes_error, $horarios_disponibles)
{
    $grupo_turno          = $horario_turno;
    $dias_sin_restriccion = ['viernes'];

    if (!isset($horarios_disponibles[$grupo_turno])) {
        error_log("Error: Horario de turno '$grupo_turno' no encontrado para el grupo ID: {$group['group_id']}.");
        $mensajes_error[] = "Horario de turno '$grupo_turno' no encontrado para el grupo ID: {$group['group_id']}.";
        return;
    }

    // 1) ORDENAR MATERIAS POR PRIORIDAD:
    //    a) Primero las que tengan 'clasificacion = PA'
    //    b) Luego las que SÍ tienen profesor (pero no son PA)
    //    c) Luego las que NO tienen profesor
    //    d) Dentro de cada bloque anterior, mayor 'remaining_hours' primero.

    usort($subjects, function ($a, $b) {
        // 1) Revisamos si alguno es PA
        $aIsPA = ($a['clasificacion'] === 'PA') ? 1 : 0;
        $bIsPA = ($b['clasificacion'] === 'PA') ? 1 : 0;
        if ($aIsPA !== $bIsPA) {
            // Quiero que PA vaya primero, es decir, si aIsPA=1 y bIsPA=0, a va primero => devolvemos -1
            return $bIsPA - $aIsPA; 
        }

        // 2) Ambos son PA o ninguno => pasamos a la lógica de "tiene profesor vs no tiene"
        $aHasTeacher = !empty($a['teacher_id']);
        $bHasTeacher = !empty($b['teacher_id']);

        if ($aHasTeacher && !$bHasTeacher) {
            return -1; 
        }
        if (!$aHasTeacher && $bHasTeacher) {
            return 1;  
        }

        // 3) Si ambos tienen profesor o ambos no => mayor remaining_hours primero
        return $b['remaining_hours'] - $a['remaining_hours'];
    });

    // ------------------------------------------------
    // PRIMERA PASADA: Asignar dentro del horario disponible
    // ------------------------------------------------
    foreach ($dias_turno as $dia) {
        if (!isset($horarios_disponibles[$grupo_turno][$dia])) {
            continue;
        }
        $start_time_str = $horarios_disponibles[$grupo_turno][$dia]['start'];
        $end_time_str   = $horarios_disponibles[$grupo_turno][$dia]['end'];
        $inicio_turno   = strtotime($start_time_str);
        $fin_turno      = strtotime($end_time_str);

        // Materia por materia
        foreach ($subjects as &$subject) {
            if ($subject['remaining_hours'] <= 0) {
                continue;
            }

            $hora = $inicio_turno;
            $consecutivasActuales = 0;

            while ($subject['remaining_hours'] > 0 && ($hora + 3600) <= $fin_turno) {
                $diaLower         = mb_strtolower($dia, 'UTF-8');
                $esSinRestriccion = in_array($diaLower, $dias_sin_restriccion);

                // Control de horas consecutivas (solo restringe si no es día sin restricción)
                if (!$esSinRestriccion) {
                    $max_consecutive_hours = $subject['max_consecutive_hours'] ?? 2;
                    if ($consecutivasActuales >= $max_consecutive_hours) {
                        break; 
                    }
                }

                // Intentar asignar
                if (asignarBloqueHorario($pdo, $subject, $group, $dia, $hora, $hora + 3600, $mensajes_error)) {
                    // Decrementa 1 hora
                    $subject['remaining_hours']--;
                    // Si no es viernes (o día sin restricción), incrementa contador de horas consecutivas
                    if (!$esSinRestriccion) {
                        $consecutivasActuales++;
                    }
                    // Avanza una hora
                    $hora += 3600;
                } else {
                    // Si no pudo asignar, simplemente avanzamos la hora e intentamos en la siguiente
                    $hora += 3600;
                }
            }
        }
    }

    // ------------------------------------------------
    // SEGUNDA PASADA: Asignar horas restantes en una hora extra (fuera del horario normal)
    // ------------------------------------------------
    foreach ($subjects as &$subject) {
        while ($subject['remaining_hours'] > 0) {
            $asignado = false;
            foreach ($dias_turno as $dia) {
                if (!isset($horarios_disponibles[$grupo_turno][$dia])) {
                    continue;
                }
                $start_time_str = $horarios_disponibles[$grupo_turno][$dia]['start'];
                $end_time_str   = $horarios_disponibles[$grupo_turno][$dia]['end'];
                $inicio_turno   = strtotime($start_time_str);
                $fin_turno      = strtotime($end_time_str);

                // Intentar asignar dentro del horario normal primero
                $hora = $inicio_turno;
                while (($hora + 3600) <= $fin_turno) {
                    if (asignarBloqueHorario($pdo, $subject, $group, $dia, $hora, $hora + 3600, $mensajes_error)) {
                        $subject['remaining_hours']--;
                        $asignado = true;
                        break 2; // Salimos de ambos bucles
                    }
                    $hora += 3600;
                }

                // Si no se pudo asignar dentro del horario, intentamos una hora adicional DESPUÉS
                if (!$asignado) {
                    $hora_extra     = $fin_turno;
                    $hora_extra_end = $hora_extra + 3600;

                    // Verificar que esta hora extra no sobrepase algún límite (solo 1 hora)
                    if (asignarBloqueHorario($pdo, $subject, $group, $dia, $hora_extra, $hora_extra_end, $mensajes_error)) {
                        $subject['remaining_hours']--;
                        $asignado = true;
                        break; 
                    }
                }
            }

            if (!$asignado) {
                // No se pudo asignar la hora restante
                error_log(
                    "No se pudo asignar la hora restante para la materia '{$subject['subject_name']}' ".
                    "(ID: {$subject['subject_id']}) del grupo ID: {$group['group_id']}."
                );
                $mensajes_error[] = 
                    "No se pudo asignar una hora restante para la materia ".
                    "'{$subject['subject_name']}' (ID: {$subject['subject_id']}) ".
                    "del grupo ID: {$group['group_id']}.";
                break;
            }
        }
    }

}

// 9. Verificar si todas las materias han sido asignadas
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
    $turno    = $turn_id_to_turno[$turno_id] ?? 'MATUTINO';

    if (!isset($horarios_disponibles[$turno])) {
        error_log("Horario de turno '$turno' no encontrado para el grupo ID: {$group['group_id']}");
        $mensajes_error[] = "Horario de turno '$turno' no encontrado para el grupo ID: {$group['group_id']}.";
        continue;
    }

    // Aquí asumo que $dias_semana está definido en horarios_disponibles.php o en tu entorno
    $horario_turno = $turno;
    $dias_turno    = $dias_semana[$turno] ?? [];

    if (empty($dias_turno)) {
        error_log("No hay días definidos para el turno '$turno' del grupo ID: {$group['group_id']}");
        $mensajes_error[] = "No hay días definidos para el turno '$turno' del grupo ID: {$group['group_id']}.";
        continue;
    }

    // Verificamos si hay materias para el grupo
    if (!isset($subjects_by_group[$group['group_id']])) {
        error_log("No se encontraron materias para el grupo ID: {$group['group_id']}");
        $mensajes_error[] = "No se encontraron materias para el grupo ID: {$group['group_id']}.";
        continue;
    }

    try {
        // 1) Crear un arreglo con la información de cada materia
        //    y, ADEMÁS, le asignamos la clasificación del profesor.
        $subjects = array_map(function ($subject) use ($pdo) {
            // Obtener la clasificación del profesor (si existe)
            $clasificacion = null;
            if (!empty($subject['teacher_id'])) {
                $stmt = $pdo->prepare("SELECT clasificacion FROM teachers WHERE teacher_id = :tid");
                $stmt->execute([':tid' => $subject['teacher_id']]);
                $clasificacion = $stmt->fetchColumn();
            }

            return [
                'subject_id'            => $subject['subject_id'],
                'subject_name'          => $subject['subject_name'],
                'teacher_id'            => $subject['teacher_id'],
                'clasificacion'         => $clasificacion,
                'remaining_hours'       => $subject['remaining_hours'],
                'type'                  => $subject['type'],
                'max_consecutive_hours' => $subject['max_consecutive_hours'],
                'min_consecutive_hours' => $subject['min_consecutive_hours'],
            ];
        }, $subjects_by_group[$group['group_id']]);

        // 2) Asignar materias
        distribuirMateriasEnSemana($pdo, $group, $subjects, $horario_turno, $dias_turno, $mensajes_error, $horarios_disponibles);

        // 3) Verificar si todas las materias quedaron asignadas
        if (todasMateriasAsignadas($subjects)) {
            error_log("Todas las materias asignadas para el grupo ID: {$group['group_id']}.");
        } else {
            foreach ($subjects as $subject) {
                if ($subject['remaining_hours'] > 0) {
                    $mensajes_error[] = 
                        "No se pudieron asignar todas las horas para la materia ".
                        "'{$subject['subject_name']}' (ID: {$subject['subject_id']}) ".
                        "del grupo ID: {$group['group_id']}. ".
                        "Horas restantes: {$subject['remaining_hours']}.";
                    error_log(
                        "Materia '{$subject['subject_name']}' (ID: {$subject['subject_id']}) ".
                        "del grupo ID: {$group['group_id']} ".
                        "tiene {$subject['remaining_hours']} horas restantes."
                    );
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
    $_SESSION['icono']   = "error";
    $_SESSION['detalles_error'] = $mensajes_error;
} else {
    $_SESSION['mensaje'] = "Todas las materias fueron asignadas exitosamente.";
    $_SESSION['icono']   = "success";
}

// Finalizar el buffer
ob_end_clean();

// 12. Redireccionar al usuario
header('Location:' . APP_URL . "/admin/horarios_grupos/");
exit();
?>
