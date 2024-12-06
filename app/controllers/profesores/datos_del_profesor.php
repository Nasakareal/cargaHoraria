<?php

/* Consulta para obtener los datos del profesor */
$sql_teacher = "
    SELECT 
        t.teacher_name AS nombres, 
        t.clasificacion AS clasificacion,
        t.hours AS horas_semanales, 
        t.program_id AS program_id, 
        t.specialization_program_id AS specialization_program_id, 
        p_ads.program_name AS programa_adscripcion, 
        p_spec.program_name AS programa_especializacion
    FROM 
        teachers AS t
    LEFT JOIN 
        programs p_ads ON p_ads.program_id = t.program_id
    LEFT JOIN 
        programs p_spec ON p_spec.program_id = t.specialization_program_id
    WHERE 
        t.teacher_id = :teacher_id";

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
} else {
    $nombres = '';
    $clasificacion = '';
    $horas_semanales = 0;
    $program_id = null;
    $specialization_program_id = null;
    $programa_adscripcion = 'No asignado';
    $programa_especializacion = 'No asignado';
}

/* Consulta para obtener los programas asignados al profesor */
$sql_programas_asignados = "
    SELECT 
        tp.program_id 
    FROM 
        teacher_program_term tp
    WHERE 
        tp.teacher_id = :teacher_id";

$query_programas_asignados = $pdo->prepare($sql_programas_asignados);
$query_programas_asignados->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
$query_programas_asignados->execute();
$programas_asignados = array_column($query_programas_asignados->fetchAll(PDO::FETCH_ASSOC), 'program_id');

/* Consulta para obtener los horarios del profesor */
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

/* Consulta para obtener las materias asignadas al profesor */
$sql_materias_asignadas = "
    SELECT 
        s.subject_name, 
        s.weekly_hours AS horas_materia, 
        s.subject_id
    FROM 
        teacher_subjects ts 
    INNER JOIN 
        subjects s ON ts.subject_id = s.subject_id 
    WHERE 
        ts.teacher_id = :teacher_id";

$query_materias_asignadas = $pdo->prepare($sql_materias_asignadas);
$query_materias_asignadas->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
$query_materias_asignadas->execute();
$materias_asignadas = $query_materias_asignadas->fetchAll(PDO::FETCH_ASSOC);

/* Inicializar las materias asignadas */
$materias = !empty($materias_asignadas) ? implode(', ', array_column($materias_asignadas, 'subject_name')) : 'No asignado';
