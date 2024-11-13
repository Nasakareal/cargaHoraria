<?php

include_once('../../app/config.php');

set_time_limit(100);
$pdo->exec("DELETE FROM schedule_assignments WHERE estado = 'activo'");

$horarios_disponibles = [
    'MATUTINO' => ['07:00:00' => '15:00:00'],
    'VESPERTINO' => ['12:00:00' => '20:00:00'],
    'MIXTO' => ['16:00:00' => '20:00:00', '07:00:00' => '18:00:00'],
    'ZINAPÉCUARO' => ['16:00:00' => '20:00:00', '07:00:00' => '18:00:00']
];

$dias_semana = [
    'MATUTINO' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
    'VESPERTINO' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
    'MIXTO' => ['Viernes', 'Sábado'],
    'ZINAPÉCUARO' => ['Viernes', 'Sábado']
];

/* Obtener datos de grupos */
$groups = $pdo->query("SELECT * FROM `groups` WHERE estado = '1'")->fetchAll(PDO::FETCH_ASSOC);

/* Obtener materias específicas de cada grupo desde `group_subjects` */
$subjects_by_group = [];
foreach ($groups as $group) {
    $stmt = $pdo->prepare("SELECT s.*, gs.group_id, g.classroom_assigned, g.lab_assigned 
                           FROM subjects s 
                           JOIN group_subjects gs ON gs.subject_id = s.subject_id 
                           JOIN `groups` g ON g.group_id = gs.group_id
                           WHERE gs.group_id = :group_id AND s.estado = '1'");
    $stmt->execute([':group_id' => $group['group_id']]);
    $subjects_by_group[$group['group_id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function asignarBloqueHorario($pdo, $subject, $group, $dia, $start_time, $end_time, $tipo_espacio, $profesor_id)
{
    if (!isset($subject['subject_id'])) {
        return false;
    }
    $formatted_start_time = date('H:i:s', $start_time);
    $formatted_end_time = date('H:i:s', $end_time);

    $check_availability = $pdo->prepare("SELECT COUNT(*) FROM schedule_assignments 
        WHERE 
            (group_id = :group_id OR classroom_id = :classroom_id)
            AND schedule_day = :schedule_day 
            AND (
                (start_time <= :start_time AND end_time > :start_time) OR 
                (start_time < :end_time AND end_time >= :end_time)
            )");
    $check_availability->execute([
        ':group_id' => $group['group_id'],
        ':classroom_id' => $tipo_espacio === 'Laboratorio' ? $group['lab_assigned'] : $group['classroom_assigned'],
        ':schedule_day' => $dia,
        ':start_time' => $formatted_start_time,
        ':end_time' => $formatted_end_time
    ]);

    if ($check_availability->fetchColumn() > 0) {
        return false; /* Bloque no disponible */
    }

    $sql_insert = "INSERT INTO schedule_assignments 
                   (subject_id, group_id, teacher_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion, tipo_espacio)
                   VALUES (:subject_id, :group_id, :teacher_id, :classroom_id, :schedule_day, :start_time, :end_time, 'activo', NOW(), :tipo_espacio)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([
        ':subject_id' => $subject['subject_id'],
        ':group_id' => $group['group_id'],
        ':teacher_id' => $profesor_id,
        ':classroom_id' => $tipo_espacio === 'Laboratorio' ? $group['lab_assigned'] : $group['classroom_assigned'],
        ':schedule_day' => $dia,
        ':start_time' => $formatted_start_time,
        ':end_time' => $formatted_end_time,
        ':tipo_espacio' => $tipo_espacio
    ]);

    return true; /* Bloque asignado exitosamente */
}

function distribuirMateriasEnSemana($pdo, $group, $subjects, $horario_turno, $dias_turno)
{
    $inicio_turno = strtotime(array_keys($horario_turno)[0]);
    $fin_turno = strtotime(array_values($horario_turno)[0]);

    /* Distribuir materias equitativamente durante la semana */
    foreach ($dias_turno as $dia) {
        $inicio_actual = $inicio_turno;
        $materias_pendientes = $subjects;
        shuffle($materias_pendientes);

        while (!empty($materias_pendientes) && $inicio_actual < $fin_turno) {
            $asignacion_realizada = false;
            foreach ($materias_pendientes as $key => $subject) {
                $horas_restantes_aula = isset($subject['class_hours']) ? (int) $subject['class_hours'] : 0;
                $horas_restantes_lab = isset($subject['lab_hours']) ? (int) $subject['lab_hours'] : 0;
                $max_horas_aula = isset($subject['max_consecutive_class_hours']) && $subject['max_consecutive_class_hours'] > 0 ? (int) $subject['max_consecutive_class_hours'] : 2;
                $max_horas_lab = isset($subject['max_consecutive_lab_hours']) && $subject['max_consecutive_lab_hours'] > 0 ? (int) $subject['max_consecutive_lab_hours'] : 2;
                $profesor_id = isset($subject['subject_id']) ? obtenerProfesorAsignado($pdo, $subject['subject_id']) : null;
                if (!$profesor_id) {
                    $profesor_id = null;
                }

                /* Asignación de horas en Aula */
                if ($horas_restantes_aula > 0) {
                    $horas_a_asignar = min($horas_restantes_aula, $max_horas_aula);
                    $fin_bloque = strtotime("+{$horas_a_asignar} hours", $inicio_actual);

                    if ($fin_bloque <= $fin_turno && asignarBloqueHorario($pdo, $subject, $group, $dia, $inicio_actual, $fin_bloque, 'Aula', $profesor_id)) {
                        $horas_restantes_aula -= $horas_a_asignar;
                        $inicio_actual = $fin_bloque; // Actualizar el inicio del siguiente bloque
                        $asignacion_realizada = true;
                    } else {
                        $inicio_actual = strtotime("+1 hour", $inicio_actual); // Mover al siguiente bloque disponible
                    }
                }

                // Si se alcanzó el límite de horas consecutivas, dejar la materia pendiente para el siguiente día
                if ($horas_restantes_aula > 0) {
                    $materias_pendientes[$key]['class_hours'] = $horas_restantes_aula;
                } else {
                    unset($materias_pendientes[$key]);
                }

                // Reiniciar el inicio para asignar horas en Laboratorio
                if ($horas_restantes_lab > 0) {
                    $inicio_actual = $inicio_turno;
                    $horas_a_asignar = min($horas_restantes_lab, $max_horas_lab);
                    $fin_bloque = strtotime("+{$horas_a_asignar} hours", $inicio_actual);

                    if ($fin_bloque <= $fin_turno && asignarBloqueHorario($pdo, $subject, $group, $dia, $inicio_actual, $fin_bloque, 'Laboratorio', $profesor_id)) {
                        $horas_restantes_lab -= $horas_a_asignar;
                        $inicio_actual = $fin_bloque;
                        $asignacion_realizada = true;
                    } else {
                        $inicio_actual = strtotime("+1 hour", $inicio_actual); // Mover al siguiente bloque disponible
                    }
                }

                // Si se alcanzó el límite de horas consecutivas, dejar la materia pendiente para el siguiente día
                if ($horas_restantes_lab > 0) {
                    $materias_pendientes[$key]['lab_hours'] = $horas_restantes_lab;
                } else {
                    unset($materias_pendientes[$key]);
                }
            }

            // Si no se realizó ninguna asignación, romper el bucle para evitar un bucle infinito
            if (!$asignacion_realizada) {
                break;
            }
        }
    }
}

foreach ($groups as $group) {
    $turno_id = $group['turn_id'];
    $turno = array_search($turno_id, array_keys($horarios_disponibles)) !== false ? array_keys($horarios_disponibles)[$turno_id - 1] : 'MATUTINO';
    $horario_turno = $horarios_disponibles[$turno];
    $dias_turno = $dias_semana[$turno];

    if (!isset($subjects_by_group[$group['group_id']])) {
        continue;
    }

    $subjects = $subjects_by_group[$group['group_id']];
    distribuirMateriasEnSemana($pdo, $group, $subjects, $horario_turno, $dias_turno);
}

function obtenerProfesorAsignado($pdo, $subject_id)
{
    if (!$subject_id) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT teacher_id FROM teacher_subjects WHERE subject_id = :subject_id");
    $stmt->execute([':subject_id' => $subject_id]);
    return $stmt->fetchColumn();
}
?>
