<?php

/* Obtener los detalles del rol */
function obtenerDatosRol($pdo, $id_rol) {
    $query_rol = $pdo->prepare("SELECT * FROM roles WHERE id_rol = :id_rol");
    $query_rol->bindParam(':id_rol', $id_rol);
    $query_rol->execute();
    return $query_rol->fetch(PDO::FETCH_ASSOC);
}

/* Obtener todos los permisos disponibles */
function obtenerPermisos($pdo) {
    $query_permisos = $pdo->prepare("SELECT * FROM permisos");
    $query_permisos->execute();
    return $query_permisos->fetchAll(PDO::FETCH_ASSOC);
}

/* Obtener los permisos asignados a un rol específico */
function obtenerPermisosAsignadosRol($pdo, $id_rol) {
    $query_permisos_rol = $pdo->prepare("SELECT id_permiso FROM permisos_roles WHERE id_rol = :id_rol");
    $query_permisos_rol->bindParam(':id_rol', $id_rol);
    $query_permisos_rol->execute();
    return $query_permisos_rol->fetchAll(PDO::FETCH_COLUMN);
}

?>
