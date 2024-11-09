<?php
include ('../../../app/config.php');

/* Obtener el ID del rol y los permisos seleccionados del formulario */
$id_rol = $_POST['id_rol'];
$permisos_seleccionados = isset($_POST['permisos']) ? $_POST['permisos'] : [];

/* Iniciar una transacción */
$pdo->beginTransaction();

try {
    /* Eliminar todos los permisos actuales del rol para reiniciar las asignaciones */
    $delete_permisos = $pdo->prepare("DELETE FROM permisos_roles WHERE id_rol = :id_rol");
    $delete_permisos->bindParam(':id_rol', $id_rol);
    $delete_permisos->execute();

    /* Insertar los permisos seleccionados nuevamente */
    $insert_permiso = $pdo->prepare("INSERT INTO permisos_roles (id_rol, id_permiso) VALUES (:id_rol, :id_permiso)");
    foreach ($permisos_seleccionados as $id_permiso) {
        $insert_permiso->bindParam(':id_rol', $id_rol);
        $insert_permiso->bindParam(':id_permiso', $id_permiso);
        $insert_permiso->execute();
    }

    /* Confirmar los cambios en la base de datos */
    $pdo->commit();

    /* Redirigir a la lista de roles con un mensaje de éxito */
    session_start();
    $_SESSION['mensaje'] = "Permisos del rol actualizados correctamente";
    $_SESSION['icono'] = "success";
    header('Location: ' . APP_URL . '/admin/roles');
    exit();

} catch (Exception $e) {
    /* Si ocurre un error, revertir los cambios */
    $pdo->rollBack();

    session_start();
    $_SESSION['mensaje'] = "Error al actualizar los permisos del rol";
    $_SESSION['icono'] = "error";
    echo "<script>window.history.back();</script>";
    exit();
}
?>
