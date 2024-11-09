<?php

/* Consulta para obtener los datos del profesor, incluyendo el nombre, programa, área y clasificación */
$sql_teacher = "
    SELECT 
        t.teacher_name AS nombres, 
        t.area AS area,
        t.clasificacion AS clasificacion,
        p.program_name AS programa,
        c.term_name AS cuatrimestre
    FROM 
        teachers AS t
    LEFT JOIN 
        teacher_program_term tpt ON tpt.teacher_id = t.teacher_id
    LEFT JOIN 
        programs p ON p.program_id = tpt.program_id
    LEFT JOIN 
        terms c ON c.term_id = tpt.term_id
    WHERE 
        t.teacher_id = :teacher_id";

$query_teacher = $pdo->prepare($sql_teacher);
$query_teacher->bindParam(':teacher_id', $teacher_id);
$query_teacher->execute();
$teacher = $query_teacher->fetch(PDO::FETCH_ASSOC);

/* Si el profesor existe, asignamos las variables para el formulario */
if ($teacher) {
    $nombres = $teacher['nombres'];
    $area = $teacher['area'] ?? 'No asignado';
    $clasificacion = $teacher['clasificacion'] ?? 'No asignado';
    $programa = $teacher['programa'] ?? 'No asignado';
    $cuatrimestre = $teacher['cuatrimestre'] ?? 'No asignado';
}

/* Consulta para obtener las materias ya asignadas al profesor, incluyendo las horas semanales */
$sql_materias_asignadas = "
    SELECT 
        s.subject_name, 
        IFNULL(s.weekly_hours, 0) AS weekly_hours,  /* Si weekly_hours no tiene valor, asignamos 0 */
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

/* Inicializamos variables para mostrar las materias y el total de horas semanales */
$materias = [];
$horas_semanales = 0;

foreach ($materias_asignadas as $materia) {
    /* Verificamos que las horas semanales existan, si no, serán 0 */
    $materia['weekly_hours'] = isset($materia['weekly_hours']) ? $materia['weekly_hours'] : 0;
    $materias[] = $materia['subject_name'];
    $horas_semanales += $materia['weekly_hours'];
}

$materias = !empty($materias) ? implode(', ', $materias) : 'No asignado';
$horas_semanales = $horas_semanales > 0 ? $horas_semanales : 'No disponible';
