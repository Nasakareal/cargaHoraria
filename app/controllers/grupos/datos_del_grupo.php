<?php

$sql_group = "
    SELECT g.group_name, g.program_id, g.period, g.year, g.volume, 
           p.program_name, s.shift_name AS turno 
    FROM `groups` g 
    LEFT JOIN programs p ON g.program_id = p.program_id 
    LEFT JOIN shifts s ON g.turn_id = s.shift_id 
    WHERE g.group_id = :group_id AND g.estado = '1'
";

$query_group = $pdo->prepare($sql_group);
$query_group->bindParam(':group_id', $group_id, PDO::PARAM_INT);
$query_group->execute();

$group_data = $query_group->fetch(PDO::FETCH_ASSOC);

if ($group_data) {
    $group_name = htmlspecialchars($group_data['group_name'], ENT_QUOTES, 'UTF-8');
    $program_id = $group_data['program_id']; // Asegúrate de que program_id se define aquí
    $program_name = htmlspecialchars($group_data['program_name'], ENT_QUOTES, 'UTF-8') ?: "Programa no encontrado";
    $period = htmlspecialchars($group_data['period'], ENT_QUOTES, 'UTF-8') ?: "Periodo no encontrado";
    $year = htmlspecialchars($group_data['year'], ENT_QUOTES, 'UTF-8') ?: "Año no encontrado";
    $volumen_grupo = htmlspecialchars($group_data['volume'], ENT_QUOTES, 'UTF-8') ?: "Volumen no encontrado"; 
    $turno = htmlspecialchars($group_data['turno'], ENT_QUOTES, 'UTF-8') ?: "Turno no encontrado"; 
} else {
    // Mensajes de depuración
    $group_name = "Grupo no encontrado (ID: $group_id)";
    $program_id = null; 
    $program_name = "Programa no encontrado";
    $period = "Periodo no encontrado";
    $year = "Año no encontrado";
    $volumen_grupo = "N/A";
    $turno = "Turno no encontrado"; 
}
