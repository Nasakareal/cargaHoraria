<?php
include('../../config.php');

$programa_id = filter_input(INPUT_POST, 'programa_id', FILTER_VALIDATE_INT);
$cuatrimestre_id = filter_input(INPUT_POST, 'cuatrimestre_id', FILTER_VALIDATE_INT);
$teacher_id = filter_input(INPUT_POST, 'teacher_id', FILTER_VALIDATE_INT);

if ($programa_id && $cuatrimestre_id && $teacher_id) {
    
    $sql_materias_disponibles = "
        SELECT 
            s.subject_id, 
            s.subject_name
        FROM 
            subjects s
        INNER JOIN 
            program_term_subjects pts ON s.subject_id = pts.subject_id
        WHERE 
            pts.program_id = :programa_id
        AND 
            pts.term_id = :cuatrimestre_id
        AND 
            s.subject_id NOT IN (
                SELECT subject_id 
                FROM teacher_subjects 
                WHERE teacher_id = :teacher_id
            )";

    $query = $pdo->prepare($sql_materias_disponibles);
    $query->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
    $query->bindParam(':cuatrimestre_id', $cuatrimestre_id, PDO::PARAM_INT);
    $query->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
    $query->execute();
    $materias_disponibles = $query->fetchAll(PDO::FETCH_ASSOC);

    /* Generar el HTML para el select de materias disponibles */
    foreach ($materias_disponibles as $materia) {
        echo '<option value="' . $materia['subject_id'] . '">' . htmlspecialchars($materia['subject_name']) . '</option>';
    }
}
?>
