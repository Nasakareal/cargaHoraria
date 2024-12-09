<?php

$teacher_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$teacher_id) {
    echo "ID de profesor inválido.";
    exit;
}

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/profesores/datos_del_profesor.php');
include('../../app/controllers/relacion_profesor_programa_cuatrimestre/listado_de_relacion.php');

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Profesor: <?= $nombres ?? 'Desconocido'; ?></h1> 
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Datos del profesor</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Nombres del profesor -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Nombres del profesor</label>
                                        <p><?= $nombres ?? 'Desconocido'; ?></p>
                                    </div>
                                </div>

                                <!-- Clasificación (Categoría) -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Clasificación</label>
                                        <p><?= $clasificacion ?? 'No asignado'; ?></p> 
                                    </div>
                                </div>

                                <!-- Área -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Área</label>
                                        <p><?= $area ?? 'No asignado'; ?></p> 
                                    </div>
                                </div>

                                <!-- Materias -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Materias</label>
                                        <p><?= $materias ?? 'No asignado'; ?></p> 
                                    </div>
                                </div>

                                <!-- Horas Semanales -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Horas Semanales</label>
                                        <p><?= $horas_semanales ?? 'No disponible'; ?></p> 
                                    </div>
                                </div>

                               <!-- Programas -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Programas</label>
                                        <p><?= $programas ?? 'No asignado'; ?></p>
                                    </div>
                                </div>


                                <!-- Grupos -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Grupos</label>
                                        <p><?= $grupos ?? 'No asignado'; ?></p> 
                                    </div>
                                </div>

                                <!-- Horarios Disponibles -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="">Horarios Disponibles</label>
                                        <?php if (!empty($horarios_disponibles) && is_array($horarios_disponibles)): ?>
                                            <ul>
                                                <?php
                                                $dias_espanol = [
                                                    'Monday' => 'Lunes',
                                                    'Tuesday' => 'Martes',
                                                    'Wednesday' => 'Miércoles',
                                                    'Thursday' => 'Jueves',
                                                    'Friday' => 'Viernes',
                                                    'Saturday' => 'Sábado',
                                                    'Sunday' => 'Domingo'
                                                ]; ?>
                                                <?php foreach ($horarios_disponibles as $horario): ?>
                                                    <li>
                                                        <?= $dias_espanol[$horario['day_of_week']] ?? $horario['day_of_week']; ?>: 
                                                        de <?= date('H:i', strtotime($horario['start_time'])); ?> 
                                                        a <?= date('H:i', strtotime($horario['end_time'])); ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p>No asignado</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <a href="<?= APP_URL; ?>/admin/profesores" class="btn btn-secondary">Volver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>


<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>
