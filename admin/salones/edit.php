<?php
$classroom_id = $_GET['id'];

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/salones/datos_del_salon.php');
include_once('../../app/middleware.php');

if (!verificarPermiso($_SESSION['sesion_id_usuario'], 'classroom_edit', $pdo)) {
    $_SESSION['mensaje'] = "No tienes permiso para editar un aula.";
    $_SESSION['icono'] = "error";
    ?>
    <script>
        history.back();
    </script>
    <?php
    exit;
}

$classroom_name = isset($classroom_name) ? $classroom_name : "Salón no encontrado";
$capacity = isset($capacity) ? $capacity : "Capacidad no encontrada";
$building = isset($building) ? $building : "Edificio no encontrado";
$floor = isset($floor) ? $floor : "Planta no encontrada";

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Editar Sal&oacute;n: <?= htmlspecialchars($classroom_name); ?></h1>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Datos registrados</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/salones/update.php" method="post">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Nombre del sal&oacute;n</label>
                                            <input type="text" name="classroom_id" value="<?= htmlspecialchars($classroom_id); ?>" hidden>
                                            <input type="text" class="form-control" name="classroom_name" value="<?= htmlspecialchars($classroom_name); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Capacidad</label>
                                            <input type="number" class="form-control" name="capacity" value="<?= htmlspecialchars($capacity); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Edificio</label>
                                            <input type="text" class="form-control" name="building" value="<?= htmlspecialchars($building); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Planta</label>
                                            <select name="floor" class="form-control" required>
                                                <option value="ALTA" <?= ($floor == 'ALTA') ? 'selected' : ''; ?>>ALTA</option>
                                                <option value="BAJA" <?= ($floor == 'BAJA') ? 'selected' : ''; ?>>BAJA</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                            <a href="<?= APP_URL; ?>/admin/salones" class="btn btn-secondary">Cancelar</a>
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
