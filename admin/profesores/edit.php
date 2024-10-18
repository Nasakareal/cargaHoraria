<?php
include('../../app/config.php');

// Obtener el ID del profesor desde la URL
$teacher_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$teacher_id) {
    echo "ID de profesor inválido.";
    exit;
}

include('../../admin/layout/parte1.php');
include('../../app/controllers/profesores/datos_del_profesor.php');  // Cargar datos del profesor
include('../../app/controllers/programas/listado_de_programas.php');  // Cargar la lista de programas
include('../../app/controllers/cuatrimestres/listado_de_cuatrimestres.php');  // Cargar la lista de cuatrimestres
include('../../app/controllers/relacion_profesor_materias/listado_de_relacion.php');  // Cargar la relación de materias disponibles y asignadas
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

                                    <!-- Local o Foráneo -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Local o Foráneo</label>
                                            <select name="es_local" class="form-control" required>
                                                <option value="1" <?= $es_local == 'Local' ? 'selected' : ''; ?>>Local</option>
                                                <option value="0" <?= $es_local == 'Foráneo' ? 'selected' : ''; ?>>Foráneo</option>
                                            </select>
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

                                <!-- Materias disponibles (se cargarán dinámicamente con AJAX) -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="">Materias disponibles</label>
                                        <select id="materias_disponibles" name="materias_disponibles[]" class="form-control" multiple>
                                            <!-- Materias se cargarán aquí con AJAX -->
                                        </select>
                                    </div>

                                    <!-- Materias asignadas -->
                                    <div class="col-md-6">
                                        <label for="">Materias asignadas</label>
                                        <select id="materias_asignadas" name="materias_asignadas[]" class="form-control" multiple>
                                            <?php foreach ($materias_asignadas as $materia_asignada): ?>
                                                <option value="<?= $materia_asignada['subject_id']; ?>">
                                                    <?= htmlspecialchars($materia_asignada['subject_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Botones para agregar y quitar materias -->
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <button type="button" id="add_subject" class="btn btn-primary"> > </button>
                                        <button type="button" id="remove_subject" class="btn btn-primary"> < </button>
                                    </div>
                                </div>

                                <!-- Botón de actualización -->
                                <div class="row">
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

<!-- Script para manejar AJAX y cargar las materias -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Evento al cambiar el programa o cuatrimestre
        $('#programa_id, #cuatrimestre_id').change(function() {
            var programa_id = $('#programa_id').val();
            var cuatrimestre_id = $('#cuatrimestre_id').val();
            var teacher_id = <?= $teacher_id; ?>;

            if (programa_id && cuatrimestre_id) {
                // Solicitud AJAX para obtener las materias
                $.ajax({
                    url: '../../app/controllers/relacion_profesor_materias/obtener_materias.php',
                    type: 'POST',
                    data: {
                        programa_id: programa_id,
                        cuatrimestre_id: cuatrimestre_id,
                        teacher_id: teacher_id
                    },
                    success: function(response) {
                        // Cargar las materias en el select
                        $('#materias_disponibles').html(response);
                    }
                });
            }
        });

        // Scripts para mover materias entre listas
        document.getElementById('add_subject').addEventListener('click', function() {
            const available = document.getElementById('materias_disponibles');
            const assigned = document.getElementById('materias_asignadas');

            Array.from(available.selectedOptions).forEach(option => {
                assigned.appendChild(option);
            });
        });

        document.getElementById('remove_subject').addEventListener('click', function() {
            const assigned = document.getElementById('materias_asignadas');
            const available = document.getElementById('materias_disponibles');

            Array.from(assigned.selectedOptions).forEach(option => {
                available.appendChild(option);
            });
        });
    });
</script>
