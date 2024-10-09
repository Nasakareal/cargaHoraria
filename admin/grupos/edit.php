<?php
$group_id = $_GET['id'];

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/grupos/datos_del_grupo.php');
include('../../app/controllers/programas/listado_de_programas.php');
include('../../app/controllers/turnos/listado_de_turnos.php'); // Asegúrate de incluir la lista de turnos

$group_name = isset($group_name) ? $group_name : "Grupo no encontrado";
$program_id = isset($program_id) ? $program_id : null;
$period = isset($period) ? $period : "Periodo no encontrado";
$year = isset($year) ? $year : "Año no encontrado";
$volumen_grupo = isset($volumen_grupo) ? $volumen_grupo : "N/A";
$turn_id = isset($turn_id) ? $turn_id : null; // Agregado para el turno

$sql_periods = "SELECT DISTINCT period FROM `groups` WHERE estado = '1'";
$query_periods = $pdo->prepare($sql_periods);
$query_periods->execute();
$periods = $query_periods->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Editar Grupo: <?= htmlspecialchars($group_name); ?></h1>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Datos registrados</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/grupos/update.php" method="post">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Nombre del grupo</label>
                                            <input type="text" name="group_id" value="<?= htmlspecialchars($group_id); ?>" hidden>
                                            <input type="text" class="form-control" name="group_name" value="<?= htmlspecialchars($group_name); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Programa Educativo</label>
                                            <select name="program_id" class="form-control" required>
                                                <option value="">Seleccione un programa</option>
                                                <?php foreach ($programs as $program): ?>
                                                    <option value="<?= $program['program_id']; ?>" <?= ($program['program_id'] == $program_id) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($program['programa'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Periodo</label>
                                            <select name="period" class="form-control" required>
                                                <option value="">Seleccione un periodo</option>
                                                <?php foreach ($periods as $p): ?>
                                                    <option value="<?= htmlspecialchars($p['period'], ENT_QUOTES, 'UTF-8'); ?>" <?= ($p['period'] == $period) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($p['period'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Año</label>
                                            <input type="number" class="form-control" name="year" value="<?= htmlspecialchars($year); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Volumen del grupo</label>
                                            <input type="number" class="form-control" name="volume" value="<?= htmlspecialchars($volumen_grupo); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Turno</label>
                                            <select name="turn_id" class="form-control" required>
                                                <option value="">Seleccione un turno</option>
                                                <?php foreach ($turns as $turn): ?> <!-- Asegúrate de usar $turns -->
                                                    <option value="<?= $turn['shift_id']; ?>" <?= ($turn['shift_id'] == $turn_id) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($turn['shift_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                            <a href="<?= APP_URL; ?>/admin/grupos" class="btn btn-secondary">Cancelar</a>
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
