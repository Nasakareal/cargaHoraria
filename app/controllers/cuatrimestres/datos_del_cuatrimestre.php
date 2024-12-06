 <?php

 $sql_terms = "SELECT * FROM terms WHERE term_id = :term_id";
 $query_terms = $pdo->prepare($sql_terms);
 $query_terms->bindParam(':term_id', $term_id, PDO::PARAM_INT);
 $query_terms->execute();
 $datos_term = $query_terms->fetch(PDO::FETCH_ASSOC);

 if ($datos_term) {
     $terms_name = $datos_term['term_name'];
 } else {
     $terms_name = "Cuatrimestre no encontrado";
 }
