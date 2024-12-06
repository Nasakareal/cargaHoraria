<?php
function obtenerGrupos($pdo)
{
    $sql = "SELECT group_id, group_name FROM `groups` ORDER BY group_name";
    $query = $pdo->prepare($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

$grupos = obtenerGrupos($pdo);
?>
