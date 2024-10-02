<?php

include ('../app/config.php');

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios WHERE email = '$email' AND estado = '1' ";
$query = $pdo->prepare($sql);
$query->execute();

$usuarios = $query->fetchAll(PDO::FETCH_ASSOC);
//print_r($usuarios);

$contador = 0;

foreach($usuarios as $usuario){
    $password_tabla = $usuario['password'];
    $contador++;
}

if(($contador > 0) && ($password === $password_tabla)) {
    echo "Los datos son correctos";
    session_start();
    $_SESSION['mensaje'] = "Bienvenido al sistema";
    $_SESSION['icono'] = "success";
    $_SESSION['sesion_email'] = $email;
    header('Location:'.APP_URL."/admin");
}else{
    echo "Los datos son incorrectos";
    session_start();
    $_SESSION['mensaje'] = "Los datos introducidos son incorrectos, vuelva a intentarlo";
    header('Location:'.APP_URL."/login");
}