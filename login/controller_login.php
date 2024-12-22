<?php
include('../app/config.php');

session_start();

/* 1. Cargar mensajes de éxito */
$mensajesExito = include('mensajes_exito.php');
$intentosMaximos = 5;
$tiempoBaseEspera = 600;

/* Obtener y validar el email del usuario */
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'];

if (!$email) {
    $_SESSION['mensaje'] = "Correo electrónico inválido.";
    header('Location:' . APP_URL . "/login");
    exit();
}

/* Consulta la tabla de intentos de login */
$sql = "SELECT * FROM login_attempts WHERE email = :email";
$query = $pdo->prepare($sql);
$query->bindParam(':email', $email);
$query->execute();
$registro_intento = $query->fetch(PDO::FETCH_ASSOC);

if ($registro_intento) {
    /* Si el usuario ya tiene registros, calcular el tiempo de espera y verificar intentos */
    $intentosFallidos = $registro_intento['intentos_fallidos'];
    $bloqueoActivado = $registro_intento['bloqueo_activado'];
    $marcaTiempo = strtotime($registro_intento['marca_tiempo']);

    /* Multiplicar el tiempo de espera por cada 5 intentos fallidos */
    $bloques = floor($intentosFallidos / $intentosMaximos);
    $multiplicadorEspera = pow(2, $bloques);
    $tiempoEspera = $tiempoBaseEspera * $multiplicadorEspera;
    $tiempoRestante = $tiempoEspera - (time() - $marcaTiempo);

    /* Bloqueo activo y el tiempo de espera no ha expirado */
    if ($bloqueoActivado && $tiempoRestante > 0) {
        $_SESSION['tiempo_restante'] = round($tiempoRestante / 60);
        $_SESSION['mensaje'] = "Demasiados intentos fallidos. Intente de nuevo en {$_SESSION['tiempo_restante']} minutos.";
        header('Location:' . APP_URL . "/login");
        exit();
    } elseif ($bloqueoActivado && $tiempoRestante <= 0) {
        /* Reinicia el bloqueo si ha expirado el tiempo de espera */
        $sql = "UPDATE login_attempts SET bloqueo_activado = FALSE, intentos = 0, intentos_fallidos = 0 WHERE email = :email";
        $query = $pdo->prepare($sql);
        $query->bindParam(':email', $email);
        $query->execute();
    }
} else {
    /* Crear un nuevo registro en login_attempts si no existe */
    $sql = "INSERT INTO login_attempts (email, intentos, intentos_fallidos, bloqueo_activado) VALUES (:email, 0, 0, FALSE)";
    $query = $pdo->prepare($sql);
    $query->bindParam(':email', $email);
    $query->execute();
}

/* Consulta al usuario en la tabla de usuarios */
$sql = "SELECT * FROM usuarios WHERE email = :email AND estado = '1'";
$query = $pdo->prepare($sql);
$query->bindParam(':email', $email);
$query->execute();
$usuario = $query->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    $password_tabla = $usuario['password'];

    if (password_verify($password, $password_tabla)) {
        $sql = "UPDATE login_attempts SET intentos = 0, intentos_fallidos = 0, bloqueo_activado = FALSE WHERE email = :email";
        $query = $pdo->prepare($sql);
        $query->bindParam(':email', $email);
        $query->execute();

        session_regenerate_id(true);

        /* 2. Seleccionar un mensaje de éxito aleatorio */
        $mensajeAleatorio = $mensajesExito[array_rand($mensajesExito)];
        $_SESSION['mensaje'] = $mensajeAleatorio;

        $_SESSION['icono'] = "success";
        $_SESSION['sesion_email'] = $email;
        $_SESSION['sesion_rol'] = $usuario['rol_id'];
        $_SESSION['sesion_id_usuario'] = $usuario['id_usuario'];
        $_SESSION['sesion_nombre_usuario'] = $usuario['nombres'];

        registrarActividad($pdo, $email, 'exitoso');

        /* Redirige según el rol del usuario */
        if ($usuario['rol_id'] == 6) {
            header('Location:' . APP_URL . "/portal");
        } else {
            header('Location:' . APP_URL . "/admin");
        }
        exit();
    } else {
        /* Incrementa el contador de intentos fallidos en login_attempts */
        $intentos = $registro_intento['intentos'] + 1;
        $intentosFallidos = $registro_intento['intentos_fallidos'] + 1;
        $bloqueoActivado = ($intentosFallidos >= $intentosMaximos);

        $sql = "UPDATE login_attempts SET intentos = :intentos, intentos_fallidos = :intentos_fallidos, bloqueo_activado = :bloqueo_activado, marca_tiempo = NOW() WHERE email = :email";
        $query = $pdo->prepare($sql);
        $query->bindParam(':intentos', $intentos);
        $query->bindParam(':intentos_fallidos', $intentosFallidos);
        $query->bindParam(':bloqueo_activado', $bloqueoActivado, PDO::PARAM_BOOL);
        $query->bindParam(':email', $email);
        $query->execute();

        $_SESSION['intentos_restantes'] = max($intentosMaximos - $intentosFallidos, 0);
        $_SESSION['mensaje'] = "La contraseña es incorrecta, quedan {$_SESSION['intentos_restantes']} intentos.";

        registrarActividad($pdo, $email, 'fallido');

        header('Location:' . APP_URL . "/login");
        exit();
    }
} else {
    /* Si el usuario no existe, cuenta como intento fallido */
    if ($registro_intento) {
        $intentos = $registro_intento['intentos'] + 1;
        $intentosFallidos = $registro_intento['intentos_fallidos'] + 1;
        $bloqueoActivado = ($intentosFallidos >= $intentosMaximos);

        $sql = "UPDATE login_attempts SET intentos = :intentos, intentos_fallidos = :intentos_fallidos, bloqueo_activado = :bloqueo_activado, marca_tiempo = NOW() WHERE email = :email";
        $query = $pdo->prepare($sql);
        $query->bindParam(':intentos', $intentos);
        $query->bindParam(':intentos_fallidos', $intentosFallidos);
        $query->bindParam(':bloqueo_activado', $bloqueoActivado, PDO::PARAM_BOOL);
        $query->bindParam(':email', $email);
        $query->execute();

        $_SESSION['intentos_restantes'] = max($intentosMaximos - $intentosFallidos, 0);
        $_SESSION['mensaje'] = "El usuario no existe o está inactivo, quedan {$_SESSION['intentos_restantes']} intentos.";
    } else {
        $_SESSION['mensaje'] = "El usuario no existe o está inactivo.";
    }
    registrarActividad($pdo, $email, 'fallido');
    header('Location:' . APP_URL . "/login");
    exit();
}

/* Función para registrar actividad de inicio de sesión */
function registrarActividad($pdo, $email, $status)
{
    $sql = "INSERT INTO registro_actividad (email, status, ip, fecha) VALUES (:email, :status, :ip, NOW())";
    $query = $pdo->prepare($sql);
    $query->execute([
        ':email' => $email,
        ':status' => $status,
        ':ip' => $_SERVER['REMOTE_ADDR']
    ]);
}
?>
