<?php
$group_id = $_GET['id']; 

$sql_group = "
    SELECT g.group_name, p.program_name 
    FROM `groups` g 
    JOIN programs p ON g.program_id = p.program_id 
    WHERE g.group_id = :group_id AND g.estado = '1'
";

$query_group = $pdo->prepare($sql_group);
$query_group->bindParam(':group_id', $group_id, PDO::PARAM_INT);
$query_group->execute();

$group_data = $query_group->fetch(PDO::FETCH_ASSOC);

if ($group_data) {
    $group_name = $group_data['group_name'];
    $program_name = $group_data['program_name'];
} else {
    $group_name = "Grupo no encontrado";
    $program_name = "Programa no encontrado"; 
}
?>
