<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/programas/listado_de_programas.php');
include('../../app/controllers/cuatrimestres/listado_de_cuatrimestres.php');
include('../../app/controllers/turnos/listado_de_turnos.php');
include_once('../../app/middleware.php');

if (!verificarPermiso($_SESSION['sesion_id_usuario'], 'group_create', $pdo)) {
    $_SESSION['mensaje'] = "No tienes permiso para crear un Grupo.";
    $_SESSION['icono'] = "error";
    ?>
    <script>
        history.back();
    </script>
    <?php
    exit;
}

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Agregar un nuevo grupo</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Llene los datos</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/grupos/create.php" method="post">
                                <div class="row">
                                    <!-- Campo para el nombre del grupo -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="group_name">Nombre del grupo</label>
                                            <input type="text" name="grupo" class="form-control" required>
                                        </div>
                                    </div>

                                    <!-- Campo para el programa educativo -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="program_id">Nombre del programa educativo</label>
                                            <select name="programa_id" class="form-control" required>
                                                <option value="">Seleccione un programa</option>
                                                <?php foreach ($programs as $program): ?>
                                                    <option value="<?= $program['program_id']; ?>"><?= htmlspecialchars($program['program_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Campo para el cuatrimestre (term_id) -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="term_id">Cuatrimestre</label>
                                            <select name="term_id" class="form-control" required>
                                                <option value="">Seleccione un cuatrimestre</option>
                                                <?php foreach ($terms as $term): ?>
                                                    <option value="<?= $term['term_id']; ?>"><?= htmlspecialchars($term['term_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Campo para el volumen del grupo -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="volume">Volumen del grupo</label>
                                            <input type="number" name="volume" class="form-control" required>
                                        </div>
                                    </div>

                                    <!-- Campo para el turno -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="turn_id">Turno</label>
                                            <select name="turn_id" class="form-control" required>
                                                <option value="">Seleccione un turno</option>
                                                <?php foreach ($turns as $turn): ?>
                                                    <option value="<?= $turn['shift_id']; ?>"><?= htmlspecialchars($turn['shift_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Registrar</button>
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
