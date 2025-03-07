<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/cargaHoraria/app/middleware.php');


$email_sesion = $_SESSION['sesion_email'];
$rol_id = $_SESSION['sesion_rol'];
$nombre_sesion_usuario = $_SESSION['sesion_nombre_usuario'] ?? null;
$foto_sesion_usuario = $_SESSION['sesion_foto_usuario'] ?? null;

if (!$nombre_sesion_usuario || !$foto_sesion_usuario) {
    $query = $pdo->prepare("SELECT nombres, foto_perfil FROM usuarios WHERE email = :email AND estado = '1'");
    $query->bindParam(':email', $email_sesion);
    $query->execute();
    $usuarioData = $query->fetch(PDO::FETCH_ASSOC);

    if ($usuarioData) {
        $nombre_sesion_usuario = $usuarioData['nombres'];
        $_SESSION['sesion_nombre_usuario'] = $nombre_sesion_usuario;

        
        $foto_sesion_usuario = $usuarioData['foto_perfil'] ?: '';
        $_SESSION['sesion_foto_usuario'] = $foto_sesion_usuario;
    } else {
        header('Location: ' . APP_URL . '/login');
        exit();
    }
}

?>
<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="es">
<head>

  <link rel="icon" href="/cargaHoraria/public/dist/img/UTM.png" type="image/png">


  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=APP_NAME;?></title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="<?=APP_URL;?>/public/plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?=APP_URL;?>/public/dist/css/adminlte.min.css">
  <!-- Sweetalert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!--  Iconos de Bootstrap-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- FullCalendar CSS -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
  <!-- FullCalendar JS -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>



  <!-- Datatables -->
<link rel="stylesheet" href="<?=APP_URL;?>/public/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?=APP_URL;?>/public/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?=APP_URL;?>/public/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">


</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="<?= APP_URL; ?>/portal/" class="nav-link"><?=APP_NAME;?></a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      
      

<!-- Mostrar notificaciones solo si el rol es ADMINISTRADOR -->
<?php if ($rol_id == 1): ?>
<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-bell"></i>
        <span class="badge badge-warning navbar-badge" id="notification-count" style="display: none;">0</span>
    </a>
</li>
<?php endif; ?>

<!-- Script para cargar notificaciones -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    fetch('/cargaHoraria/app/reports/get_reports.php')
        .then(response => response.json())
        .then(data => {
            const countElement = document.getElementById('notification-count');
            const headerElement = document.getElementById('notification-header');
            const listElement = document.getElementById('notification-list');

            /* Actualizar conteo de notificaciones */
            if (data.length > 0) {
                countElement.textContent = data.length;
                countElement.style.display = 'inline';
            } else {
                countElement.style.display = 'none';
            }

            headerElement.textContent = `${data.length} Notificaciones`;

            /* Generar lista de notificaciones */
            let notificationHTML = '';
            data.forEach(report => {
                notificationHTML += `
                <a href="/cargaHoraria/admin/reportes/show.php?id=${report.report_id}" class="dropdown-item">
                  <i class="fas fa-file mr-2"></i> ${report.report_message}
                  <span class="float-right text-muted text-sm">${new Date(report.created_at).toLocaleString()}</span>
                </a>
                <div class="dropdown-divider"></div>
                `;
            });

            /* Insertar notificaciones en la lista */
            listElement.innerHTML = notificationHTML || '<p class="text-center text-muted">No hay notificaciones</p>';
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            const listElement = document.getElementById('notification-list');
            listElement.innerHTML = '<p class="text-center text-danger">Error al cargar notificaciones</p>';
        });
});
</script>


      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="bi bi-chat-right-dots"></i>
          
        </a>
        
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: #008080">
    
    <!-- Brand Logo --> 
        <a href="<?=APP_URL;?>/admin" class="brand-link" style="background-color: #008080; padding: 10px; border-radius: 8px;">
          <img src="<?= APP_URL; ?>/public/dist/img/UTM.png" 
               alt="AdminLTE Logo" 
               class="brand-image img-circle elevation-3" 
               style="opacity: .8; width: 50px; height: 50px; object-fit: contain; background-color: white; padding: 5px; border-radius: 50%;">
          <span class="brand-text font-weight-light">Carga Horaria</span>
        </a>


    <!-- Sidebar -->
    <div class="sidebar" style="background-color: #008080">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
            <img src="<?= $_SESSION['sesion_foto_usuario'] ?: 'https://cdn-icons-png.flaticon.com/512/74/74472.png'; ?>" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?= $nombre_sesion_usuario; ?></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          

            



          <br>
          <li class="nav-item">
            <a href="<?=APP_URL;?>/login/logout.php" class="nav-link" style= "background-color: #f44336;color: black">
              <i class="nav-icon fas"><i class="bi bi-door-open"></i></i>
              <p>
                Cerrar Sesión
              </p>
            </a>
          </li>
<br>



        </ul>

      </nav>
      <!-- /.sidebar-menu -->
       
    </div>
    <!-- /.sidebar -->
     
  </aside>

