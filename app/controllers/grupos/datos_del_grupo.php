<?php

$sql_group = "
    SELECT g.group_name, g.program_id, p.program_name, g.term_id 
    FROM `groups` g 
    LEFT JOIN programs p ON g.program_id = p.program_id 
    WHERE g.group_id = :group_id AND g.estado = '1'
";

$query_group = $pdo->prepare($sql_group);
$query_group->bindParam(':group_id', $group_id, PDO::PARAM_INT);
$query_group->execute();

$group_data = $query_group->fetch(PDO::FETCH_ASSOC);

if ($group_data) {
    $group_name = $group_data['group_name'];
    $selected_program_id = $group_data['program_id'];
    $selected_term_id = $group_data['term_id'];

    $program_name = $group_data['program_name'] ?? "Programa no encontrado";

    
    $sql_volumen = "
        SELECT COUNT(*) AS volumen 
        FROM students 
        WHERE group_id = :group_id AND estado = '1'
    ";

    $query_volumen = $pdo->prepare($sql_volumen);
    $query_volumen->bindParam(':group_id', $group_id, PDO::PARAM_INT);
    $query_volumen->execute();

    $volumen_data = $query_volumen->fetch(PDO::FETCH_ASSOC);
    $volumen_grupo = $volumen_data['volumen'];
} else {
    $group_name = "Grupo no encontrado";
    $selected_program_id = null;
    $selected_term_id = null;
    $volumen_grupo = "N/A";
}
