<?php
$group_id = $_GET['id']; 

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
    $selected_program_id = $group_data['program_id']; // Programa seleccionado
    $selected_term_id = $group_data['term_id']; // Cuatrimestre seleccionado
    // Agregamos esto para asegurar que se recupera correctamente
    $program_name = $group_data['program_name'] ?? "Programa no encontrado";
} else {
    $group_name = "Grupo no encontrado";
    $selected_program_id = null; // No hay programa seleccionado
    $selected_term_id = null; // No hay cuatrimestre seleccionado
}
