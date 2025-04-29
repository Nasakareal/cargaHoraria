<?php
require_once '../../../app/registro_eventos.php';
require_once '../../../app/config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../../app/controllers/horarios_grupos/horarios_disponibles.php';

/*--------------------------------------------------------------
  ↘ DISPONIBILIDAD DEL PROFESOR
----------------------------------------------------------------*/
function cargarDisponibilidadProfesor($pdo, $teacher_id)
{
    $sql = "SELECT day_of_week, start_time, end_time
            FROM teacher_availability
            WHERE teacher_id = ?";
    $st = $pdo->prepare($sql);
    $st->execute([$teacher_id]);

    $disp = [];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $d = $r['day_of_week'];
        if (!isset($disp[$d]))
            $disp[$d] = [];
        $disp[$d][] = ['start' => $r['start_time'], 'end' => $r['end_time']];
    }
    return $disp;
}

function profesorEstaDisponible($teacherAvailability, $diaEsp, $start, $end)
{
    if (!isset($teacherAvailability[$diaEsp]))
        return false;
    $bS = strtotime($start);
    $bE = strtotime($end);
    foreach ($teacherAvailability[$diaEsp] as $rng) {
        $rS = strtotime($rng['start']);
        $rE = strtotime($rng['end']);
        if ($bS >= $rS && $bE <= $rE)
            return true;
    }
    return false;
}

function teacherLibreEnHorario($pdo, $teacher_id, $diaEsp, $start_time, $end_time)
{
    if (!$teacher_id)
        return true;
    $sql = "SELECT COUNT(*) FROM schedule_assignments
            WHERE teacher_id = ? AND schedule_day = ?
              AND (start_time < ? AND end_time > ?)";
    $st = $pdo->prepare($sql);
    $st->execute([$teacher_id, $diaEsp, $end_time, $start_time]);
    return ($st->fetchColumn() == 0);
}

/*--------------------------------------------------------------
  ↘ REGLA: ¿ya existe esa materia ese día en manual_schedule…?
----------------------------------------------------------------*/
function existeAsignacionManualDia($pdo, $subject_id, $group_id, $diaEsp)
{
    $sql = "SELECT COUNT(*) FROM manual_schedule_assignments
            WHERE subject_id = ? AND group_id = ?
              AND schedule_day = ? AND estado = 'activo'";
    $st = $pdo->prepare($sql);
    $st->execute([$subject_id, $group_id, $diaEsp]);
    return ($st->fetchColumn() > 0);
}

