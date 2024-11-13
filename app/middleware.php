<?php
session_start();

/* Verificar si el usuario está autenticado */

if (isset($_SESSION['usuario_id'])) {
    include('

config.php');

    $usuario_id = $_SESSION['usuario_id'];
    $query = "SELECT estado FROM usuarios WHERE id_usuario = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$usuario_id]);
    $estado = $stmt->fetchColumn();

    /* Si el estado del usuario es 'INACTIVO', cerrar sesión y redirigir */
    if ($estado !== '1') {
        session_destroy();
        header("Location: ../../login.php?mensaje=Su sesión ha sido cerrada porque su cuenta ha sido inactivada");
        exit();
    }
}
?>
