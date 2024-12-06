<?php
include('../../app/config.php');

$teacher_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$teacher_id) {
    echo "ID de profesor inválido.";
    exit;
}

include('../../admin/layout/parte1.php');
include('../../app/controllers/profesores/datos_del_profesor.php');
include('../../app/controllers/programas/listado_de_programas.php');

$clasificacion = isset($clasificacion) ? $clasificacion : '';
$specialization_program_id = isset($specialization_program_id) ? $specialization_program_id : '';
$programa_adscripcion_id = isset($program_id) ? $program_id : null;
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
                            <form id="editForm" action="<?= APP_URL; ?>/app/controllers/profesores/update.php" method="post">
                                <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($teacher_id); ?>">

                                <!-- Datos del profesor -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Nombres del profesor</label>
                                            <input type="text" name="nombres" value="<?= htmlspecialchars($nombres); ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    <!-- Clasificación -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="clasificacion">Clasificación</label>
                                            <select name="clasificacion" id="clasificacion" class="form-control" required>
                                                <option value="PTC" <?= ($clasificacion == 'PTC') ? 'selected' : ''; ?>>PTC</option>
                                                <option value="PA" <?= ($clasificacion == 'PA') ? 'selected' : ''; ?>>PA</option>
                                                <option value="TA" <?= ($clasificacion == 'TA') ? 'selected' : ''; ?>>TA</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Programa de Adscripción -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="programa_adscripcion">Programa de Adscripción</label>
                                            <select name="programa_adscripcion" id="programa_adscripcion" class="form-control" <?= ($clasificacion == 'PTC') ? '' : 'disabled'; ?>>
                                                <option value="">Seleccione un programa</option>
                                                <?php foreach ($programs as $program): ?>
                                                    <option value="<?= $program['program_id']; ?>" <?= ($programa_adscripcion_id == $program['program_id']) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($program['program_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Horarios Disponibles -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="horarios_disponibles">Horarios Disponibles</label>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Día</th>
                                                        <th>Hora de Inicio</th>
                                                        <th>Hora de Fin</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="horarios_table">
                                                    <?php if (!empty($horarios_disponibles)): ?>
                                                        <?php foreach ($horarios_disponibles as $horario): ?>
                                                            <tr>
                                                                <td>
                                                                    <select name="day_of_week[]" class="form-control">
                                                                        <option value="Monday" <?= ($horario['day_of_week'] == 'Monday') ? 'selected' : ''; ?>>Lunes</option>
                                                                        <option value="Tuesday" <?= ($horario['day_of_week'] == 'Tuesday') ? 'selected' : ''; ?>>Martes</option>
                                                                        <option value="Wednesday" <?= ($horario['day_of_week'] == 'Wednesday') ? 'selected' : ''; ?>>Miércoles</option>
                                                                        <option value="Thursday" <?= ($horario['day_of_week'] == 'Thursday') ? 'selected' : ''; ?>>Jueves</option>
                                                                        <option value="Friday" <?= ($horario['day_of_week'] == 'Friday') ? 'selected' : ''; ?>>Viernes</option>
                                                                        <option value="Saturday" <?= ($horario['day_of_week'] == 'Saturday') ? 'selected' : ''; ?>>Sábado</option>
                                                                        <option value="Sunday" <?= ($horario['day_of_week'] == 'Sunday') ? 'selected' : ''; ?>>Domingo</option>
                                                                    </select>
                                                                </td>
                                                                <td><input type="time" name="start_time[]" class="form-control" value="<?= htmlspecialchars($horario['start_time']); ?>"></td>
                                                                <td><input type="time" name="end_time[]" class="form-control" value="<?= htmlspecialchars($horario['end_time']); ?>"></td>
                                                                <td>
                                                                    <button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="4">No hay horarios disponibles asignados.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                            <button type="button" id="addHorario" class="btn btn-success btn-sm">Agregar Horario</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Programas -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="programas">Programas</label>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <tbody>
                                                        <?php $counter = 0; ?>
                                                        <tr>
                                                            <?php foreach ($programs as $program): ?>
                                                                <td>
                                                                    <input type="checkbox" name="programas[]" value="<?= $program['program_id']; ?>" id="programa_<?= $program['program_id']; ?>" <?= in_array($program['program_id'], $programas_asignados ?? []) ? 'checked' : ''; ?> <?= ($clasificacion == 'PTC') ? 'disabled' : ''; ?>>
                                                                    <label for="programa_<?= $program['program_id']; ?>"><?= htmlspecialchars($program['program_name']); ?></label>
                                                                </td>
                                                                <?php $counter++; ?>
                                                                <?php if ($counter % 3 == 0): ?>
                                                                    </tr><tr>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <small class="text-muted">Seleccione múltiples programas si aplica.</small>
                                        </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const horariosTable = document.getElementById('horarios_table');

        // Agregar horario
        document.getElementById('addHorario').addEventListener('click', function () {
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <select name="day_of_week[]" class="form-control">
                        <option value="Monday">Lunes</option>
                        <option value="Tuesday">Martes</option>
                        <option value="Wednesday">Miércoles</option>
                        <option value="Thursday">Jueves</option>
                        <option value="Friday">Viernes</option>
                        <option value="Saturday">Sábado</option>
                        <option value="Sunday">Domingo</option>
                    </select>
                </td>
                <td><input type="time" name="start_time[]" class="form-control"></td>
                <td><input type="time" name="end_time[]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button></td>
            `;
            horariosTable.appendChild(newRow);
        });

        // Eliminar horario
        horariosTable.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-row')) {
                event.target.closest('tr').remove();
            }
        });

        // Cambiar clasificación
        document.getElementById('clasificacion').addEventListener('change', function () {
            const clasificacion = this.value;
            const programaAdscripcion = document.getElementById('programa_adscripcion');
            const checkboxes = document.querySelectorAll('input[name="programas[]"]');

            if (clasificacion === 'PTC') {
                programaAdscripcion.disabled = false;
                checkboxes.forEach(checkbox => checkbox.disabled = true);
            } else {
                programaAdscripcion.disabled = true;
                checkboxes.forEach(checkbox => checkbox.disabled = false);
            }

            // Guardar automáticamente los cambios
            const formData = new FormData(document.getElementById('editForm'));
            fetch('<?= APP_URL; ?>/app/controllers/profesores/update.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Clasificación actualizada correctamente');
                } else {
                    alert('Error al actualizar la clasificación');
                }
            });
        });
    });
</script>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>
