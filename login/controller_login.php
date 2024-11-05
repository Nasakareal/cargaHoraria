<?php

include ('../app/config.php');

$email = trim($_POST['email']);
$password = $_POST['password'];

// Consulta al usuario por email y estado activo
$sql = "SELECT * FROM usuarios WHERE email = :email AND estado = '1'";
$query = $pdo->prepare($sql);
$query->bindParam(':email', $email);
$query->execute();

$usuario = $query->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    $password_tabla = $usuario['password'];
    
    // Verifica la contraseña ingresada con el hash almacenado
    if (password_verify($password, $password_tabla)) {
        session_start();
        $_SESSION['mensaje'] = "Bienvenido al sistema";
        $_SESSION['icono'] = "success";
        $_SESSION['sesion_email'] = $email;
        $_SESSION['sesion_rol'] = $usuario['rol_id'];  // Guarda el rol en la sesión
        $_SESSION['sesion_id_usuario'] = $usuario['id_usuario'];  // Guarda el ID del usuario en la sesión
        $_SESSION['sesion_nombre_usuario'] = $usuario['nombres'];  // Guarda el nombre del usuario en la sesión

        // Redirige según el rol del usuario
        if ($usuario['rol_id'] == 1) {
            // Si el rol es 1 (administrador), redirige al panel de administración
            header('Location:'.APP_URL."/admin");
        } else {
            // Si el rol no es de administrador, redirige al panel general
            header('Location:'.APP_URL."/portal"); // Cambia "/portal" según la ruta para usuarios normales
        }
    } else {
        session_start();
        $_SESSION['mensaje'] = "La contraseña es incorrecta, vuelva a intentarlo";
        header('Location:'.APP_URL."/login");
    }
} else {
    session_start();
    $_SESSION['mensaje'] = "El usuario no existe o está inactivo";
    header('Location:'.APP_URL."/login");
}
