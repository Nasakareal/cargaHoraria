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

function cargarDisponibilidadProfesor($pdo, $teacher_id) {
    $sql = "SELECT day_of_week, start_time, end_time
            FROM teacher_availability
            WHERE teacher_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$teacher_id]);
    $disponibilidad = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dia = $row['day_of_week'];
        if (!isset($disponibilidad[$dia])) {
            $disponibilidad[$dia] = [];
        }
        $disponibilidad[$dia][] = [
            'start' => $row['start_time'],
            'end'   => $row['end_time']
        ];
    }
    return $disponibilidad;
}

function mapDia($diaEsp) {
    static $m = [
        'Lunes' => 'Monday', 'Martes' => 'Tuesday',
        'Miércoles' => 'Wednesday', 'Miercoles' => 'Wednesday',
        'Jueves' => 'Thursday', 'Viernes' => 'Friday',
        'Sábado' => 'Saturday', 'Sabado' => 'Saturday', 'Domingo' => 'Sunday'
    ];
    return isset($m[$diaEsp]) ? $m[$diaEsp] : null;
}

function profesorEstaDisponible($teacherAvailability, $diaEsp, $start, $end) {
    $diaIngles = mapDia($diaEsp);
    if (!$diaIngles) {
        return false;
    }
    if (!isset($teacherAvailability[$diaIngles])) {
        return false;
    }
    $bloqueStart = strtotime($start);
    $bloqueEnd   = strtotime($end);
    foreach ($teacherAvailability[$diaIngles] as $rango) {
        $rangoStart = strtotime($rango['start']);
        $rangoEnd   = strtotime($rango['end']);
        if ($bloqueStart >= $rangoStart && $bloqueEnd <= $rangoEnd) {
            return true;
        }
    }
    return false;
}

function teacherLibreEnHorario($pdo, $teacher_id, $diaEsp, $start_time, $end_time) {
    if (!$teacher_id) return true;
    $sql = "SELECT COUNT(*) FROM schedule_assignments
            WHERE teacher_id = ? AND schedule_day = ?
              AND (start_time < ? AND end_time > ?)";
    $st = $pdo->prepare($sql);
    $st->execute([$teacher_id, $diaEsp, $end_time, $start_time]);
    return ($st->fetchColumn() == 0);
}

function obtenerTurnoDelGrupo($pdo, $group_id) {
    $sql = "SELECT turn_id FROM `groups` WHERE group_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$group_id]);
    $turn_id = $stmt->fetchColumn();
    $map = [
        1=>'MATUTINO',2=>'VESPERTINO',3=>'MIXTO',4=>'ZINAPÉCUARO',
        5=>'ENFERMERIA',6=>'MATUTINO AVANZADO',7=>'VESPERTINO AVANZADO'
    ];
    return isset($map[$turn_id]) ? $map[$turn_id] : 'MATUTINO';
}

