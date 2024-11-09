<?php

function tienePermiso($pdo, $id_rol, $nombre_permiso) {
    $query = $pdo->prepare("
        SELECT COUNT(*) 
        FROM permisos_roles pr 
        JOIN permisos p ON pr.id_permiso = p.id_permiso 
        WHERE pr.id_rol = :id_rol AND p.nombre_permiso = :nombre_permiso
    ");
    $query->bindParam(':id_rol', $id_rol);
    $query->bindParam(':nombre_permiso', $nombre_permiso);
    $query->execute();
    
    return $query->fetchColumn() > 0;
}
?>
