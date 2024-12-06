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
            <div class="row">
                <h1>Editar Grupo: <?= htmlspecialchars($group_name); ?></h1>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Datos registrados</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/grupos/update.php" method="post">
                                <!-- Campo oculto para el ID del grupo -->
                                <input type="hidden" name="group_id" value="<?= htmlspecialchars($group_id); ?>">

                                <!-- Campo para el nombre del grupo -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Nombre del grupo</label>
                                            <input type="text" class="form-control" name="group_name" value="<?= htmlspecialchars($group_name); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campo para el programa educativo -->
                                <div class="row">
                                    <div class="col-md-12">
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
                                    </div>
                                </div>

                                <!-- Campo para el cuatrimestre -->
                                <div class="row">
                                    <div class="col-md-12">
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
                                    </div>
                                </div>
                                                               
                                <!-- Campo para el volumen del grupo -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Volumen del grupo</label>
                                            <input type="number" class="form-control" name="volume" value="<?= htmlspecialchars($volumen_grupo); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campo para el turno -->
                                <div class="row">
                                    <div class="col-md-12">
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
                                    </div>
                                </div>

                                <!-- Campo para el nivel educativo -->
                                <div class="row">
                                    <div class="col-md-12">
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
                                    </div>
                                </div>

                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                            <a href="<?= APP_URL; ?>/admin/grupos" class="btn btn-secondary">Cancelar</a>
                                        </div>
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
