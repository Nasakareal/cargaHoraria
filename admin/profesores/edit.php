<?php
include('../../app/config.php');

$teacher_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$teacher_id) {
    echo "ID de usuario inv�lido.";
    exit;
}

include('../../admin/layout/parte1.php');
include('../../app/controllers/profesores/datos_del_profesor.php');
include('../../app/controllers/materias/listado_de_materias.php');

$materia_id = isset($materia_id) ? $materia_id : '';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Modificar profesor: <?= $nombres; ?></h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Llene los datos</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/profesores/update.php" method="post">
                                <!-- A�adir campo oculto para el ID del profesor -->
                                <input type="hidden" name="teacher_id" value="<?= $teacher_id; ?>">

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Nombres del profesor</label>
                                            <input type="text" name="nombres" value="<?= $nombres; ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Materia a impartir</label>
                                            <select name="materia_id" class="form-control" required>
                                                <option value="">Selecciona una materia</option>
                                                <?php
                                                /* Mostrar las materias disponibles */
                                                if (!empty($subjects)) {
                                                    foreach ($subjects as $subject) { ?>
                                                        <option value="<?= $subject['subject_id']; ?>" <?php if ($materia_id == $subject['subject_id']) { ?> selected="selected" <?php } ?>>
                                                            <?= $subject['subject_name']; ?>
                                                        </option>
                                                    <?php }
                                                } else {
                                                    echo "<option value=''>No hay materias disponibles</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Horas Semanales</label>
                                            <input type="number" name="horas_semanales" value="<?= $horas_semanales; ?>" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Actualizar</button>
                                            <a href="<?= APP_URL; ?>/admin/profesores" class="btn btn-secondary">Cancelar</a>
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
