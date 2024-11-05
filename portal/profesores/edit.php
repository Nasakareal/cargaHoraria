<?php
include('../../app/config.php');


$teacher_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$teacher_id) {
    echo "ID de profesor inválido.";
    exit;
}

include('../../layout/parte1.php');
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
                            <form action="<?= APP_URL; ?>/app/controllers/profesores/update.php" method="post">
                                <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($teacher_id); ?>">

                                <!-- Datos del profesor -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Nombres del profesor</label>
                                            <input type="text" name="nombres" value="<?= htmlspecialchars($nombres); ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    <!-- Programa -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Programa</label>
                                            <select name="programa_id" id="programa_id" class="form-control" required>
                                                <option value="">Seleccione un programa</option>
                                                <?php foreach ($programs as $program): ?>
                                                    <option value="<?= $program['program_id']; ?>" <?= ($programa_id == $program['program_id']) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($program['program_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Cuatrimestre -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Cuatrimestre</label>
                                            <select name="cuatrimestre_id" id="cuatrimestre_id" class="form-control" required>
                                                <option value="">Seleccione un cuatrimestre</option>
                                                <?php foreach ($terms as $term): ?>
                                                    <option value="<?= $term['term_id']; ?>" <?= ($cuatrimestre_id == $term['term_id']) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($term['term_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
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

                                <!-- Contador de horas -->
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <h4>Horas semanales totales: <span id="total_hours">0</span></h4>
                                    </div>
                                </div>

                                <!-- Grupos disponibles y asignados -->
                                <div class="row">
                                    <!-- Grupos disponibles -->
                                    <div class="col-md-5">
                                        <label for="">Grupos disponibles</label>
                                        <select id="grupos_disponibles" class="form-control" multiple style="height:200px;">
                                            <?php foreach ($grupos_disponibles as $grupo): ?>
                                                <?php if (!in_array($grupo['group_id'], array_column($grupos_asignados, 'group_id'))): ?>
                                                    <option value="<?= $grupo['group_id']; ?>">
                                                        <?= htmlspecialchars($grupo['group_name']); ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Botones para agregar y quitar grupos -->
                                    <div class="col-md-2 text-center" style="margin-top: 80px;">
                                        <button type="button" id="add_group" class="btn btn-primary btn-block">Agregar &gt;&gt;</button>
                                        <button type="button" id="remove_group" class="btn btn-primary btn-block">&lt;&lt; Quitar</button>
                                    </div>

                                    <!-- Grupos asignados -->
                                    <div class="col-md-5">
                                        <label for="">Grupos asignados</label>
                                        <select id="grupos_asignados" name="grupos_asignados[]" class="form-control" multiple style="height:200px;">
                                            <?php foreach ($grupos_asignados as $grupo_asignado): ?>
                                                <option value="<?= $grupo_asignado['group_id']; ?>" selected>
                                                    <?= htmlspecialchars($grupo_asignado['group_name']); ?>
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
include('../../layout/parte2.php');
include('../../layout/mensajes.php');
?>

<!-- Script para manejar AJAX y cargar las materias y grupos -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    /* Función para actualizar el contador de horas */
    function updateTotalHours() {
        let totalHours = 0;
        $('#materias_asignadas option').each(function() {
            totalHours += parseInt($(this).data('hours') || 0);
        });
        $('#total_hours').text(totalHours);
    }

    /* Actualizar horas al cargar la página */
    updateTotalHours();

    /* Seleccionar todas las opciones en 'materias_asignadas' y 'grupos_asignados' al cargar la página */
    $('#materias_asignadas option').prop('selected', true);
    $('#grupos_asignados option').prop('selected', true);

    /* Evento al cambiar el programa o cuatrimestre */
    $('#programa_id, #cuatrimestre_id').change(function() {
        var programa_id = $('#programa_id').val();
        var cuatrimestre_id = $('#cuatrimestre_id').val();

        if (programa_id && cuatrimestre_id) {
            /* Obtener IDs de materias asignadas */
            var assignedSubjectIds = [];
            $('#materias_asignadas option').each(function() {
                assignedSubjectIds.push($(this).val());
            });

            /* Solicitud AJAX para obtener las materias disponibles */
            $.ajax({
                url: '../../app/controllers/relacion_profesor_materias/obtener_materias.php',
                type: 'POST',
                data: {
                    programa_id: programa_id,
                    cuatrimestre_id: cuatrimestre_id,
                    assigned_subjects: assignedSubjectIds
                },
                success: function(response) {
                    $('#materias_disponibles').html(response);
                    updateTotalHours();
                },
                error: function() {
                    console.log('Error en la solicitud de materias');
                }
            });

            /* Obtener IDs de grupos asignados */
            var assignedGroupIds = [];
            $('#grupos_asignados option').each(function() {
                assignedGroupIds.push($(this).val());
            });

            /* Solicitud AJAX para obtener los grupos disponibles */
            $.ajax({
                url: '../../app/controllers/relacion_profesor_grupos/obtener_grupos.php',
                type: 'POST',
                data: {
                    programa_id: programa_id,
                    cuatrimestre_id: cuatrimestre_id,
                    assigned_groups: assignedGroupIds
                },
                success: function(response) {
                    $('#grupos_disponibles').html(response);
                },
                error: function() {
                    console.log('Error en la solicitud de grupos');
                }
            });
        }
    });

    /* Eventos para mover materias entre listas */
    $('#add_subject').click(function() {
        $('#materias_disponibles option:selected').each(function() {
            $(this).appendTo('#materias_asignadas');
        });
        /* Seleccionar todas las opciones en 'materias_asignadas' */
        $('#materias_asignadas option').prop('selected', true);
        updateTotalHours();
    });

    $('#remove_subject').click(function() {
        $('#materias_asignadas option:selected').each(function() {
            $(this).appendTo('#materias_disponibles');
        });
        /* Seleccionar todas las opciones en 'materias_asignadas' */
        $('#materias_asignadas option').prop('selected', true);
        updateTotalHours();
    });

    /* Eventos para mover grupos entre listas */
    $('#add_group').click(function() {
        $('#grupos_disponibles option:selected').each(function() {
            $(this).appendTo('#grupos_asignados');
        });
        /* Seleccionar todas las opciones en 'grupos_asignados' */
        $('#grupos_asignados option').prop('selected', true);
    });

    $('#remove_group').click(function() {
        $('#grupos_asignados option:selected').each(function() {
            $(this).appendTo('#grupos_disponibles');
        });
        /* Seleccionar todas las opciones en 'grupos_asignados' */
        $('#grupos_asignados option').prop('selected', true);
    });

    /* Seleccionar todas las opciones antes de enviar el formulario */
    $('form').submit(function() {
        $('#materias_asignadas option').prop('selected', true);
        $('#grupos_asignados option').prop('selected', true);
    });
});
</script>
