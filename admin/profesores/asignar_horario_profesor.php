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

/* Incluir el archivo que carga las materias disponibles y asignadas */
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
                            <!-- Formulario con método POST y acción que apunta a `update_subjects.php` -->
                            <form action="<?= APP_URL; ?>/app/controllers/profesores/update_subjects.php" method="post">
                                <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($teacher_id); ?>">
                                <input type="hidden" id="grupos_asignados" name="grupos_asignados[]" value=""> <!-- Campo oculto para los grupos -->

                                <!-- Total de horas asignadas -->
                                <div class="row" style="margin-top: 20px;">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="total_hours">Total de horas asignadas</label>
                                            <input type="text" id="total_hours" name="total_hours" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <!-- Grupos disponibles -->
                                    <div class="col-md-5">
                                        <label for="grupos_disponibles">Grupos disponibles</label>
                                        <div class="input-group">
                                            <select id="grupos_disponibles" name="grupos_disponibles" class="form-control">
                                                <?php include('../../app/controllers/relacion_profesor_grupos/grupos_disponibles.php'); ?>
                                            </select>
                                            <button id="confirm_group" class="btn btn-primary" type="button">Seleccionar Grupo</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Materias disponibles y asignadas -->
                                <div class="row">
                                    <!-- Materias disponibles -->
                                    <div class="col-md-5">
                                        <label for="materias_disponibles">Materias disponibles</label>
                                        <select id="materias_disponibles" class="form-control" multiple style="height:200px;">
                                            <?php foreach ($materias_disponibles as $materia): ?>
                                                <option value="<?= htmlspecialchars($materia['subject_id']); ?>">
                                                    <?= htmlspecialchars($materia['subject_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Botones para agregar y quitar materias -->
                                    <div class="col-md-2 text-center" style="margin-top: 80px;">
                                        <button type="button" id="add_subject" class="btn btn-primary btn-block">Agregar &gt;&gt;</button>
                                        <?php if (isset($_SESSION['sesion_rol']) && $_SESSION['sesion_rol'] == 1): ?>
                                            <button type="button" id="remove_subject" class="btn btn-primary btn-block">&lt;&lt; Quitar</button>
                                        <?php else: ?>
                                            <button type="button" id="remove_subject" class="btn btn-primary btn-block" disabled>&lt;&lt; Quitar</button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Materias asignadas -->
                                    <div class="col-md-5">
                                        <label for="materias_asignadas">Materias asignadas</label>
                                        <select id="materias_asignadas" name="materias_asignadas[]" class="form-control" multiple style="height:200px;">
                                            <?php foreach ($materias_asignadas as $materia): ?>
                                                <option value="<?= htmlspecialchars($materia['subject_id']); ?>">
                                                    <?= htmlspecialchars($materia['subject_name']); ?>
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
