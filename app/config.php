<?php
define ('SERVIDOR','localhost');
define ('USUARIO','jaggerjack');
define ('PASSWORD','IAMTHELobosolitario117$');
define ('BD','cargaHoraria');

define ('APP_NAME','Sistema De Carga Horaria');
define ('APP_URL','http://localhost/cargaHoraria');
define ('KEY_API_MAPS','');

$servidor = "mysql:dbname=" . BD . ";host=" . SERVIDOR . ";charset=utf8mb4";

try{
    $pdo = new PDO($servidor, USUARIO, PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    
}catch (PDOException $e) {
    print_r($e);
    echo "Error de conexión con la base de datos";
}

date_default_timezone_set("America/Mexico_City");

$fechaHora = date('Y-m-d H:i:s');
$fecha_actual = date('Y-m-d');
$dia_actual = date('d');
$mes_actual = date('m');
$ano_actual = date('Y');

$estado_de_registro = '1';