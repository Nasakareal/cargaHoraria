<?php

/* Obtener los detalles del usuario y su rol */
function obtenerDatosUsuario($pdo, $id_usuario) {
    $query_usuario = $pdo->prepare("
        SELECT u.*, r.nombre_rol, r.id_rol 
        FROM usuarios u 
        JOIN roles r ON u.rol_id = r.id_rol 
        WHERE u.id_usuario = :id_usuario
    ");
    $query_usuario->bindParam(':id_usuario', $id_usuario);
    $query_usuario->execute();
    return $query_usuario->fetch(PDO::FETCH_ASSOC);
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
