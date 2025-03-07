<?php
include ('../app/config.php');
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="/cargaHoraria/public/dist/img/UTM.png" type="image/png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=APP_NAME;?></title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="<?=APP_URL;?>/public/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?=APP_URL;?>/public/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="<?=APP_URL;?>/public/dist/css/adminlte.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <center>
    <img src="/cargaHoraria/public/img/utm2025.png" width="400px" alt="UTM Logo"><br><br>
    </center>
  <div class="login-logo">
    <h3><b><?=APP_NAME;?></b></h3>
  </div>
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Inicio de sesión</p>
      <hr>

      <form action="controller_login.php" method="post">
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Email" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <hr>
        <div class="input-group mb-3">
          <button class="btn btn-primary btn-block" type="submit">Ingresar</button>
        </div>
      </form>

      <?php
      if(isset($_SESSION['mensaje'])){
          $mensaje = $_SESSION['mensaje'];
          ?>
          <script>
            Swal.fire({
                position: "top-center",
                icon: "error",
                title: "<?=$mensaje?>",
                showConfirmButton: false,
                timer: 4000
            });
          </script>
      <?php
          unset($_SESSION['mensaje']);
      }

      /* Muestra intentos restantes si existen */
      if (isset($_SESSION['intentos_restantes']) && $_SESSION['intentos_restantes'] > 0) {
          echo "<p>Intentos restantes: {$_SESSION['intentos_restantes']}</p>";
      }

      /* Muestra tiempo restante si hay un bloqueo activo */
      if (isset($_SESSION['tiempo_restante'])) {
          echo "<p>Tiempo de espera restante: {$_SESSION['tiempo_restante']} minutos</p>";
          unset($_SESSION['tiempo_restante']);
      }
      ?>
    </div>
  </div>
</div>

<script src="<?=APP_URL;?>/public/plugins/jquery/jquery.min.js"></script>
<script src="<?=APP_URL;?>/public/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?=APP_URL;?>/public/dist/js/adminlte.min.js"></script>
</body>
</html>
