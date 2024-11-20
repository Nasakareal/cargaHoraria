<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Agregar un nuevo laboratorio</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Llene los datos</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/laboratorios/create.php" method="post">
                                <div class="row">
                                    <!-- Campo para el nombre del laboratorio -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="lab_name">Nombre del laboratorio</label>
                                            <input type="text" id="lab_name" name="lab_name" class="form-control" required>
                                        </div>
                                    </div>

                                    <!-- Campo para la descripción -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description">Descripción</label>
                                            <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Registrar</button>
                                            <a href="<?= APP_URL; ?>/admin/laboratorios" class="btn btn-secondary">Cancelar</a>
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
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>
