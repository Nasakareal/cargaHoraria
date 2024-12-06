<?php

include ('../app/config.php');

/* Datos del usuario */
$nombres = 'Mario Bautista';
$rol_id = 1;
$email = 'admin@admin.com';
$password = 'ansq98';
$estado = '1';

/* Genera el hash de la contraseña */
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

/* Inserta el usuario en la base de datos */
$sql = "INSERT INTO usuarios (nombres, rol_id, email, password, fyh_creacion, estado) 
        VALUES (:nombres, :rol_id, :email, :password, NOW(), :estado)";

$query = $pdo->prepare($sql);
$query->bindParam(':nombres', $nombres);
$query->bindParam(':rol_id', $rol_id);
$query->bindParam(':email', $email);
$query->bindParam(':password', $hashed_password);
$query->bindParam(':estado', $estado);
$query->execute();

echo "Usuario insertado con éxito.";
