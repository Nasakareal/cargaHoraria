<?php
function registrarEvento($pdo, $usuario_email, $accion, $descripcion = '') {
    $ip_usuario = $_SERVER['REMOTE_ADDR'];
    $query = "INSERT INTO registro_eventos (usuario_email, accion, descripcion, ip_usuario, estado)
              VALUES (:usuario_email, :accion, :descripcion, :ip_usuario, 'ACTIVO')";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':usuario_email', $usuario_email);
    $stmt->bindValue(':accion', $accion);
    $stmt->bindValue(':descripcion', $descripcion);
    $stmt->bindValue(':ip_usuario', $ip_usuario);
    $stmt->execute();
}
