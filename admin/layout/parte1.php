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

        
        $foto_sesion_usuario = $usuarioData['foto_perfil'] ?: 'https://cdn-icons-png.flaticon.com/512/74/74472.png';
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
        <a href="<?=APP_URL;?>/admin" class="nav-link"><?=APP_NAME;?></a>
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
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-header" id="notification-header">0 Notificaciones</span>
        <div class="dropdown-divider"></div>
        <div id="notification-list">
            <!-- Aquí se cargarán las notificaciones dinámicamente -->
        </div>
        <div class="dropdown-divider"></div>
        <a href="<?= APP_URL; ?>/admin/reportes/" class="dropdown-item dropdown-footer">Ver todas las Notificaciones</a>
    </div>
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
                countElement.style.display = 'inline'; // Mostrar el contador
            } else {
                countElement.style.display = 'none'; // Ocultar el contador
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
    <a href="<?=APP_URL;?>/admin" class="brand-link" style="background-color: #008080">
      <img src="https://ut-morelia.edu.mx/wp-content/uploads/2022/05/Logo-UTM-Claro.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8; width: 50px; height: 50px; object-fit: contain;">
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
          <a href="<?= APP_URL; ?>/admin/usuarios/show.php?id=<?= $_SESSION['sesion_id_usuario']; ?>" class="d-block"><?= $nombre_sesion_usuario; ?></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          

          <li class="nav-item">
            <a href="#" class="nav-link" style= "background-color: #3688f4">
              <i class="nav-icon fas"><i class="bi bi-person-workspace"></i></i>
              <p>
                Profesores
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?=APP_URL;?>/admin/profesores" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Profesores</p>
                </a>
              </li>
            </ul>

              <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/horarios_profesores" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Horario de Profesores</p>
                </a>
              </li>
            </ul>

          </li>

          <li class="nav-item">
            <a href="#" class="nav-link" style= "background-color: #3688f4">
              <i class="nav-icon fas"><i class="bi bi-journal-bookmark-fill"></i></i>
              <p>
                Materias
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?=APP_URL;?>/admin/materias" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Materias</p>
                </a>
              </li>
            </ul>
          </li>

            <li class="nav-item">
            <a href="#" class="nav-link" style= "background-color: #3688f4">
              <i class="nav-icon fas"><i class="bi bi-backpack2"></i></i>
              <p>
                Programas
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/programas" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Programas</p>
                </a>
              </li>
            </ul>

            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?=APP_URL;?>/admin/relacion_materia_cuatrimestre_programa" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado relacionado de Materias, Programas, y cuatrimestre</p>
                </a>
              </li>
            </ul>

          </li>

            <li class="nav-item">
            <a href="#" class="nav-link" style= "background-color: #3688f4">
              <i class="nav-icon fas"><i class="bi bi-boxes"></i></i>
              <p>
                Grupos
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/grupos" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Grupos</p>
                </a>
              </li>
            </ul>

             <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/horarios_grupos" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Horarios de Grupos</p>
                </a>
              </li>
            </ul>

            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/intercambios" class="nav-link">
                <i class="bi bi-arrow-left-right"></i>
                  <p>Intercambiar Horarios</p>
                </a>
              </li>
            </ul>

          </li>
            
          <li class="nav-item">
            <a href="#" class="nav-link" style= "background-color: #3688f4">
              <i class="nav-icon fas"><i class="bi bi-buildings"></i></i>
              <p>
                Salones
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/salones" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Salones</p>
                </a>
              </li>
            </ul>

            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/autoSalones" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Autoasignación de salones</p>
                </a>
              </li>
            </ul>

            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/laboratorios" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Laboratorios</p>
                </a>
              </li>
            </ul>

            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/asignacion_manual/" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Asignar Horario de Laboratorios</p>
                </a>
              </li>
            </ul>

          </li>

          
            <li class="nav-item">
            <a href="#" class="nav-link" style= "background-color: #3688f4">
              <i class="nav-icon fas"><i class="bi bi-gear"></i></i>
              <p>
                Configuraciones
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/configuraciones" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Configuraciones</p>
                </a>
              </li>
            </ul>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/roles" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Roles</p>
                </a>
              </li>
            </ul>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/usuarios" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Usuarios</p>
                </a>
              </li>
            </ul>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/admin/configuraciones/calendarios" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Calendario</p>
                </a>
              </li>
            </ul>

          </li>



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

