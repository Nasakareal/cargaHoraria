<?php
include('../../../app/config.php');
include('../../../app/helpers/verificar_admin.php');

try {
    
    $pdo->beginTransaction();

    
    $sql = "UPDATE usuarios 
            SET estado = '0', fyh_actualizacion = NOW() 
            WHERE rol_id != (SELECT id_rol FROM roles WHERE nombre_rol = 'ADMINISTRADOR')";

    $stmt = $pdo->prepare($sql);

    if ($stmt->execute()) {
        
        $pdo->commit();

        session_start();
        $_SESSION['mensaje'] = "Todos los usuarios fueron desactivados con éxito.";
        $_SESSION['icono'] = "success";
    } else {
        
        $pdo->rollBack();

        session_start();
        $_SESSION['mensaje'] = "Error al desactivar usuarios.";
        $_SESSION['icono'] = "error";
    }
} catch (Exception $e) {
    
    $pdo->rollBack();

    session_start();
    $_SESSION['mensaje'] = "Ocurrió un error: " . $e->getMessage();
    $_SESSION['icono'] = "error";
}


header('Location: ' . APP_URL . '/admin/configuraciones');
exit;
