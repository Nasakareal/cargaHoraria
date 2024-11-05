<?php
session_start();

/* Verifica si el usuario ha iniciado sesión */
if (isset($_SESSION['sesion_email'])) {
    $email_sesion = $_SESSION['sesion_email'];
    $rol_id = $_SESSION['sesion_rol'];
    $query_sesion = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND estado = '1'");
    $query_sesion->bindParam(':email', $email_sesion);
    $query_sesion->execute();

    $datos_sesion_usuarios = $query_sesion->fetchAll(PDO::FETCH_ASSOC);
    foreach ($datos_sesion_usuarios as $datos_sesion_usuario) {
        $nombre_sesion_usuario = $datos_sesion_usuario['nombres'];
    }
} else {
    /* Si no está autenticado, redirige al login */
    header('Location:' . APP_URL . "/login");
    exit();
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
        <a href="<?=APP_URL;?>/portal" class="nav-link"><?=APP_NAME;?></a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      
      

      <!-- Notifications Dropdown Menu -->
      
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
          
        </a>
        
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: #008080">
    
    <!-- Brand Logo -->
    <a href="<?=APP_URL;?>/portal" class="brand-link" style="background-color: #008080">
      <img src="https://ut-morelia.edu.mx/wp-content/uploads/2022/05/Logo-UTM-Claro.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8; width: 50px; height: 50px; object-fit: contain;">
      <span class="brand-text font-weight-light">Carga Horaria</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar" style="background-color: #008080">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://cdn-icons-png.flaticon.com/512/74/74472.png" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?=$nombre_sesion_usuario;?></a>
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
                <a href="<?=APP_URL;?>/portal/profesores" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Profesores</p>
                </a>
              </li>
            </ul>

              <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/portal/horarios_profesores" class="nav-link">
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
                <a href="<?=APP_URL;?>/portal/materias" class="nav-link">
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
                <a href="<?= APP_URL; ?>/portal/programas" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Programas</p>
                </a>
              </li>
            </ul>

            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?=APP_URL;?>/portal/relacion_materia_cuatrimestre_programa" class="nav-link">
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
                <a href="<?= APP_URL; ?>/portal/grupos" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Grupos</p>
                </a>
              </li>
            </ul>

             <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/portal/horarios_grupos" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Horarios de Grupos</p>
                </a>
              </li>
            </ul>


          </li>
            
            <li class="nav-item">
            <a href="#" class="nav-link" style= "background-color: #3688f4">
              <i class="nav-icon fas"><i class="bi bi-calendar3"></i></i>
              <p>
                Cuatrimestres
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/portal/cuatrimestres" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Cuatrimestres</p>
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
                <a href="<?= APP_URL; ?>/portal/salones" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Salones</p>
                </a>
              </li>
            </ul>

            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/portal/autoSalones" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Autoasignación de salones</p>
                </a>
              </li>
            </ul>

            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= APP_URL; ?>/portal/laboratorios" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listado de Laboratorios</p>
                </a>
              </li>
            </ul>

          </li>

          
            

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



        </ul>
      </nav>
      <!-- /.sidebar-menu -->
       
    </div>
    <!-- /.sidebar -->
     
  </aside>