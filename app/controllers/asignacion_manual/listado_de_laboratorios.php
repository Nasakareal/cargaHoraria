<?php

$group_id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($group_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
    exit;
}

$sql_subject_ids = "SELECT subject_id FROM group_subjects WHERE group_id = :group_id AND estado = '1'"; 
$stmt = $pdo->prepare($sql_subject_ids);
$stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
$stmt->execute();

$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($subjects)) {
    echo json_encode(['status' => 'error', 'message' => 'No se encontró ninguna materia asociada al grupo o el grupo está inactivo.']);
    exit;
}

error_log("subject_ids found: " . count($subjects));

$subject_ids = array_map(function($subject) { return $subject['subject_id']; }, $subjects);
$subject_ids_placeholder = implode(',', array_fill(0, count($subject_ids), '?'));

$sql_labs = "SELECT DISTINCT
                l.lab_id, 
                l.lab_name, 
                l.fyh_creacion, 
                l.description
            FROM 
                labs l
            INNER JOIN 
                subject_labs sl ON l.lab_id = sl.lab_id
            WHERE 
                sl.subject_id IN ($subject_ids_placeholder)";

$stmt = $pdo->prepare($sql_labs);
$stmt->execute($subject_ids);

$labs = $stmt->fetchAll(PDO::FETCH_ASSOC);

error_log("Labs found: " . count($labs));

if (empty($labs)) {
    $labs = [];
}

?>
