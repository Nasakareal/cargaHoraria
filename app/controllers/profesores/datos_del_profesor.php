<?php

<<<<<<< HEAD

$sql_teacher = "SELECT 
                    t.teacher_name AS nombres, 
                    ts.subject_id,  -- Añadir subject_id para obtener la materia específica
                    s.subject_name AS subject_name,
                    0 AS weekly_hours,
=======
$sql_teacher = "SELECT 
                    t.teacher_name AS nombres, 
                    ts.subject_id, 
                    s.subject_name,
                    ts.weekly_hours,
>>>>>>> 09dfda8 (descagada)
                    t.fyh_creacion,
                    t.estado
                FROM 
                    teachers AS t
                LEFT JOIN 
<<<<<<< HEAD
                    teacher_subjects AS ts ON ts.teacher_id = t.teacher_id  -- Relación con asignaciones
                LEFT JOIN 
                    subjects AS s ON s.subject_id = ts.subject_id  -- Relación con materias
=======
                    teacher_subjects AS ts ON ts.teacher_id = t.teacher_id
                LEFT JOIN 
                    subjects AS s ON s.subject_id = ts.subject_id
>>>>>>> 09dfda8 (descagada)
                WHERE 
                    t.teacher_id = :teacher_id";

/* Prepara y ejecuta la consulta */
$query_teacher = $pdo->prepare($sql_teacher);
$query_teacher->bindParam(':teacher_id', $teacher_id);
$query_teacher->execute();
$teachers = $query_teacher->fetchAll(PDO::FETCH_ASSOC);

<<<<<<< HEAD
/* Verifica si se obtuvieron resultados */
foreach($teachers as $teacher) {
    $nombres = $teacher['nombres'];
    $materia_id = isset($teacher['subject_id']) ? $teacher['subject_id'] : null; /* Captura el ID de la materia */
    $materias = !empty($teacher['subject_name']) ? $teacher['subject_name'] : 'No asignada';
    $horas_semanales = $teacher['weekly_hours'];
    $fyh_creacion = $teacher['fyh_creacion'];
    $estado = $teacher['estado'];


=======
$nombres = '';
$materias = [];
$horas_semanales = [];
$fyh_creacion = '';
$estado = '';

/* Verifica si se obtuvieron resultados */
if ($teachers) {
    $nombres = $teachers[0]['nombres'];
    $fyh_creacion = $teachers[0]['fyh_creacion'];
    $estado = $teachers[0]['estado'];

    foreach ($teachers as $teacher) {
        $materias[] = [
            'subject_id' => $teacher['subject_id'],
            'subject_name' => $teacher['subject_name'],
            'weekly_hours' => $teacher['weekly_hours']
        ];
    }
>>>>>>> 09dfda8 (descagada)
}

?>
