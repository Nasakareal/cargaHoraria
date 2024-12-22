<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/programas/datos_del_programa.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Editar el programa: <?= $nombre_programa; ?></h1>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Datos registrados</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/programas/update.php" method="post">

                            <!-- Campo para el Nombre del Programa -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Nombre del programa</label>
                                            <input type="hidden" name="program_id" value="<?= $program_id; ?>">
                                            <input type="text" class="form-control" name="program_name" value="<?= $nombre_programa; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <!-- Campo para el Area -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="area">Área</label>
                                            <input type="text" class="form-control" name="area" id="area" placeholder="Ingrese el área" value="<?= isset($area) ? $area : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>


                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                            <a href="<?= APP_URL; ?>/admin/programas" class="btn btn-secondary">Cancelar</a>
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
