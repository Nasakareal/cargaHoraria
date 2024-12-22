<?php

$sql_group = "
    SELECT g.group_name, g.program_id, g.term_id, g.volume, 
           g.turn_id,
           p.program_name, t.term_name, s.shift_name AS turno, 
           el.level_id AS nivel_id,
           el.level_name AS nivel_educativo,
           GROUP_CONCAT(sj.subject_name SEPARATOR ', ') AS materias
    FROM `groups` g 
    LEFT JOIN programs p ON g.program_id = p.program_id 
    LEFT JOIN terms t ON g.term_id = t.term_id
    LEFT JOIN shifts s ON g.turn_id = s.shift_id
    LEFT JOIN educational_levels el ON g.group_id = el.group_id
    LEFT JOIN group_subjects gs ON g.group_id = gs.group_id
    LEFT JOIN subjects sj ON gs.subject_id = sj.subject_id
    WHERE g.group_id = :group_id AND g.estado = '1'
    GROUP BY g.group_id, g.program_id, g.term_id, g.volume, 
             g.turn_id, p.program_name, t.term_name, s.shift_name, el.level_id, el.level_name
";

$query_group = $pdo->prepare($sql_group);
$query_group->bindParam(':group_id', $group_id, PDO::PARAM_INT);
$query_group->execute();

$group_data = $query_group->fetch(PDO::FETCH_ASSOC);

if ($group_data) {
    $group_name = htmlspecialchars($group_data['group_name'] ?? "Grupo no encontrado", ENT_QUOTES, 'UTF-8');
    $program_id = $group_data['program_id'];
    $program_name = htmlspecialchars($group_data['program_name'] ?? "Programa no encontrado", ENT_QUOTES, 'UTF-8');
    $term_id = $group_data['term_id'];
    $term_name = htmlspecialchars($group_data['term_name'] ?? "Cuatrimestre no encontrado", ENT_QUOTES, 'UTF-8');
    $volumen_grupo = htmlspecialchars($group_data['volume'] ?? "Volumen no encontrado", ENT_QUOTES, 'UTF-8');
    $turn_id = $group_data['turn_id'];
    $turno = htmlspecialchars($group_data['turno'] ?? "Turno no encontrado", ENT_QUOTES, 'UTF-8');
    $nivel_id = $group_data['nivel_id'];
    $nivel_educativo = htmlspecialchars($group_data['nivel_educativo'] ?? "Nivel educativo no encontrado", ENT_QUOTES, 'UTF-8');
    $materias = htmlspecialchars($group_data['materias'] ?? "No se encontraron materias", ENT_QUOTES, 'UTF-8');
} else {

    $group_name = "Grupo no encontrado (ID: $group_id)";
    $program_id = null;
    $program_name = "Programa no encontrado";
    $term_id = null;
    $term_name = "Cuatrimestre no encontrado";
    $volumen_grupo = "N/A";
    $turn_id = null;
    $turno = "Turno no encontrado";
    $nivel_id = null;
    $nivel_educativo = "Nivel educativo no encontrado";
    $materias = "No se encontraron materias";
}