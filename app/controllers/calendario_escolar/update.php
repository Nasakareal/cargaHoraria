<?php

include('../../../app/config.php');

/* Función para convertir a mayúsculas y quitar acentos */
function normalize_string($string)
{
    $string = mb_strtoupper($string, 'UTF-8');
    $acentos = [
        'Á' => 'A',
        'É' => 'E',
        'Í' => 'I',
        'Ó' => 'O',
        'Ú' => 'U',
        'Ü' => 'U',
        'á' => 'A',
        'é' => 'E',
        'í' => 'I',
        'ó' => 'O',
        'ú' => 'U',
        'ü' => 'U'
    ];
    return strtr($string, $acentos);
}

/* Obtener los datos del formulario y normalizarlos */
$id_calendario = $_POST['id_calendario'];
$nombre_cuatrimestre = normalize_string($_POST['nombre_cuatrimestre']);
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'];
$estado = normalize_string($_POST['estado']);
$fechaHora = date("Y-m-d H:i:s");

try {
    /* Preparar la sentencia SQL para actualizar el calendario */
    $query = $pdo->prepare("UPDATE calendario_escolar 
                            SET nombre_cuatrimestre = :nombre_cuatrimestre, 
                                fecha_inicio = :fecha_inicio, 
                                fecha_fin = :fecha_fin, 
                                estado = :estado, 
                                fyh_actualizacion = :fyh_actualizacion
                            WHERE id = :id_calendario");

    /* Enlazar los parámetros con los valores normalizados del formulario */
    $query->bindParam(':nombre_cuatrimestre', $nombre_cuatrimestre);
    $query->bindParam(':fecha_inicio', $fecha_inicio);
    $query->bindParam(':fecha_fin', $fecha_fin);
    $query->bindParam(':estado', $estado);
    $query->bindParam(':fyh_actualizacion', $fechaHora);
    $query->bindParam(':id_calendario', $id_calendario, PDO::PARAM_INT);

    /* Ejecutar la sentencia */
    if ($query->execute()) {
        session_start();
        $_SESSION['mensaje'] = "El calendario se ha actualizado con éxito.";
        $_SESSION['icono'] = "success";
        header('Location: ' . APP_URL . "/admin/configuraciones/calendarios");
        exit();
    } else {
        session_start();
        $_SESSION['mensaje'] = "No se pudo actualizar el calendario. Por favor, intente de nuevo.";
        $_SESSION['icono'] = "error";
        echo "<script>window.history.back();</script>";
        exit();
    }
} catch (Exception $e) {
    session_start();
    $_SESSION['mensaje'] = "Error al actualizar el calendario: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    echo "<script>window.history.back();</script>";
    exit();
}

?>
