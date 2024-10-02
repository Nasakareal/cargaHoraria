<?php
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/materias/listado_de_materias.php');
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <br>
    <div class="content">
      <div class="container">
        <div class="row">
          <h1>Añadir un nuevo profesor</h1>
        </div>
        <div class="row">
        
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Llene los datos</h3>
                    </div>
                    <div class="card-body">
                    <form action="<?= APP_URL;?>/app/controllers/profesores/create.php" method="post">
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">

                          <label for="">Nombres del profesor</label>
                          <input type="text" name="teacher_name" class="form-control" required>
                        </div>
                      </div>
                    </div>
                    <hr>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <button type="submit" class="btn btn-primary">Registrar</button>
                          <a href="<?=APP_URL;?>/admin/profesores" class="btn btn-secondary">Cancelar</a>
                        </div>
                      </div>
                    </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

<?php 
  
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
  
?>