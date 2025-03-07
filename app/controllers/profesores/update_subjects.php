<?php
require_once '../../../app/registro_eventos.php';
require_once '../../../app/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ----------------------------------------------------------------
// INCLUIMOS LA INFORMACIÓN NECESARIA PARA SABER CÓMO ES CADA TURNO,
// QUÉ DÍAS SE USAN, ETC. (Antes, lo tenías en horarios_disponibles.php)
// ----------------------------------------------------------------
require_once '../../../app/controllers/horarios_grupos/horarios_disponibles.php';

// ----------------------------------------------------------------
// OBTENER DATOS DEL POST
// ----------------------------------------------------------------
$teacher_id  = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;
$materia_ids = isset($_POST['materias_asignadas']) ? $_POST['materias_asignadas'] : [];
$grupo_ids   = isset($_POST['grupos_asignados']) ? array_filter($_POST['grupos_asignados'], 'is_numeric') : [];
$fechaHora   = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    if (empty($grupo_ids)) {
        throw new Exception("Debe seleccionar al menos un grupo para asignar materias.");
    }

    // ----------------------------------------------------------------
    // Definimos funciones
    // ----------------------------------------------------------------

    function obtenerTurnoDelGrupo($pdo, $group_id) {
        $sql = "SELECT turn_id FROM `groups` WHERE group_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$group_id]);
        $turn_id = $stmt->fetchColumn();

        $turn_id_to_turno = [
            1 => 'MATUTINO',
            2 => 'VESPERTINO',
            3 => 'MIXTO',
            4 => 'ZINAPÉCUARO',
            5 => 'ENFERMERIA',
            6 => 'MATUTINO AVANZADO',
            7 => 'VESPERTINO AVANZADO',
        ];

        return $turn_id_to_turno[$turn_id] ?? 'MATUTINO';
    }

    function asignarBloqueHorario($pdo, $teacher_id, $subject_id, $group_id, $classroom_id,
                                  $dia, $start_timestamp, $end_timestamp, &$errores) {

        $start_time = date('H:i:s', $start_timestamp);
        $end_time   = date('H:i:s', $end_timestamp);

        // Verificar conflicto con el grupo
        $check_group = $pdo->prepare("
            SELECT COUNT(*) FROM schedule_assignments
            WHERE group_id = :group_id
              AND schedule_day = :dia
              AND (start_time < :et AND end_time > :st)
        ");
        $check_group->execute([
            ':group_id' => $group_id,
            ':dia'      => $dia,
            ':st'       => $start_time,
            ':et'       => $end_time
        ]);
        if ($check_group->fetchColumn() > 0) {
            return false; // Hay conflicto con el grupo
        }

        // Verificar conflicto con el profesor
        if ($teacher_id) {
            $check_teacher = $pdo->prepare("
                SELECT COUNT(*) FROM schedule_assignments
                WHERE teacher_id = :teacher_id
                  AND schedule_day = :dia
                  AND (start_time < :et AND end_time > :st)
            ");
            $check_teacher->execute([
                ':teacher_id' => $teacher_id,
                ':dia'        => $dia,
                ':st'         => $start_time,
                ':et'         => $end_time
            ]);
            if ($check_teacher->fetchColumn() > 0) {
                return false;
            }
        }

        // Insertar
        $insert = $pdo->prepare("
            INSERT INTO schedule_assignments
                (subject_id, group_id, teacher_id, classroom_id, schedule_day,
                 start_time, end_time, estado, fyh_creacion, tipo_espacio)
            VALUES
                (:subj, :gr, :teach, :classroom, :dia, :st, :et, 'activo', NOW(), 'Aula')
        ");
        $insert->execute([
            ':subj'      => $subject_id,
            ':gr'        => $group_id,
            ':teach'     => $teacher_id,
            ':classroom' => $classroom_id,
            ':dia'       => $dia,
            ':st'        => $start_time,
            ':et'        => $end_time
        ]);

        return true;
    }

    // CAMBIO NOMBRE: 
    // Esta función la llamaremos "asignarMateriaAHorarioRoundRobin" para diferenciarla
    // de la antigua. Y luego en el bucle la invocamos con este nuevo nombre.
    function asignarMateriaAHorarioRoundRobin($pdo, $teacher_id, $subject_id, $group_id, $weekly_hours,
                                    &$errores, $horarios_disponibles, $dias_semana) {

        $query = "SELECT classroom_assigned, turn_id FROM `groups` WHERE group_id = ?";
        $stmt  = $pdo->prepare($query);
        $stmt->execute([$group_id]);
        $group_info = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$group_info) {
            $errores[] = "No se encontró el grupo ID: $group_id.";
            return;
        }
        $classroom_id = $group_info['classroom_assigned'] ?? null;

        $turn_id_to_turno = [
            1 => 'MATUTINO',
            2 => 'VESPERTINO',
            3 => 'MIXTO',
            4 => 'ZINAPÉCUARO',
            5 => 'ENFERMERIA',
            6 => 'MATUTINO AVANZADO',
            7 => 'VESPERTINO AVANZADO',
        ];
        $turno_id = $group_info['turn_id'];
        $turno    = $turn_id_to_turno[$turno_id] ?? 'MATUTINO';

        if (!isset($dias_semana[$turno])) {
            $errores[] = "No hay días configurados para el turno: $turno. (Grupo $group_id)";
            return;
        }
        $dias_del_turno = $dias_semana[$turno];
        if (!isset($horarios_disponibles[$turno])) {
            $errores[] = "No hay horarios_disponibles para el turno: $turno. (Grupo $group_id)";
            return;
        }

        $remaining = $weekly_hours; 
        $diasCount = count($dias_del_turno);
        $currentDayIndex = 0;

        // Reglas extra, si quieres
        //$max_consecutive = 2;
        $ciclosSinAsignar = 0;

        while ($remaining > 0) {
            $dia = $dias_del_turno[$currentDayIndex];
            
            if (!isset($horarios_disponibles[$turno][$dia])) {
                // Pasamos al siguiente día
                $currentDayIndex = ($currentDayIndex + 1) % $diasCount;
                continue;
            }

            $start_str = $horarios_disponibles[$turno][$dia]['start'];
            $end_str   = $horarios_disponibles[$turno][$dia]['end'];
            $inicio = strtotime($start_str);
            $fin    = strtotime($end_str);

            $asignado = false;
            $hora_actual = $inicio;
            while ($hora_actual + 3600 <= $fin) {
                $ok = asignarBloqueHorario(
                    $pdo,
                    $teacher_id,
                    $subject_id,
                    $group_id,
                    $classroom_id,
                    $dia,
                    $hora_actual,
                    $hora_actual + 3600,
                    $errores
                );
                if ($ok) {
                    $remaining--;
                    $asignado = true;
                    break;  // dejamos de buscar más huecos en este día
                }
                $hora_actual += 3600;
            }

            if (!$asignado) {
                $ciclosSinAsignar++;
                // si quieres un corte de seguridad:
                if ($ciclosSinAsignar >= $diasCount * 3) {
                    // 3 vueltas completas sin asignar => no hay huecos
                    $errores[] = "No se puede asignar más horas a la materia $subject_id (faltan $remaining).";
                    break;
                }
            } else {
                $ciclosSinAsignar = 0;
            }

            // Siguiente día (cíclico)
            $currentDayIndex = ($currentDayIndex + 1) % $diasCount;
        }

        if ($remaining > 0) {
            $errores[] = "No se pudo asignar $remaining horas (ROUND-ROBIN) a la materia $subject_id (Grupo $group_id).";
        }
    }

    // --------------------------------------------------------------------
    // PROCESAMOS CADA (grupo, materia) SELECCIONADO
    // --------------------------------------------------------------------
    $lista_ids = implode(',', array_map('intval', $materia_ids)); 
    $sql_subjects = "SELECT subject_id, weekly_hours FROM subjects WHERE subject_id IN ($lista_ids)";
    $stmt_subjs   = $pdo->query($sql_subjects);
    $subjects_data = $stmt_subjs->fetchAll(PDO::FETCH_ASSOC);

    $map_hours = [];
    foreach ($subjects_data as $sd) {
        $map_hours[$sd['subject_id']] = $sd['weekly_hours'];
    }

    $errores = [];

    foreach ($grupo_ids as $grupo_id) {
        foreach ($materia_ids as $materia_id) {
            $verif = $pdo->prepare("
                SELECT COUNT(*) FROM teacher_subjects
                WHERE teacher_id = ? AND subject_id = ? AND group_id = ?
            ");
            $verif->execute([$teacher_id, $materia_id, $grupo_id]);
            $existe = $verif->fetchColumn();

            if (!$existe) {
                $ins = $pdo->prepare("
                    INSERT INTO teacher_subjects (teacher_id, subject_id, group_id, fyh_creacion, fyh_actualizacion)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $ins->execute([$teacher_id, $materia_id, $grupo_id, $fechaHora, $fechaHora]);
            }

            $weekly_hours = isset($map_hours[$materia_id]) ? (int)$map_hours[$materia_id] : 0;
            if ($weekly_hours <= 0) {
                $errores[] = "La materia $materia_id no tiene weekly_hours > 0.";
                continue;
            }

            // CAMBIO NOMBRE: Llamamos a la nueva función RoundRobin
            asignarMateriaAHorarioRoundRobin(
                $pdo,
                $teacher_id,
                $materia_id,
                $grupo_id,
                $weekly_hours,
                $errores,
                $horarios_disponibles,
                $dias_semana
            );
        }
    }

    // Actualizar las horas totales del profesor
    $sentencia_horas_totales = $pdo->prepare("
        SELECT SUM(s.weekly_hours) AS total_hours
        FROM teacher_subjects ts
        JOIN subjects s ON ts.subject_id = s.subject_id
        WHERE ts.teacher_id = ?
    ");
    $sentencia_horas_totales->execute([$teacher_id]);
    $total_hours = (int) $sentencia_horas_totales->fetchColumn();

    $sentencia_actualizar_horas = $pdo->prepare("
        UPDATE teachers SET hours = ?, fyh_actualizacion = ? WHERE teacher_id = ?
    ");
    $sentencia_actualizar_horas->execute([$total_hours, $fechaHora, $teacher_id]);

    if (!empty($errores)) {
        // throw new Exception(implode("\n", $errores));
    }

    $pdo->commit();

    // Registrar evento
    $usuario_email = $_SESSION['sesion_email'] ?? 'desconocido';
    $accion        = 'Asignación de materias + horarios RoundRobin';
    $descripcion   = "Se asignaron materias al profesor con ID $teacher_id: " . implode(', ', $materia_ids);
    registrarEvento($pdo, $usuario_email, $accion, $descripcion);

    $_SESSION['mensaje'] = "Se han asignado las materias y generado los horarios en modo RoundRobin.";
    $_SESSION['icono']   = "success";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['icono']   = "error";
    error_log("Error: " . $e->getMessage());
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
}
