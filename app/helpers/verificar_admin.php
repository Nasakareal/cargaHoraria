<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/config.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['sesion_email']) || $_SESSION['sesion_rol'] != 1) {
    header('Location: ' . APP_URL . '/admin');
    exit();
}