function asignarBloqueHorario($pdo, $teacher_id, $subject_id, $group_id, $classroom_id,
    $diaEsp, $start_ts, $end_ts, &$errores, $teacherAvailability
) {
    $start_time = date('H:i:s', $start_ts);
    $end_time   = date('H:i:s', $end_ts);

    if (!profesorEstaDisponible($teacherAvailability, $diaEsp, $start_time, $end_time)) {
        return false;
    }
    $qGroup = $pdo->prepare("
        SELECT COUNT(*) FROM schedule_assignments
        WHERE group_id = :g
          AND schedule_day = :d
          AND (start_time < :et AND end_time > :st)
    ");
    $qGroup->execute([
        ':g'=>$group_id, ':d'=>$diaEsp, ':st'=>$start_time, ':et'=>$end_time
    ]);
    if($qGroup->fetchColumn()>0){
        return false;
    }

    if($teacher_id){
        if(!teacherLibreEnHorario($pdo, $teacher_id, $diaEsp, $start_time, $end_time)){
            return false;
        }
    }

    $ins = $pdo->prepare("
        INSERT INTO schedule_assignments
            (subject_id, group_id, teacher_id, classroom_id, schedule_day,
             start_time, end_time, estado, fyh_creacion, tipo_espacio)
        VALUES
            (:subj,:gr,:t,:cls,:dia,:st,:et,'activo',NOW(),'Aula')
    ");
    $ins->execute([
        ':subj'=>$subject_id, ':gr'=>$group_id, ':t'=>$teacher_id,
        ':cls'=>$classroom_id, ':dia'=>$diaEsp, ':st'=>$start_time, ':et'=>$end_time
    ]);

    return true;
}

function actualizarBloquesExistentes($pdo, $teacher_id, $subject_id, $group_id, $teacherAvailability, &$horasPendientes) {
    $sql = "SELECT assignment_id, schedule_day, start_time, end_time
            FROM schedule_assignments
            WHERE subject_id=? AND group_id=?
              AND (teacher_id=0 OR teacher_id IS NULL)
              AND estado='activo'
            ORDER BY schedule_day, start_time";
    $st = $pdo->prepare($sql);
    $st->execute([$subject_id, $group_id]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    $actualizados = 0;
    foreach ($rows as $r) {
        if ($horasPendientes <= 0) break;
        $dia = $r['schedule_day'];
        $stt = $r['start_time'];
        $ett = $r['end_time'];
        $id  = $r['assignment_id'];
        if (!profesorEstaDisponible($teacherAvailability, $dia, $stt, $ett)) {
            continue;
        }
        if (!teacherLibreEnHorario($pdo, $teacher_id, $dia, $stt, $ett)) {
            continue;
        }
        $upd = $pdo->prepare("
            UPDATE schedule_assignments
               SET teacher_id=?, fyh_actualizacion=NOW()
             WHERE assignment_id=?
        ");
        $upd->execute([$teacher_id, $id]);
        $actualizados++;
        $horasPendientes--;
    }
    return $actualizados;
}

function asignarMateriaAHorarioRoundRobin(
    $pdo, $teacher_id, $subject_id, $group_id, $weekly_hours,
    &$errores, $horarios_disponibles, $dias_semana, $teacherAvailability
) {
    $q = "SELECT classroom_assigned, turn_id FROM `groups` WHERE group_id = ?";
    $s = $pdo->prepare($q);
    $s->execute([$group_id]);
    $gi = $s->fetch(PDO::FETCH_ASSOC);
    if(!$gi){
        $errores[]="No se encontró el grupo ID: $group_id.";
        return false;
    }
    $classroom_id = $gi['classroom_assigned'] ?? null;
    $m = [
        1=>'MATUTINO',2=>'VESPERTINO',3=>'MIXTO',4=>'ZINAPÉCUARO',
        5=>'ENFERMERIA',6=>'MATUTINO AVANZADO',7=>'VESPERTINO AVANZADO'
    ];
    $turno_id = $gi['turn_id'];
    $turno    = isset($m[$turno_id])?$m[$turno_id]:'MATUTINO';

    if(!isset($dias_semana[$turno])){
        $errores[]="No hay días configurados para el turno: $turno. (Grupo $group_id)";
        return false;
    }
    $dias_del_turno = $dias_semana[$turno];

    if(!isset($horarios_disponibles[$turno])){
        $errores[]="No hay horarios_disponibles para el turno: $turno. (Grupo $group_id)";
        return false;
    }

    $restan = $weekly_hours;
    $usados = actualizarBloquesExistentes($pdo, $teacher_id, $subject_id, $group_id, $teacherAvailability, $restan);
    if ($restan <= 0) return true; // si ya se cubrieron con bloques existentes

    $dc = count($dias_del_turno);
    $i=0; 
    $cs=0;
    while($restan>0){
        $dia = $dias_del_turno[$i];
        if(!isset($horarios_disponibles[$turno][$dia])){
            $i=($i+1)%$dc;
            continue;
        }
        $start_str = $horarios_disponibles[$turno][$dia]['start'];
        $end_str   = $horarios_disponibles[$turno][$dia]['end'];
        $ini = strtotime($start_str);
        $fin = strtotime($end_str);

        $asignado = false;
        $ha = $ini;
        while($ha+3600 <= $fin){
            $ok = asignarBloqueHorario(
                $pdo, $teacher_id, $subject_id, $group_id,
                $classroom_id, $dia, $ha, $ha+3600,
                $errores, $teacherAvailability
            );
            if($ok){
                $restan--;
                $asignado=true;
                break;
            }
            $ha+=3600;
        }

        if(!$asignado){
            $cs++;
            if($cs >= $dc*3){
                $errores[]="No se puede asignar la materia, por la disponibilidad del profesor.";
                return false;
            }
        } else {
            $cs=0;
        }
        $i=($i+1)%$dc;
    }

    if($restan>0){
        $errores[]="No se pudo asignar $restan horas (ROUND-ROBIN) a la materia $subject_id (Grupo $group_id).";
        return false;
    }
    return true;
}

$teacher_id  = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;
$materia_ids = isset($_POST['materias_asignadas']) ? $_POST['materias_asignadas'] : [];
$grupo_ids   = isset($_POST['grupos_asignados']) ? array_filter($_POST['grupos_asignados'],'is_numeric') : [];
$fechaHora   = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    if (empty($grupo_ids)) {
        throw new Exception("Debe seleccionar al menos un grupo para asignar materias.");
    }

    $teacherAvailability = cargarDisponibilidadProfesor($pdo, $teacher_id);

    $lista_ids = implode(',', array_map('intval', $materia_ids));
    $sql_subjects = "SELECT subject_id, weekly_hours FROM subjects WHERE subject_id IN ($lista_ids)";
    $stmt_subjs   = $pdo->query($sql_subjects);
    $subjects_data = $stmt_subjs->fetchAll(PDO::FETCH_ASSOC);

    $map_hours = [];
    foreach($subjects_data as $sd){
        $map_hours[$sd['subject_id']] = (int)$sd['weekly_hours'];
    }

    $errores = [];
    foreach($grupo_ids as $grupo_id){
        foreach($materia_ids as $materia_id){
            $wh = isset($map_hours[$materia_id]) ? $map_hours[$materia_id] : 0;
            if($wh<=0){
                $errores[]="La materia $materia_id no tiene weekly_hours > 0.";
                continue;
            }
            $asignada = asignarMateriaAHorarioRoundRobin(
                $pdo, 
                $teacher_id, 
                $materia_id, 
                $grupo_id, 
                $wh, 
                $errores, 
                $horarios_disponibles, 
                $dias_semana, 
                $teacherAvailability
            );
            if(!$asignada){
                continue;
            }
            $verif = $pdo->prepare("
                SELECT COUNT(*) FROM teacher_subjects
                WHERE teacher_id=? AND subject_id=? AND group_id=?
            ");
            $verif->execute([$teacher_id, $materia_id, $grupo_id]);
            $existe = $verif->fetchColumn();
            if(!$existe){
                $ins = $pdo->prepare("
                    INSERT INTO teacher_subjects
                    (teacher_id, subject_id, group_id, fyh_creacion, fyh_actualizacion)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $ins->execute([$teacher_id, $materia_id, $grupo_id, $fechaHora, $fechaHora]);
            }
        }
    }

    if(!empty($errores)){
        throw new Exception(implode(" | ", $errores));
    }

    $sentencia_horas_totales = $pdo->prepare("
        SELECT SUM(s.weekly_hours) AS total_hours
        FROM teacher_subjects ts
        JOIN subjects s ON ts.subject_id = s.subject_id
        WHERE ts.teacher_id = ?
    ");
    $sentencia_horas_totales->execute([$teacher_id]);
    $total_hours = (int)$sentencia_horas_totales->fetchColumn();

    $sentencia_actualizar_horas = $pdo->prepare("
        UPDATE teachers SET hours=?, fyh_actualizacion=? WHERE teacher_id=?
    ");
    $sentencia_actualizar_horas->execute([$total_hours, $fechaHora, $teacher_id]);

    $pdo->commit();

    $usuario_email = isset($_SESSION['sesion_email']) ? $_SESSION['sesion_email'] : 'desconocido';
    $accion        = 'Asignación de materias + horarios RoundRobin';
    $descripcion   = "Se asignaron materias al profesor con ID $teacher_id: ".implode(', ',$materia_ids);
    registrarEvento($pdo, $usuario_email, $accion, $descripcion);

    $_SESSION['mensaje'] = "Se han asignado las materias y generado/actualizado los horarios.";
    $_SESSION['icono']   = "success";
    header('Location: '.APP_URL."/admin/profesores");
    exit;
}
catch(Exception $e){
    $pdo->rollBack();
    $_SESSION['mensaje'] = "Error: ".$e->getMessage();
    $_SESSION['icono']   = "error";
    error_log("Error: ".$e->getMessage());
    header('Location: '.APP_URL."/admin/profesores");
    exit;
}
