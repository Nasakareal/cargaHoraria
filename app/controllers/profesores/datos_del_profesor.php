<?php

/* Consulta para obtener los datos del profesor */
$sql_teacher = "
    SELECT 
        t.teacher_name AS nombres, 
        t.clasificacion AS clasificacion,
        t.hours AS horas_semanales, 
        t.program_id AS program_id, 
        t.specialization_program_id AS specialization_program_id, 
        t.area AS area, 
        p_ads.program_name AS programa_adscripcion, 
        p_spec.program_name AS programa_especializacion,
        GROUP_CONCAT(DISTINCT ptt.program_name SEPARATOR ', ') AS programas,
        GROUP_CONCAT(DISTINCT CONCAT(g.group_name, ' (', sh.shift_name, ')') SEPARATOR ', ') AS grupos
    FROM 
        teachers AS t
    LEFT JOIN 
        programs p_ads ON p_ads.program_id = t.program_id
    LEFT JOIN 
        programs p_spec ON p_spec.program_id = t.specialization_program_id
    LEFT JOIN 
        teacher_program_term tpt ON t.teacher_id = tpt.teacher_id
    LEFT JOIN 
        programs ptt ON tpt.program_id = ptt.program_id
    LEFT JOIN 
        teacher_subjects ts ON t.teacher_id = ts.teacher_id
    LEFT JOIN 
        `groups` g ON g.group_id = ts.group_id
    LEFT JOIN 
        shifts sh ON g.turn_id = sh.shift_id
    WHERE 
        t.teacher_id = :teacher_id
    GROUP BY 
        t.teacher_id";

$query_teacher = $pdo->prepare($sql_teacher);
$query_teacher->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
$query_teacher->execute();
$teacher = $query_teacher->fetch(PDO::FETCH_ASSOC);

if ($teacher) {
    $nombres = $teacher['nombres'];
    $clasificacion = $teacher['clasificacion'] ?? 'No asignado';
    $horas_semanales = $teacher['horas_semanales'] ?? 0;
    $program_id = $teacher['program_id'] ?? null;
    $specialization_program_id = $teacher['specialization_program_id'] ?? null;
    $programa_adscripcion = $teacher['programa_adscripcion'] ?? 'No asignado';
    $programa_especializacion = $teacher['programa_especializacion'] ?? 'No asignado';
    $programas = $teacher['programas'] ?? 'No asignado';
    $area = $teacher['area'] ?? 'No asignado';
    $grupos = $teacher['grupos'] ?? 'No asignado';
} else {
    $nombres = '';
    $clasificacion = '';
    $horas_semanales = 0;
    $program_id = null;
    $specialization_program_id = null;
    $programa_adscripcion = 'No asignado';
    $programa_especializacion = 'No asignado';
    $programas = 'No asignado';
    $area = 'No asignado';
    $grupos = 'No asignado';
}

/* Consulta para obtener las materias asignadas al profesor */
$sql_materias_asignadas = "
    SELECT 
        s.subject_name, 
        s.weekly_hours AS horas_materia, 
        s.subject_id,
        CONCAT(g.group_name, ' (', sh.shift_name, ')') AS grupo_turno
    FROM 
        teacher_subjects ts 
    INNER JOIN 
        subjects s ON ts.subject_id = s.subject_id 
    LEFT JOIN 
        `groups` g ON g.group_id = ts.group_id
    LEFT JOIN 
        shifts sh ON g.turn_id = sh.shift_id
    WHERE 
        ts.teacher_id = :teacher_id";

$query_materias_asignadas = $pdo->prepare($sql_materias_asignadas);
$query_materias_asignadas->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
$query_materias_asignadas->execute();
$materias_asignadas = $query_materias_asignadas->fetchAll(PDO::FETCH_ASSOC);

$materias = !empty($materias_asignadas) ? implode(', ', array_column($materias_asignadas, 'subject_name')) : 'No asignado';

$grupos_materias = !empty($materias_asignadas) ? implode(', ', array_column($materias_asignadas, 'grupo_turno')) : 'No asignado';

/* Consulta para obtener los horarios disponibles del profesor */
$sql_horarios_disponibles = "
    SELECT 
        day_of_week, 
        start_time, 
        end_time 
    FROM 
        teacher_availability 
    WHERE 
        teacher_id = :teacher_id";

$query_horarios_disponibles = $pdo->prepare($sql_horarios_disponibles);
$query_horarios_disponibles->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
$query_horarios_disponibles->execute();
$horarios_disponibles = $query_horarios_disponibles->fetchAll(PDO::FETCH_ASSOC);

$horarios = [];
if (!empty($horarios_disponibles)) {
    $dias_espanol = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo',
    ];

    foreach ($horarios_disponibles as $horario) {
        $dia = $dias_espanol[$horario['day_of_week']] ?? $horario['day_of_week'];
        $horarios[] = "$dia de " . date('H:i', strtotime($horario['start_time'])) . " a " . date('H:i', strtotime($horario['end_time']));
    }
}
$horarios = !empty($horarios) ? implode(', ', $horarios) : 'No asignado';

?>
