<?php
include('../../app/config.php');

/* Obtener el ID del profesor de la URL */
$teacher_id = filter_input(INPUT_GET, 'teacher_id', FILTER_VALIDATE_INT);

/* Verificar si el ID es válido */
if (!$teacher_id) {
    echo "ID de profesor inválido.";
    exit;
}

include('../../admin/layout/parte1.php');
include('../../app/controllers/profesores/datos_del_profesor.php');
include('../../app/controllers/programas/listado_de_programas.php');
include('../../app/controllers/cuatrimestres/listado_de_cuatrimestres.php');
include('../../app/controllers/relacion_profesor_materias/listado_de_relacion.php');
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Modificar profesor: <?= htmlspecialchars($nombres); ?></h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Llene los datos</h3>
                        </div>
                        <div class="card-body">
                            <!-- Formulario con método POST y acción que apunta a `update.php` -->
                            <form action="<?= APP_URL; ?>/app/controllers/profesores/update_subjects.php" method="post">
                                <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($teacher_id); ?>">

                                

                                <!-- Total de horas asignadas -->
                                <div class="row" style="margin-top: 20px;">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="total_hours">Total de horas asignadas</label>
                                            <input type="text" id="total_hours" name="total_hours" class="form-control" value="0" readonly>
                                        </div>
                                    </div>

<!-- Grupos disponibles -->
<div class="col-md-5">
    <label for="grupos_disponibles">Grupos disponibles</label>
    <div class="input-group">
        <select id="grupos_disponibles" name="grupos_disponibles" class="form-control">
            <?php
            include('../../app/controllers/relacion_profesor_grupos/grupos_disponibles.php');
            ?>
        </select>
        <button id="confirm_group" class="btn btn-primary" type="button">Seleccionar Grupo</button>
    </div>
</div>





                                </div>

                                <!-- Grupos disponibles y asignados -->
                                <div class="row">
                                    
                                </div>

                                <!-- Materias disponibles y asignadas -->
                                <div class="row">
                                    <!-- Materias disponibles -->
                                    <div class="col-md-5">
                                        <label for="">Materias disponibles</label>
                                        <select id="materias_disponibles" class="form-control" multiple style="height:200px;">
                                            <?php foreach ($materias_disponibles as $materia): ?>
                                                <?php if (!in_array($materia['subject_id'], array_column($materias_asignadas, 'subject_id'))): ?>
                                                    <option value="<?= $materia['subject_id']; ?>" data-hours="<?= isset($materia['weekly_hours']) ? $materia['weekly_hours'] : 0; ?>">
                                                        <?= htmlspecialchars($materia['subject_name']); ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Botones para agregar y quitar materias -->
                                    <div class="col-md-2 text-center" style="margin-top: 80px;">
                                        <button type="button" id="add_subject" class="btn btn-primary btn-block">Agregar &gt;&gt;</button>
                                        <button type="button" id="remove_subject" class="btn btn-primary btn-block">&lt;&lt; Quitar</button>
                                    </div>

                                    <!-- Materias asignadas -->
                                    <div class="col-md-5">
                                        <label for="">Materias asignadas</label>
                                        <select id="materias_asignadas" name="materias_asignadas[]" class="form-control" multiple style="height:200px;">
                                            <?php foreach ($materias_asignadas as $materia_asignada): ?>
                                                <option value="<?= $materia_asignada['subject_id']; ?>" data-hours="<?= isset($materia_asignada['weekly_hours']) ? $materia_asignada['weekly_hours'] : 0; ?>" selected>
                                                    <?= htmlspecialchars($materia_asignada['subject_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                
                                <!-- Botón de actualización -->
                                <div class="row" style="margin-top:20px;">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Actualizar</button>
                                        <a href="<?= APP_URL; ?>/admin/profesores" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>

<!-- jQuery y el archivo JavaScript externo -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../../app/controllers/js/asignar_materias_grupos.js"></script>
