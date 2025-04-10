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

$areas = [];
foreach ($programs as $program) {
    if (!in_array($program['area'], array_column($areas, 'area_name'))) {
        $areas[] = [
            'area_id' => $program['area'],
            'area_name' => $program['area']
        ];
    }
}
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

                                <!-- Áreas -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="areas">Áreas</label>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <tbody>
                                                        <?php $counter = 0; ?>
                                                        <tr>
                                                            <?php foreach ($areas as $area): ?>
                                                                <td>
                                                                    <input type="checkbox" name="areas[]" value="<?= $area['area_id']; ?>" id="area_<?= $area['area_id']; ?>" <?= in_array($area['area_id'], $areas_asignadas ?? []) ? 'checked' : ''; ?>>
                                                                    <label for="area_<?= $area['area_id']; ?>"><?= htmlspecialchars($area['area_name']); ?></label>
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
                                            <small class="text-muted">Seleccione una o más áreas.</small>
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
                                                                        <option value="Lunes" <?= ($horario['day_of_week'] == 'Lunes') ? 'selected' : ''; ?>>Lunes</option>
                                                                        <option value="Martes" <?= ($horario['day_of_week'] == 'Martes') ? 'selected' : ''; ?>>Martes</option>
                                                                        <option value="Miércoles" <?= ($horario['day_of_week'] == 'Miércoles') ? 'selected' : ''; ?>>Miércoles</option>
                                                                        <option value="Jueves" <?= ($horario['day_of_week'] == 'Jueves') ? 'selected' : ''; ?>>Jueves</option>
                                                                        <option value="Viernes" <?= ($horario['day_of_week'] == 'Viernes') ? 'selected' : ''; ?>>Viernes</option>
                                                                        <option value="Sábado" <?= ($horario['day_of_week'] == 'Sábado') ? 'selected' : ''; ?>>Sábado</option>
                                                                        <option value="Domingo" <?= ($horario['day_of_week'] == 'Domingo') ? 'selected' : ''; ?>>Domingo</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select name="start_time[]" class="form-control">
                                                                        <?php for ($h = 7; $h <= 22; $h++):
                                                                            $time = sprintf("%02d:00", $h);
                                                                        ?>
                                                                        <option value="<?= $time; ?>" <?= (isset($horario['start_time']) && substr($horario['start_time'], 0, 5) == $time) ? 'selected' : ''; ?>>
                                                                            <?= $time; ?>
                                                                        </option>
                                                                        <?php endfor; ?>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select name="end_time[]" class="form-control">
                                                                        <?php for ($h = 7; $h <= 22; $h++):
                                                                            $time = sprintf("%02d:00", $h);
                                                                        ?>
                                                                        <option value="<?= $time; ?>" <?= (isset($horario['end_time']) && substr($horario['end_time'], 0, 5) == $time) ? 'selected' : ''; ?>>
                                                                            <?= $time; ?>
                                                                        </option>
                                                                        <?php endfor; ?>
                                                                    </select>
                                                                </td>
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

                                <!-- Botón de actualización -->
                                <div class="row" style="margin-top:20px;">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Actualizar</button>
                                        <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
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

    // Función que genera el select de hora para el nuevo horario
    function generateTimeSelect(name) {
        let selectHTML = `<select name="${name}" class="form-control">`;
        for (let h = 7; h <= 22; h++) {
            let hour = h < 10 ? '0' + h : h;
            let time = hour + ":00";
            selectHTML += `<option value="${time}">${time}</option>`;
        }
        selectHTML += `</select>`;
        return selectHTML;
    }

    // Agregar una nueva fila de horario
    document.getElementById('addHorario').addEventListener('click', function () {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <select name="day_of_week[]" class="form-control">
                    <option value="Lunes">Lunes</option>
                    <option value="Martes">Martes</option>
                    <option value="Miércoles">Miércoles</option>
                    <option value="Jueves">Jueves</option>
                    <option value="Viernes">Viernes</option>
                    <option value="Sábado">Sábado</option>
                    <option value="Domingo">Domingo</option>
                </select>
            </td>
            <td>${generateTimeSelect('start_time[]')}</td>
            <td>${generateTimeSelect('end_time[]')}</td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button></td>
        `;
        horariosTable.appendChild(newRow);
    });

    horariosTable.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row')) {
            event.target.closest('tr').remove();
        }
    });

    document.getElementById('clasificacion').addEventListener('change', function () {
        const formData = new FormData(document.getElementById('editForm'));
        fetch('<?= APP_URL; ?>/app/controllers/profesores/update.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
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
