<?php
$group_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$group_id) {
    header('Location: ' . APP_URL . '/admin/grupos');
    exit;
}

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/grupos/datos_del_grupo.php');
include('../../app/controllers/programas/listado_de_programas.php');
include('../../app/controllers/cuatrimestres/listado_de_cuatrimestres.php');
include('../../app/controllers/turnos/listado_de_turnos.php');
include('../../app/controllers/niveles/listado_de_niveles.php');
include('../../app/controllers/salones/listado_de_salones.php');

$group_name = isset($group_name) ? $group_name : "Grupo no encontrado";
$program_id = isset($program_id) ? $program_id : null;
$term_id = isset($term_id) ? $term_id : null;
$year = isset($year) ? $year : "Año no encontrado";
$volumen_grupo = isset($volumen_grupo) ? $volumen_grupo : "N/A";
$turn_id = isset($turn_id) ? $turn_id : null;
$nivel_id = isset($nivel_id) ? $nivel_id : null;
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Editar Grupo: <?= htmlspecialchars($group_name); ?></h3>
                </div>
                <div class="card-body">
                    <form action="<?= APP_URL; ?>/app/controllers/grupos/update.php" method="post">
                        <!-- Campo oculto para el ID del grupo -->
                        <input type="hidden" name="group_id" value="<?= htmlspecialchars($group_id); ?>">

                        <div class="row">
                            <!-- Nombre del grupo -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Nombre del grupo</label>
                                    <input type="text" class="form-control" name="group_name" value="<?= htmlspecialchars($group_name); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="">Programa Educativo</label>
                                    <select name="program_id" class="form-control" required>
                                        <option value="">Seleccione un programa</option>
                                        <?php foreach ($programs as $program): ?>
                                            <option value="<?= $program['program_id']; ?>" <?= ($program['program_id'] == $program_id) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($program['program_name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="">Cuatrimestre</label>
                                    <select name="term_id" class="form-control" required>
                                        <option value="">Seleccione un cuatrimestre</option>
                                        <?php foreach ($terms as $term): ?>
                                            <option value="<?= $term['term_id']; ?>" <?= ($term['term_id'] == $term_id) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($term['term_name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="">Volumen del grupo</label>
                                    <input type="number" class="form-control" name="volume" value="<?= htmlspecialchars($volumen_grupo); ?>" required>
                                </div>
                            </div>

                            <!-- Turno del grupo -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Turno</label>
                                    <select name="turn_id" class="form-control" required>
                                        <option value="">Seleccione un turno</option>
                                        <?php foreach ($turns as $turn): ?>
                                            <option value="<?= $turn['shift_id']; ?>" <?= ($turn['shift_id'] == $turn_id) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($turn['shift_name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="">Nivel Educativo</label>
                                    <select name="nivel_id" class="form-control" required>
                                        <option value="">Seleccione un nivel educativo</option>
                                        <?php foreach ($levels as $level): ?>
                                            <option value="<?= $level['level_id']; ?>" <?= ($level['level_id'] == $nivel_id) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($level['level_name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Edificio del salón del grupo -->
                                <div class="form-group">
                                    <label for="building_id">Edificio</label>
                                    <select id="building_id" name="building_id" class="form-control">
                                        <option value="">Seleccione un Edificio</option>
                                        <?php 
                                        $buildings = array_unique(array_column($classrooms, 'edificio'));
                                        foreach ($buildings as $building): ?>
                                            <option value="<?= htmlspecialchars($building, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars($building, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="floor_id">Planta</label>
                                    <select id="floor_id" name="floor_id" class="form-control" disabled>
                                        <option value="">Seleccione una Planta</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="classroom_id">Salón</label>
                                    <select id="classroom_id" name="classroom_id" class="form-control" disabled>
                                        <option value="">Seleccione un Salón</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href="<?= APP_URL; ?>/admin/grupos" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>


<!-- Scripts para manejar la interactividad -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const buildingSelect = document.getElementById("building_id");
    const floorSelect = document.getElementById("floor_id");
    const classroomSelect = document.getElementById("classroom_id");

    const classrooms = <?= json_encode($classrooms); ?>;

    buildingSelect.addEventListener("change", function() {
        const building = buildingSelect.value;
        floorSelect.innerHTML = '<option value="">Seleccione una Planta</option>';
        classroomSelect.innerHTML = '<option value="">Seleccione un Salón</option>';
        floorSelect.disabled = true;
        classroomSelect.disabled = true;

        if (building) {
            const floors = [...new Set(classrooms.filter(c => c.edificio === building).map(c => c.planta))];
            floors.forEach(floor => {
                floorSelect.innerHTML += `<option value="${floor}">${floor}</option>`;
            });
            floorSelect.disabled = false;
        }
    });

    floorSelect.addEventListener("change", function() {
        const building = buildingSelect.value;
        const floor = floorSelect.value;
        classroomSelect.innerHTML = '<option value="">Seleccione un Salón</option>';
        classroomSelect.disabled = true;

        if (building && floor) {
            const filteredClassrooms = classrooms.filter(c => c.edificio === building && c.planta === floor);
            filteredClassrooms.forEach(classroom => {
                classroomSelect.innerHTML += `<option value="${classroom.classroom_id}">${classroom.nombre_salon}</option>`;
            });
            classroomSelect.disabled = false;
        }
    });
});
</script>