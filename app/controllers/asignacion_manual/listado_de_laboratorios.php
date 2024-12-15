<?php

$group_id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($group_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
    exit;
}

try {
    $sql_group_area = "SELECT area FROM `groups` WHERE group_id = :group_id AND estado = '1'";
    $stmt = $pdo->prepare($sql_group_area);
    $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
    $stmt->execute();

    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($group)) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró el grupo o el grupo está inactivo.']);
        exit;
    }

    $group_area = $group['area'];

    $sql_labs = "SELECT DISTINCT
                    l.lab_id, 
                    l.lab_name, 
                    l.fyh_creacion, 
                    l.description
                 FROM 
                    labs l
                 WHERE 
                    FIND_IN_SET(:group_area, l.area) > 0";

    $stmt = $pdo->prepare($sql_labs);
    $stmt->bindParam(':group_area', $group_area, PDO::PARAM_STR);
    $stmt->execute();

    $labs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Labs found: " . count($labs));

    if (empty($labs)) {
        $labs = [];
    }


} catch (PDOException $e) {
    exit;
}
?>
