<?php

$sql_subjects = "SELECT 
					subject_id,
					subject_name,
					is_specialization,
					hours_consecutive,
					fyh_creacion,
					estado
				FROM
					subjects";

$query_subjects = $pdo->prepare($sql_subjects);
$query_subjects->execute();
$subjects = $query_subjects->fetchAll(PDO::FETCH_ASSOC);


