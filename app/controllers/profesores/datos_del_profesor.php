<?php


$sql_teacher = "SELECT 
                    t.teacher_name AS nombres, 
                    ts.subject_id,  -- Añadir subject_id para obtener la materia específica
                    s.subject_name AS subject_name,
                    0 AS weekly_hours,
                    t.fyh_creacion,
                    t.estado
                FROM 
                    teachers AS t
                LEFT JOIN 
                    teacher_subjects AS ts ON ts.teacher_id = t.teacher_id  -- Relación con asignaciones
                LEFT JOIN 
                    subjects AS s ON s.subject_id = ts.subject_id  -- Relación con materias
                WHERE 
                    t.teacher_id = :teacher_id";

/* Prepara y ejecuta la consulta */
$query_teacher = $pdo->prepare($sql_teacher);
$query_teacher->bindParam(':teacher_id', $teacher_id);
$query_teacher->execute();
$teachers = $query_teacher->fetchAll(PDO::FETCH_ASSOC);

/* Verifica si se obtuvieron resultados */
foreach ($teachers as $teacher) {
    $nombres = $teacher['nombres'];
    $materia_id = isset($teacher['subject_id']) ? $teacher['subject_id'] : null; /* Captura el ID de la materia */
    $materias = !empty($teacher['subject_name']) ? $teacher['subject_name'] : 'No asignada';
    $horas_semanales = $teacher['weekly_hours'];
    $fyh_creacion = $teacher['fyh_creacion'];
    $estado = $teacher['estado'];


}

?>
