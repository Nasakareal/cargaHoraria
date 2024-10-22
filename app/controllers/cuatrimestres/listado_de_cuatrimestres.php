<?php
$sql_terms = "SELECT 
                term_id,
                term_name,
                fyh_creacion,
                fyh_actualizacion,
                estado
              FROM
                terms";

$query_terms = $pdo->prepare($sql_terms);
$query_terms->execute();
$terms = $query_terms->fetchAll(PDO::FETCH_ASSOC);

if (empty($terms)) {
    $terms = [];
}