<?php

include ('../../../app/config.php');

$id_rol = $_POST['id_rol'];

$sql_usuarios = "SELECT * FROM usuarios WHERE estado = '1' AND rol_id = '$id_rol' ";
$query_usuarios = $pdo->prepare($sql_usuarios);
$query_usuarios->execute();
$usuarios = $query_usuarios->fetchAll(PDO::FETCH_ASSOC);
$contador = 0;

foreach ($usuarios AS $usuario){
    $contador++;

}
if ($contador>0){
    session_start();
        $_SESSION['mensaje'] = "Existe este rol en otro usuario, no se puede eliminar";
        $_SESSION['icono'] = "error";
        header('Location:'.APP_URL."/admin/roles");
}else{
    $sentencia = $pdo->prepare("DELETE FROM roles WHERE id_rol=:id_rol ");

$sentencia->bindParam('id_rol', $id_rol);


    if($sentencia->execute()){
        session_start();
        $_SESSION['mensaje'] = "Se ha eliminado el rol";
        $_SESSION['icono'] = "success";
        header('Location:'.APP_URL."/admin/roles");
    }else{
        session_start();
        $_SESSION['mensaje'] = "No se ha podido eliminar el rol, comuniquese con el area de IT";
        $_SESSION['icono'] = "error";
        header('Location:'.APP_URL."/admin/roles");
    }

}