<?php
<<<<<<< HEAD
include ('../../app/config.php'); 
=======
include('../../app/config.php');
>>>>>>> 09dfda8 (descagada)

$teacher_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$teacher_id) {
    echo "ID de usuario inválido.";
    exit;
}

<<<<<<< HEAD
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/profesores/datos_del_profesor.php');
include ('../../app/controllers/materias/listado_de_materias.php'); 

$materia_id = isset($materia_id) ? $materia_id : '';
=======
include('../../admin/layout/parte1.php');
include('../../app/controllers/profesores/datos_del_profesor.php');
include('../../app/controllers/materias/listado_de_materias.php');
>>>>>>> 09dfda8 (descagada)
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
<<<<<<< HEAD
                <h1>Modificar profesor: <?=$nombres;?></h1>
=======
                <h1>Modificar profesor: <?= $nombres; ?></h1>
>>>>>>> 09dfda8 (descagada)
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Llene los datos</h3>
                        </div>
                        <div class="card-body">
<<<<<<< HEAD
                            <form action="<?= APP_URL;?>/app/controllers/profesores/update.php" method="post">
                                <!-- Añadir campo oculto para el ID del profesor -->
                                <input type="hidden" name="teacher_id" value="<?=$teacher_id;?>">
=======
                            <form action="<?= APP_URL; ?>/app/controllers/profesores/update.php" method="post">
                                <!-- Añadir campo oculto para el ID del profesor -->
                                <input type="hidden" name="teacher_id" value="<?= $teacher_id; ?>">
>>>>>>> 09dfda8 (descagada)

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Nombres del profesor</label>
<<<<<<< HEAD
                                            <input type="text" name="nombres" value="<?=$nombres;?>" class="form-control" required>
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
                                                        <option value="<?=$subject['subject_id'];?>" <?php if($materia_id == $subject['subject_id']) { ?> selected="selected" <?php } ?>>
                                                            <?=$subject['subject_name'];?>
                                                        </option>
                                                    <?php }
=======
                                            <input type="text" name="nombres" value="<?= $nombres; ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="">Materias a impartir</label>
                                            <select name="materia_ids[]" class="form-control" multiple required>
                                                <?php
                                                /* Mostrar las materias disponibles */
                                                if (!empty($subjects)) {
                                                    foreach ($subjects as $subject) {
                                                        $selected = in_array($subject['subject_id'], array_column($materias, 'subject_id')) ? 'selected' : '';
                                                        ?>
                                                        <option value="<?= $subject['subject_id']; ?>" <?= $selected; ?>>
                                                            <?= $subject['subject_name']; ?>
                                                        </option>
                                                <?php
                                                    }
>>>>>>> 09dfda8 (descagada)
                                                } else {
                                                    echo "<option value=''>No hay materias disponibles</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
<<<<<<< HEAD

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Horas Semanales</label>
                                            <input type="number" name="horas_semanales" value="<?=$horas_semanales;?>" class="form-control" required>
=======
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Horas Semanales por Materia</label>
                                            <?php if (!empty($materias)) {
                                                foreach ($materias as $materia) { ?>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text"><?= $materia['subject_name']; ?></span>
                                                        <input type="number" name="horas_semanales[<?= $materia['subject_id']; ?>]" value="<?= $materia['weekly_hours']; ?>" class="form-control" required>
                                                    </div>
                                                <?php }
                                            } ?>
>>>>>>> 09dfda8 (descagada)
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Actualizar</button>
<<<<<<< HEAD
                                            <a href="<?=APP_URL;?>/admin/profesores" class="btn btn-secondary">Cancelar</a>
=======
                                            <a href="<?= APP_URL; ?>/admin/profesores" class="btn btn-secondary">Cancelar</a>
>>>>>>> 09dfda8 (descagada)
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

<<<<<<< HEAD
<?php 
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
=======
<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
>>>>>>> 09dfda8 (descagada)
?>
