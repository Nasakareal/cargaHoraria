<?php

/* Consulta para obtener los datos del profesor */
$sql_teacher = "
    SELECT 
        t.teacher_name AS nombres, 
        t.clasificacion AS clasificacion,
        t.hours AS horas_semanales,             /* Obtener el campo hours como horas_semanales */
        t.program_id AS program_id,             /* Programa de adscripción */
        t.specialization_program_id AS specialization_program_id, /* Programa de especialización */
        p_ads.program_name AS programa_adscripcion, /* Nombre del programa de adscripción */
        p_spec.program_name AS programa_especializacion /* Nombre del programa de especialización */
    FROM 
        teachers AS t
    LEFT JOIN 
        programs p_ads ON p_ads.program_id = t.program_id /* Programa de adscripción */
    LEFT JOIN 
        programs p_spec ON p_spec.program_id = t.specialization_program_id /* Programa de especialización */
    WHERE 
        t.teacher_id = :teacher_id";

$query_teacher = $pdo->prepare($sql_teacher);
$query_teacher->bindParam(':teacher_id', $teacher_id);
$query_teacher->execute();
$teacher = $query_teacher->fetch(PDO::FETCH_ASSOC);

/* Si el profesor existe, asignamos las variables para el formulario */
if ($teacher) {
    $nombres = $teacher['nombres'];
    $clasificacion = $teacher['clasificacion'] ?? 'No asignado';
    $horas_semanales = $teacher['horas_semanales'] ?? 0;
    $program_id = $teacher['program_id'] ?? null;
    $specialization_program_id = $teacher['specialization_program_id'] ?? null;
    $programa_adscripcion = $teacher['programa_adscripcion'] ?? 'No asignado';
    $programa_especializacion = $teacher['programa_especializacion'] ?? 'No asignado';
}
