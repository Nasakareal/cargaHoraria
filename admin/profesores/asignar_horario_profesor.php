<?php
include('../../app/config.php');
include('../../app/middleware.php');

/* Verificar si el usuario tiene el permiso */
if (!verificarPermiso($_SESSION['sesion_id_usuario'], 'teacher_assign', $pdo)) {
    $_SESSION['mensaje'] = "No tienes permiso para asignar profesores a materias y grupos.";
    $_SESSION['icono'] = "error";
    ?>
    <script>
        history.back();
    </script>
    <?php
    exit;
}

/* Obtener el ID del profesor de la URL */
$teacher_id = filter_input(INPUT_GET, 'teacher_id', FILTER_VALIDATE_INT);

/* Verificar si el ID es válido */
if (!$teacher_id) {
    echo "ID de profesor inválido.";
    exit;
}

include('../../admin/layout/parte1.php');
include('../../app/controllers/profesores/datos_del_profesor_en_subjects.php');
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
<script>$(document).ready(function () {
    var teacher_id = $('input[name="teacher_id"]').val();

    /* Obtener el valor inicial de horas asignadas desde la base de datos */
    $.ajax({
        url: '../../app/controllers/relacion_profesor_materias/obtener_horas.php',
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
            // Verificar si ya hay materias asignadas
            if ($('#materias_asignadas option').length > 0) {
                // Si hay materias asignadas, mostrar un mensaje de SweetAlert
                Swal.fire({
                    title: '¿Eliminar materias asignadas?',
                    text: "Ya tienes materias asignadas. ¿Quieres eliminarlas antes de seleccionar otro grupo?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'No, cancelar',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Si el usuario acepta, eliminar las materias asignadas
                        $('#materias_asignadas').empty(); // Limpiar materias asignadas
                        $('#total_hours').val(0); // Resetear horas
                        console.log("Materias asignadas eliminadas.");

                        // Continuar con la selección del grupo
                        var gruposAsignados = $('#grupos_asignados').val().split(',').filter(Boolean);
                        if (!gruposAsignados.includes(group_id)) {
                            gruposAsignados.push(group_id);
                            $('#grupos_asignados').val(gruposAsignados.join(','));
                        }

                        // Realizar la solicitud AJAX para obtener materias
                        $.ajax({
                            url: '../../app/controllers/relacion_profesor_materias/obtener_materias.php',
                            type: 'POST',
                            data: { group_id: group_id },
                            success: function (response) {
                                console.log("Materias disponibles:", response);
                                $('#materias_disponibles').html(response);
                            },
                            error: function () {
                                console.error('Error al cargar las materias.');
                            }
                        });

                    } else {
                        // Si el usuario decide no eliminar, mostrar mensaje
                        console.log("El usuario decidió no eliminar las materias.");
                    }
                });

            } else {
                // Si no hay materias asignadas, proceder normalmente
                var gruposAsignados = $('#grupos_asignados').val().split(',').filter(Boolean);
                if (!gruposAsignados.includes(group_id)) {
                    gruposAsignados.push(group_id);
                    $('#grupos_asignados').val(gruposAsignados.join(','));
                }

                // Realizar la solicitud AJAX para obtener materias
                $.ajax({
                    url: '../../app/controllers/relacion_profesor_materias/obtener_materias.php',
                    type: 'POST',
                    data: { group_id: group_id },
                    success: function (response) {
                        console.log("Materias disponibles:", response);
                        $('#materias_disponibles').html(response);
                    },
                    error: function () {
                        console.error('Error al cargar las materias.');
                    }
                });
            }

        } else {
            Swal.fire({
                title: 'Error',
                text: 'Por favor, selecciona un grupo válido.',
                icon: 'error',
                confirmButtonText: 'Cerrar'
            });
        }
    });

    /* Calcular el total de horas asignadas */
    function calcularTotalHoras() {
        var totalHoras = parseInt($('#total_hours').val()) || 0;
        $('#materias_asignadas option').each(function () {
            totalHoras += parseInt($(this).data('hours')) || 0;
        });
        $('#total_hours').val(totalHoras);
    }

    /* Mover materias disponibles a asignadas */
    $('#add_subject').click(function () {
        $('#materias_disponibles option:selected').each(function () {
            $(this).appendTo('#materias_asignadas');
        });
        calcularTotalHoras();
    });

    /* Mover materias asignadas a disponibles */
    $('#remove_subject').click(function () {
        $('#materias_asignadas option:selected').each(function () {
            $(this).appendTo('#materias_disponibles');
        });
        calcularTotalHoras();
    });

    /* Seleccionar todas las materias asignadas antes de enviar el formulario */
    $('form').submit(function () {
        $('#materias_asignadas option').prop('selected', true);
    });
});
</script>
