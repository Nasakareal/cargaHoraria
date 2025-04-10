<?php
include('../../../app/config.php');
include('../../../app/middleware.php');

/* Verificar si el usuario tiene el permiso */
if (!verificarPermiso($_SESSION['sesion_id_usuario'], 'teacher_assign', $pdo)) {
    $_SESSION['mensaje'] = "No tienes permiso para eliminar materias asignadas a un profesor.";
    $_SESSION['icono'] = "error";
    ?>
    <script>
        history.back();
    </script>
    <?php
    exit;
}

/* Obtener el ID del profesor de la URL */
$teacher_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

/* Verificar si el ID es válido */
if (!$teacher_id) {
    echo "ID de profesor inválido.";
    exit;
}

$materias_disponibles = [];
$materias_asignadas = [];

include('../../../admin/layout/parte1.php');
include('../../../app/controllers/profesores/datos_del_profesor_en_subjects.php');
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
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h3 class="card-title">Llene los datos</h3>
                        </div>
                        <div class="card-body">
                            <!-- Formulario con método POST y acción que apunta a `update_subjects.php` -->
                            <form action="<?= APP_URL; ?>/app/controllers/profesores/delete_subjects.php" method="post">
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
                                                <?php include('../../../app/controllers/relacion_profesor_grupos/obtener_grupos_eliminar.php'); ?>
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
                                        <button type="button" id="add_subject" class="btn btn-primary btn-block">Eliminar &gt;&gt;</button>
                                        <button type="button" id="remove_subject" class="btn btn-primary btn-block">&lt;&lt; Conservar</button>
                                    </div>

                                    <!-- Materias que serán eliminadas -->
                                    <div class="col-md-5">
                                        <label for="materias_eliminar">Materias que serán eliminadas</label>
                                        <select id="materias_eliminar" name="materias_eliminar[]" class="form-control" multiple style="height:200px;">
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
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                        <a href="<?= APP_URL; ?>/admin/configuraciones/eliminar_materias_profesor" class="btn btn-primary">Cancelar</a>
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
include('../../../admin/layout/parte2.php');
include('../../../layout/mensajes.php');
?>

<!-- jQuery y el archivo JavaScript externo -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        var teacher_id = $('input[name="teacher_id"]').val();

        /* Obtener el valor inicial de horas asignadas desde la base de datos */
        $.ajax({
            url: '../../../app/controllers/relacion_profesor_materias/obtener_horas.php',
            type: 'POST',
            data: { teacher_id: teacher_id },
            success: function (response) {
                console.log("Horas iniciales del servidor:", response);
                var initialHours = parseInt(response) || 0;
                $('#total_hours').val(initialHours);
            },
            error: function () {
                console.error('Error al obtener las horas iniciales del profesor.');
                $('#total_hours').val(0);
            }
        });

        /* Confirmar grupo seleccionado */
        $('#confirm_group').click(function () {
            var group_id = $('#grupos_disponibles').val();
            console.log("Grupo seleccionado:", group_id);

            if (group_id) {
                /* Limpiar las materias actuales antes de cargar las nuevas */
                $('#materias_disponibles').empty();
                $('#materias_eliminar').empty();

                /* Actualizar el campo oculto de grupos asignados */
                $('#grupos_asignados').val(group_id);

                /* Realizar la solicitud AJAX para obtener materias */
                $.ajax({
                    url: '../../../app/controllers/relacion_profesor_materias/obtener_materias_eliminar.php',
                    type: 'POST',
                    data: { group_id: group_id, teacher_id: teacher_id },
                    success: function (response) {
                        console.log("Materias disponibles:", response);
                        $('#materias_disponibles').html(response);
                    },
                    error: function () {
                        console.error('Error al cargar las materias.');
                    }
                });
            } else {
                alert("Por favor, selecciona un grupo válido.");
            }
        });

        /* Calcular el total de horas eliminadas */
        function actualizarHoras() {
            var totalHoras = 0;
            $('#materias_eliminar option').each(function () {
                totalHoras += parseInt($(this).data('hours')) || 0;
            });
            $('#total_hours').val(totalHoras);
        }

        /* Mover materias disponibles a eliminar */
        $('#add_subject').click(function () {
            $('#materias_disponibles option:selected').each(function () {
                var horas = parseInt($(this).data('hours')) || 0;

                /* Resta las horas al mover al canvas de eliminación */
                var totalHoras = parseInt($('#total_hours').val()) || 0;
                totalHoras -= horas;

                $('#total_hours').val(totalHoras);

                /* Mover la materia seleccionada */
                $(this).appendTo('#materias_eliminar');
            });
        });

        /* Mover materias de eliminar a disponibles */
        $('#remove_subject').click(function () {
            $('#materias_eliminar option:selected').each(function () {
                var horas = parseInt($(this).data('hours')) || 0;

                /* Suma las horas al mover de regreso a disponibles */
                var totalHoras = parseInt($('#total_hours').val()) || 0;
                totalHoras += horas;

                $('#total_hours').val(totalHoras);

                /* Mover la materia seleccionada */
                $(this).appendTo('#materias_disponibles');
            });
        });

        /* Actualizar el campo oculto de grupos antes de enviar el formulario */
        $('form').submit(function () {
            var gruposSeleccionados = [];
            var groupId = $('#grupos_asignados').val();

            /* Recopilar los grupos correspondientes a las materias seleccionadas */
            if (groupId) {
                gruposSeleccionados.push(groupId);
            }

            /* Actualizar el valor del campo oculto */
            $('#grupos_asignados').val(gruposSeleccionados.join(','));

            /* Seleccionar todas las materias en el canvas de eliminación */
            $('#materias_eliminar option').prop('selected', true);
        });
    });
</script>
