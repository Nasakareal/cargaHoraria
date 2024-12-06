<?php

$sql_group = "
    SELECT g.group_name, g.program_id, g.term_id, g.volume, 
           p.program_name, t.term_name, s.shift_name AS turno, 
           el.level_name AS nivel_educativo  /* Obtener el nombre del nivel educativo */
    FROM `groups` g 
    LEFT JOIN programs p ON g.program_id = p.program_id 
    LEFT JOIN terms t ON g.term_id = t.term_id  /* Unimos con terms para obtener el cuatrimestre */
    LEFT JOIN shifts s ON g.turn_id = s.shift_id
    LEFT JOIN educational_levels el ON g.group_id = el.group_id  /* Unimos con la tabla de niveles educativos */
    WHERE g.group_id = :group_id AND g.estado = '1'
";

$query_group = $pdo->prepare($sql_group);
$query_group->bindParam(':group_id', $group_id, PDO::PARAM_INT);
$query_group->execute();

$group_data = $query_group->fetch(PDO::FETCH_ASSOC);

if ($group_data) {
    
    $group_name = htmlspecialchars($group_data['group_name'] ?? "Grupo no encontrado", ENT_QUOTES, 'UTF-8');
    $program_id = $group_data['program_id'];
    $program_name = htmlspecialchars($group_data['program_name'] ?? "Programa no encontrado", ENT_QUOTES, 'UTF-8');
    $term_name = htmlspecialchars($group_data['term_name'] ?? "Cuatrimestre no encontrado", ENT_QUOTES, 'UTF-8');
    $volumen_grupo = htmlspecialchars($group_data['volume'] ?? "Volumen no encontrado", ENT_QUOTES, 'UTF-8');
    $turno = htmlspecialchars($group_data['turno'] ?? "Turno no encontrado", ENT_QUOTES, 'UTF-8');
    $nivel_educativo = htmlspecialchars($group_data['nivel_educativo'] ?? "Nivel educativo no encontrado", ENT_QUOTES, 'UTF-8');
} else {
    $group_name = "Grupo no encontrado (ID: $group_id)";
    $program_id = null;
    $program_name = "Programa no encontrado";
    $term_name = "Cuatrimestre no encontrado";
    $year = "Año no encontrado";
    $volumen_grupo = "N/A";
    $turno = "Turno no encontrado";
    $nivel_educativo = "Nivel educativo no encontrado";
}