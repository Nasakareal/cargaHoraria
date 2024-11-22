<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['sesion_id_usuario']) || !isset($_SESSION['sesion_email'])) {
    // Si no hay sesión activa
    $_SESSION['mensaje'] = "Debe iniciar sesión para acceder.";
    $_SESSION['icono'] = "warning";
    header("Location: " . APP_URL . "/login");
    exit();
}

include_once('config.php'); // Conexión a la base de datos

$usuario_id = $_SESSION['sesion_id_usuario'];

// Verificar si el usuario está activo
try {
    $query = $pdo->prepare("SELECT estado FROM usuarios WHERE id_usuario = ?");
    $query->execute([$usuario_id]);
    $estado = $query->fetchColumn();

    if ($estado !== '1') {
        // Si el usuario no está activo
        session_destroy();
        $_SESSION['mensaje'] = "Tu cuenta ha sido desactivada. Contacta al administrador.";
        $_SESSION['icono'] = "error";
        header("Location: " . APP_URL . "/login");
        exit();
    }
} catch (Exception $e) {
    error_log("Error al verificar estado del usuario: " . $e->getMessage());
    $_SESSION['mensaje'] = "Error interno. Contacte al administrador.";
    $_SESSION['icono'] = "error";
    header("Location: " . APP_URL . "/login");
    exit();
}

/**
 * Función para verificar permisos del usuario
 */
function verificarPermiso($usuario_id, $nombre_permiso, $pdo)
{
    try {
        $query = $pdo->prepare("
            SELECT COUNT(*) 
            FROM permisos_roles pr
            INNER JOIN permisos p ON pr.id_permiso = p.id_permiso
            WHERE pr.id_rol = (SELECT rol_id FROM usuarios WHERE id_usuario = ?)
            AND p.nombre_permiso = ?
            AND p.estado = '1'
        ");
        $query->execute([$usuario_id, $nombre_permiso]);
        $resultado = $query->fetchColumn();

        // Depuración
        error_log("Verificando permiso: Usuario ID: $usuario_id, Permiso: $nombre_permiso, Resultado: $resultado");

        return $resultado > 0;
    } catch (Exception $e) {
        error_log("Error al verificar permiso: " . $e->getMessage());
        return false;
    }
}

?>
