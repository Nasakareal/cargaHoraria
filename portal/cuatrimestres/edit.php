<?php

$term_id = $_GET['id'];

include('../../app/config.php');
include('../../layout/parte1.php');
include('../../app/controllers/cuatrimestres/datos_del_cuatrimestre.php');
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <br>
    <div class="content">
      <div class="container">
        <div class="row">
          <h1>Editar el cuatrimestre: <?= $term_id; ?></h1>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">Datos registrados</h3>
                    </div>
                    <div class="card-body">
                    <form action="<?= APP_URL; ?>/app/controllers/cuatrimestres/update.php" method="post">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="">Nombre del cuatrimestre</label>
                          <input type="text" name="term_id" value="<?= $term_id; ?>" hidden>
                          <input type="text" class="form-control" name="term_name" value="<?= $terms_name; ?>">
                        </div>
                      </div>
                    </div>
                    <hr>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                          <a href="<?= APP_URL; ?>/admin/cuatrimestres" class="btn btn-secondary">Cancelar</a>
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

include('../../layout/parte2.php');
include('../../layout/mensajes.php');

?>