/*--------------------------------------------------------------
  ↘ COPIAR BLOQUES MANUALES (LABORATORIO) A schedule_assignments
----------------------------------------------------------------*/
function copiarBloquesManualASchedule(
    $pdo,
    $teacher_id,
    $subject_id,
    $group_id,
    $teacherAvailability
) {
    /* bloques manuales activos */
    $sql = "SELECT schedule_day, start_time, end_time,
               classroom_id, lab1_assigned AS lab_id, tipo_espacio
        FROM manual_schedule_assignments
        WHERE subject_id = ? AND group_id = ? AND estado = 'activo'";

    $st = $pdo->prepare($sql);
    $st->execute([$subject_id, $group_id]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows)
        return 0;

    $horasCopiadas = 0;

    foreach ($rows as $r) {
        $dia = $r['schedule_day'];
        $s = $r['start_time'];
        $e = $r['end_time'];

        /* profesor disponible */
        if (!profesorEstaDisponible($teacherAvailability, $dia, $s, $e)) {
            return false; // alguna hora no puede cubrirse
        }
        if (!teacherLibreEnHorario($pdo, $teacher_id, $dia, $s, $e)) {
            return false; // choca con otro horario del profe
        }

        /* evitar duplicados exactos */
        $du = $pdo->prepare("SELECT 1 FROM schedule_assignments
                             WHERE subject_id = ? AND group_id = ?
                               AND schedule_day = ? AND start_time = ? AND end_time = ?
                               LIMIT 1");
        $du->execute([$subject_id, $group_id, $dia, $s, $e]);
        if ($du->fetchColumn()) {
            /* si ya existe, solo asegurar teacher_id */
            $up = $pdo->prepare("UPDATE schedule_assignments
                                 SET teacher_id = ?, fyh_actualizacion = NOW()
                                 WHERE subject_id = ? AND group_id = ?
                                   AND schedule_day = ? AND start_time = ? AND end_time = ?");
            $up->execute([$teacher_id, $subject_id, $group_id, $dia, $s, $e]);
        } else {
            /* insertar nuevo bloque copiando datos base */
            $ins = $pdo->prepare("INSERT INTO schedule_assignments
                (subject_id, group_id, teacher_id, classroom_id, lab_id,
                 schedule_day, start_time, end_time, estado, fyh_creacion, tipo_espacio)
                VALUES(?,?,?,?,?,?,?,?,?,NOW(),?)");
            $ins->execute([
                $subject_id,
                $group_id,
                $teacher_id,
                $r['classroom_id'],
                $r['lab_id'],
                $dia,
                $s,
                $e,
                'activo',
                $r['tipo_espacio'] ?: 'Laboratorio'
            ]);
        }

        $horasCopiadas += (strtotime($e) - strtotime($s)) / 3600;
    }

    return $horasCopiadas;
}

/*--------------------------------------------------------------
  ↘ BLOQUES EXISTENTES (SIN PROFESOR) EN schedule_assignments
----------------------------------------------------------------*/
function checarTodosBloquesExistentes($pdo, $teacher_id, $subject_id, $group_id, $teacherAvailability)
{
    $sql = "SELECT assignment_id, schedule_day, start_time, end_time
            FROM schedule_assignments
            WHERE subject_id = ? AND group_id = ?
              AND (teacher_id = 0 OR teacher_id IS NULL)
              AND estado = 'activo'
            ORDER BY schedule_day, start_time";
    $st = $pdo->prepare($sql);
    $st->execute([$subject_id, $group_id]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows)
        return 0;

    foreach ($rows as $r) {
        $dia = $r['schedule_day'];
        $stt = $r['start_time'];
        $ett = $r['end_time'];
        if (!profesorEstaDisponible($teacherAvailability, $dia, $stt, $ett))
            return false;
        if (!teacherLibreEnHorario($pdo, $teacher_id, $dia, $stt, $ett))
            return false;
    }
    return count($rows);
}

function asignarTodosBloquesExistentes($pdo, $teacher_id, $subject_id, $group_id)
{
    $sql = "UPDATE schedule_assignments
            SET teacher_id = ?, fyh_actualizacion = NOW()
            WHERE subject_id = ? AND group_id = ?
              AND (teacher_id = 0 OR teacher_id IS NULL)
              AND estado = 'activo'";
    $up = $pdo->prepare($sql);
    $up->execute([$teacher_id, $subject_id, $group_id]);
}

/*--------------------------------------------------------------
  ↘ INSERTAR UN BLOQUE DE 1 h (si hay hueco)
----------------------------------------------------------------*/
function asignarBloqueHorario(
    $pdo,
    $teacher_id,
    $subject_id,
    $group_id,
    $classroom_id,
    $diaEsp,
    $start_ts,
    $end_ts,
    $teacherAvailability
) {
    $s = date('H:i:s', $start_ts);
    $e = date('H:i:s', $end_ts);

    if (!profesorEstaDisponible($teacherAvailability, $diaEsp, $s, $e))
        return false;

    /* solape con cualquier materia del grupo */
    $q = $pdo->prepare("SELECT COUNT(*) FROM schedule_assignments
                         WHERE group_id = ? AND schedule_day = ?
                           AND (start_time < ? AND end_time > ?)");
    $q->execute([$group_id, $diaEsp, $e, $s]);
    if ($q->fetchColumn() > 0)
        return false;

    /* solape con horario del profesor */
    if ($teacher_id && !teacherLibreEnHorario($pdo, $teacher_id, $diaEsp, $s, $e))
        return false;

    $ins = $pdo->prepare("INSERT INTO schedule_assignments
        (subject_id, group_id, teacher_id, classroom_id, schedule_day,
         start_time, end_time, estado, fyh_creacion, tipo_espacio)
        VALUES(?,?,?,?,?,?,?,?,NOW(),'Aula')");
    $ins->execute([$subject_id, $group_id, $teacher_id, $classroom_id, $diaEsp, $s, $e, 'activo']);
    return true;
}

/*--------------------------------------------------------------
  ↘ FUNCIÓN PRINCIPAL  (por materia + grupo)
----------------------------------------------------------------*/
function asignarMateriaConBloquesYAparteSiSobra(
    $pdo,
    $teacher_id,
    $subject_id,
    $group_id,
    $weekly_hours,
    &$errores,
    $horarios_disponibles,
    $dias_semana,
    $teacherAvailability
) {
    /* datos del grupo: aula y turno */
    $ginfo = $pdo->prepare("SELECT classroom_assigned, turn_id
                            FROM `groups` WHERE group_id = ?");
    $ginfo->execute([$group_id]);
    $gi = $ginfo->fetch(PDO::FETCH_ASSOC);
    if (!$gi) {
        $errores[] = "No existe grupo $group_id";
        return false;
    }
    $classroom_id = $gi['classroom_assigned'] ?: null;

    $mapT = [
        1 => 'MATUTINO',
        2 => 'VESPERTINO',
        3 => 'MIXTO',
        4 => 'ZINAPÉCUARO',
        5 => 'ENFERMERIA',
        6 => 'MATUTINO AVANZADO',
        7 => 'VESPERTINO AVANZADO'
    ];
    $turno = $mapT[$gi['turn_id']] ?? 'MATUTINO';

    if (!isset($dias_semana[$turno]) || !isset($horarios_disponibles[$turno])) {
        $errores[] = "No hay configuración de días/horarios para turno $turno";
        return false;
    }

    /* 1️⃣  COPIAR BLOQUES MANUALES (laboratorio) */
    $hManual = copiarBloquesManualASchedule(
        $pdo,
        $teacher_id,
        $subject_id,
        $group_id,
        $teacherAvailability
    );
    if ($hManual === false) {
        $errores[] =
            "El profesor no puede cubrir los bloques manuales " .
            "de la materia $subject_id en el grupo $group_id.";
        return false;
    }
    $weekly_hours -= $hManual;
    if ($weekly_hours <= 0)
        return true;  // todo cubierto con manuales

    /* 2️⃣  BLOQUES YA EXISTENTES SIN PROFESOR */
    $cnt = checarTodosBloquesExistentes(
        $pdo,
        $teacher_id,
        $subject_id,
        $group_id,
        $teacherAvailability
    );
    if ($cnt === false) {
        $errores[] =
            "No se asignó la materia $subject_id al grupo $group_id: " .
            "el profesor no puede cubrir todos los bloques existentes";
        return false;
    }
    if ($cnt > 0) {
        if ($cnt > $weekly_hours) {
            $errores[] =
                "La materia $subject_id requiere $weekly_hours horas, " .
                "pero hay $cnt bloques existentes. Ajusta manualmente.";
            return false;
        }
        asignarTodosBloquesExistentes($pdo, $teacher_id, $subject_id, $group_id);
        $weekly_hours -= $cnt;
    }
    if ($weekly_hours <= 0)
        return true;   // ya quedó

    /* 3️⃣  ASIGNAR HUECOS RESTANTES */
    $diasDelTurno = $dias_semana[$turno];
    $dc = count($diasDelTurno);
    $i = 0;        // índice del día
    $ciclosSinHueco = 0;        // para romper si no hay huecos

    while ($weekly_hours > 0) {

        $dia = $diasDelTurno[$i];

        /* 3.a  evitar duplicar día si en manual ya hay la misma materia */
        if (existeAsignacionManualDia($pdo, $subject_id, $group_id, $dia)) {
            $i = ($i + 1) % $dc;
            $ciclosSinHueco++;
            if ($ciclosSinHueco >= $dc * 3) {
                $errores[] =
                    "No hay espacio para completar horas de la materia $subject_id " .
                    "en el grupo $group_id (regla: evitar mismo día).";
                return false;
            }
            continue;
        }

        /* 3.b  ¿Existe horario para ese día? */
        if (!isset($horarios_disponibles[$turno][$dia])) {
            $i = ($i + 1) % $dc;
            continue;
        }

        /* 3.c  recorrer hora a hora buscando hueco */
        $ini = strtotime($horarios_disponibles[$turno][$dia]['start']);
        $fin = strtotime($horarios_disponibles[$turno][$dia]['end']);

        $huecoEncontrado = false;
        for ($ha = $ini; $ha + 3600 <= $fin; $ha += 3600) {
            $ok = asignarBloqueHorario(
                $pdo,
                $teacher_id,
                $subject_id,
                $group_id,
                $classroom_id,
                $dia,
                $ha,
                $ha + 3600,
                $teacherAvailability
            );

            if ($ok) {
                $weekly_hours--;
                $huecoEncontrado = true;
                $ciclosSinHueco = 0; // reiniciar
                break;
            }
        }

        if (!$huecoEncontrado) {
            $ciclosSinHueco++;
            if ($ciclosSinHueco >= $dc * 3) {
                $errores[] =
                    "No hay espacio para completar horas de la materia $subject_id " .
                    "en el grupo $group_id.";
                return false;
            }
        }

        $i = ($i + 1) % $dc;   // siguiente día
    }

    return true;
}

/*--------------------------------------------------------------
  ↘ PROCESO PRINCIPAL
----------------------------------------------------------------*/
$teacher_id = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;
$materia_ids = isset($_POST['materias_asignadas']) ? $_POST['materias_asignadas'] : [];
$grupo_ids = isset($_POST['grupos_asignados']) ? array_filter($_POST['grupos_asignados'], 'is_numeric') : [];
$fechaHora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    if (empty($grupo_ids)) {
        throw new Exception("Seleccione al menos un grupo.");
    }

    $teacherAvailability = cargarDisponibilidadProfesor($pdo, $teacher_id);

    /* horas semanales de las materias */
    $lista_ids = implode(',', array_map('intval', $materia_ids));
    $sql_subjs = "SELECT subject_id, weekly_hours FROM subjects WHERE subject_id IN ($lista_ids)";
    $subjs = $pdo->query($sql_subjs)->fetchAll(PDO::FETCH_ASSOC);

    $map_hours = [];
    foreach ($subjs as $s)
        $map_hours[$s['subject_id']] = $s['weekly_hours'];

    $errores = [];

    foreach ($grupo_ids as $g) {
        foreach ($materia_ids as $m) {
            $wh = isset($map_hours[$m]) ? (int) $map_hours[$m] : 0;
            if ($wh <= 0) {
                $errores[] = "La materia $m no tiene horas > 0";
                continue;
            }
            $ok = asignarMateriaConBloquesYAparteSiSobra(
                $pdo,
                $teacher_id,
                $m,
                $g,
                $wh,
                $errores,
                $horarios_disponibles,
                $dias_semana,
                $teacherAvailability
            );
            if (!$ok)
                continue;

            /* registrar relación en teacher_subjects */
            $v = $pdo->prepare("SELECT COUNT(*) FROM teacher_subjects
                                WHERE teacher_id = ? AND subject_id = ? AND group_id = ?");
            $v->execute([$teacher_id, $m, $g]);
            if (!$v->fetchColumn()) {
                $ins = $pdo->prepare("INSERT INTO teacher_subjects
                    (teacher_id, subject_id, group_id, fyh_creacion, fyh_actualizacion)
                    VALUES (?,?,?,?,?)");
                $ins->execute([$teacher_id, $m, $g, $fechaHora, $fechaHora]);
            }
        }
    }

    if (!empty($errores))
        throw new Exception(implode(" | ", $errores));

    /* actualizar total de horas del profesor */
    $st = $pdo->prepare("SELECT SUM(s.weekly_hours)
                         FROM teacher_subjects ts
                         JOIN subjects s ON ts.subject_id = s.subject_id
                         WHERE ts.teacher_id = ?");
    $st->execute([$teacher_id]);
    $total = (int) $st->fetchColumn();

    $up = $pdo->prepare("UPDATE teachers
                         SET hours = ?, fyh_actualizacion = ?
                         WHERE teacher_id = ?");
    $up->execute([$total, $fechaHora, $teacher_id]);

    $pdo->commit();

    /* registro de evento */
    $usr = $_SESSION['sesion_email'] ?? 'desconocido';
    registrarEvento(
        $pdo,
        $usr,
        'Asignación Materias',
        'Asignadas al prof ' . $teacher_id
    );

    $_SESSION['mensaje'] = "Asignación exitosa.";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: ' . APP_URL . "/admin/profesores");
    exit;
}
