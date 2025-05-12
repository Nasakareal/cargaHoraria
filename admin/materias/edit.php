<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

$subject_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$subject_id) {
    echo "ID de materia inválido.";
    exit;
}

include('../../app/controllers/materias/datos_de_materias.php');
include('../../app/controllers/programas/listado_de_programas.php');
include('../../app/controllers/cuatrimestres/listado_de_cuatrimestres.php');

include_once('../../app/middleware.php');

if (!verificarPermiso($_SESSION['sesion_id_usuario'], 'subject_edit', $pdo)) {
    $_SESSION['mensaje'] = "No tienes permiso para editar un Grupo.";
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
                <h1>Modificar materia: <?= htmlspecialchars($subject_name); ?></h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Llene los datos</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/materias/update.php" method="post">
                                <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subject_id); ?>">

                                <div class="row">
                                    <!-- Nombre de la materia -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="subject_name">Nombre de la materia</label>
                                            <input type="text" name="subject_name" value="<?= htmlspecialchars($subject_name); ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    <!-- Horas semanales -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="weekly_hours">Horas Semanales</label>
                                            <input type="number" name="weekly_hours" value="<?= htmlspecialchars($weekly_hours); ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    
                                    <!-- Máximo de Horas consecutivas -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="hours_consecutive">Máximo de Horas Consecutivas</label>
                                            <input type="number" name="max_consecutive_class_hours" value="<?= htmlspecialchars($max_consecutive_class_hours); ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    <!-- Programa -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="program_id">Programa</label>
                                            <select name="program_id" class="form-control" required>
                                                <?php foreach ($programs as $program): ?>
                                                    <option value="<?= htmlspecialchars($program['program_id']); ?>" <?= $program['program_id'] == $materia['program_id'] ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($program['program_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Cuatrimestre -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="term_id">Cuatrimestre</label>
                                            <select name="term_id" class="form-control" required>
                                                <?php foreach ($terms as $term): ?>
                                                    <option value="<?= htmlspecialchars($term['term_id']); ?>" <?= $term['term_id'] == $materia['term_id'] ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($term['term_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Unidades -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Unidades</label>
                                            <input type="number" name="unidades" value="<?= htmlspecialchars($unidades); ?>" class="form-control" required>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Actualizar</button>
                                        <a href="<?= APP_URL; ?>/admin/materias" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </div><!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>
