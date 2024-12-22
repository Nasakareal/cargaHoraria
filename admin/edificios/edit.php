<?php
$building_id = $_GET['id'];

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/edificios/datos_del_edificio.php');

$sql_areas = "SELECT DISTINCT area FROM programs ORDER BY area ASC";
$query_areas = $pdo->prepare($sql_areas);
$query_areas->execute();
$available_areas = $query_areas->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Editar Edificio: <?= htmlspecialchars($building_name); ?></h1>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Datos registrados</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/edificios/update.php" method="post">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Nombre del Edificio</label>
                                            <input type="text" name="building_id" value="<?= htmlspecialchars($building_id); ?>" hidden>
                                            <input type="text" class="form-control" name="building_name" value="<?= htmlspecialchars($building_name); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Planta Alta</label>
                                            <select name="planta_alta" class="form-control" required>
                                                <option value="1" <?= ($planta_alta == 'Sí') ? 'selected' : ''; ?>>Sí</option>
                                                <option value="0" <?= ($planta_alta == 'No') ? 'selected' : ''; ?>>No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Planta Baja</label>
                                            <select name="planta_baja" class="form-control" required>
                                                <option value="1" <?= ($planta_baja == 'Sí') ? 'selected' : ''; ?>>Sí</option>
                                                <option value="0" <?= ($planta_baja == 'No') ? 'selected' : ''; ?>>No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Áreas</label>
                                            <div class="form-control" style="height: auto; padding: 10px;">
                                                <?php foreach ($available_areas as $area): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="areas[]" value="<?= htmlspecialchars($area['area']); ?>" id="area_<?= htmlspecialchars($area['area']); ?>" 
                                                        <?php if (strpos($areas, $area['area']) !== false) echo 'checked'; ?>>
                                                        <label class="form-check-label" for="area_<?= htmlspecialchars($area['area']); ?>">
                                                            <?= htmlspecialchars($area['area']); ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                            <a href="<?= APP_URL; ?>/admin/edificios" class="btn btn-secondary">Cancelar</a>
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
