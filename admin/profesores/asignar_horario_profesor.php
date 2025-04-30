<?php
if (!isset($_GET['refreshed'])) {
    $urlActual = $_SERVER['REQUEST_URI'];
    $separador = strpos($urlActual, '?') !== false ? '&' : '?';
    header("Location: " . $urlActual . $separador . "refreshed=1");
    exit;
}

require_once '../../app/registro_eventos.php';
include('../../app/config.php');
include('../../app/middleware.php');

if (!verificarPermiso($_SESSION['sesion_id_usuario'], 'teacher_assign', $pdo)) {
    $_SESSION['mensaje'] = "No tienes permiso para asignar profesores a materias y grupos.";
    $_SESSION['icono'] = "error";
    ?>
    <script>history.back();</script>
    <?php
    exit;
}

$teacher_id = filter_input(INPUT_GET, 'teacher_id', FILTER_VALIDATE_INT);
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
                            <form action="<?= APP_URL; ?>/app/controllers/profesores/update_subjects.php" method="post">
                                <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($teacher_id); ?>">
                                <!-- Contenedor donde el JS añade los inputs ocultos de grupos -->
                                <div id="hidden_group_inputs"></div>

                                <!-- Total de horas asignadas -->
                                <div class="row" style="margin-top: 20px;">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="total_hours">Total de horas asignadas</label>
                                            <input type="text" id="total_hours" name="total_hours" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <!-- Grupos disponibles (ya no enviamos name aquí) -->
                                    <div class="col-md-5">
                                        <label for="grupos_disponibles">Grupos disponibles</label>
                                        <div class="input-group">
                                            <select id="grupos_disponibles" class="form-control">
                                                <?php include('../../app/controllers/relacion_profesor_grupos/grupos_disponibles.php'); ?>
                                            </select>
                                            <button id="confirm_group" class="btn btn-primary" type="button">Seleccionar Grupo</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Materias disponibles y asignadas -->
                                <div class="row">
                                    <div class="col-md-5">
                                        <label for="materias_disponibles">Materias disponibles</label>
                                        <select id="materias_disponibles" class="form-control" multiple style="height:200px;">
                                            <?php foreach ($materias_disponibles as $materia): ?>
                                                <option value="<?= htmlspecialchars($materia['subject_id']); ?>"
                                                        data-hours="<?= htmlspecialchars($materia['weekly_hours']); ?>">
                                                    <?= htmlspecialchars($materia['subject_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-2 text-center" style="margin-top: 80px;">
                                        <button type="button" id="add_subject" class="btn btn-primary btn-block">Agregar &gt;&gt;</button>
                                        <button type="button" id="remove_subject" class="btn btn-primary btn-block">&lt;&lt; Quitar</button>
                                    </div>

                                    <div class="col-md-5">
                                        <label for="materias_asignadas">Materias asignadas</label>
                                        <select id="materias_asignadas" name="materias_asignadas[]" class="form-control" multiple style="height:200px;">
                                            <?php foreach ($materias_asignadas as $materia): ?>
                                                <option value="<?= htmlspecialchars($materia['subject_id']); ?>"
                                                        data-hours="<?= htmlspecialchars($materia['weekly_hours']); ?>">
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
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    var teacher_id = $('input[name="teacher_id"]').val();

    // Cargo horas iniciales
    $.ajax({
        url: '../../app/controllers/relacion_profesor_materias/obtener_horas.php',
        type: 'POST',
        data: { teacher_id: teacher_id },
        success: function (response) {
            $('#total_hours').val(parseInt(response) || 0);
        },
        error: function () {
            $('#total_hours').val(0);
        }
    });

    $('#confirm_group').click(function () {
        var group_id = $('#grupos_disponibles').val();
        if (!group_id) return;

        // Si ya había materias asignadas, pregunto si limpiar
        if ($('#materias_asignadas option').length > 0) {
            Swal.fire({
                title: '¿Eliminar materias asignadas?',
                text: "Ya tienes materias asignadas. ¿Quieres eliminarlas antes de seleccionar otro grupo?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'No, cancelar',
            }).then((result) => {
                if (!result.isConfirmed) return;
                $('#materias_asignadas').empty();
                $('#total_hours').val(0);
                // limpio grupos previos y agrego el nuevo
                $('#hidden_group_inputs').empty();
                $('#hidden_group_inputs').append(
                    '<input type="hidden" name="grupos_asignados[]" value="' + group_id + '">'
                );
                cargarMaterias(group_id);
            });
        } else {
            // Si no había materias, solo agrego el grupo si no existe
            if ($('#hidden_group_inputs input[value="' + group_id + '"]').length === 0) {
                $('#hidden_group_inputs').append(
                    '<input type="hidden" name="grupos_asignados[]" value="' + group_id + '">'
                );
            }
            cargarMaterias(group_id);
        }
    });

    function cargarMaterias(group_id) {
        $.ajax({
            url: '../../app/controllers/relacion_profesor_materias/obtener_materias.php',
            type: 'POST',
            data: { group_id: group_id },
            success: function (response) {
                $('#materias_disponibles').html(response);
            }
        });
    }

    function calcularTotalHoras() {
        var totalHoras = 0;
        $('#materias_asignadas option').each(function () {
            totalHoras += parseInt($(this).data('hours')) || 0;
        });
        $('#total_hours').val(totalHoras);
    }

    $('#add_subject').click(function () {
        $('#materias_disponibles option:selected').appendTo('#materias_asignadas');
        calcularTotalHoras();
    });

    $('#remove_subject').click(function () {
        $('#materias_asignadas option:selected').appendTo('#materias_disponibles');
        calcularTotalHoras();
    });

    $('form').submit(function () {
        // Aseguro que todas las opciones queden marcadas
        $('#materias_asignadas option').prop('selected', true);
    });
});
</script>
