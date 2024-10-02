<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/materias/datos_de_materias.php');
include('../../app/controllers/materias/listado_de_materias.php');

$subject_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$subject_id) {
    echo "ID de materia inválido.";
    exit;
}

/* Obtener los datos de la materia a modificar */
$query = $pdo->prepare("SELECT * FROM subjects WHERE subject_id = :subject_id");
$query->bindParam(':subject_id', $subject_id);
$query->execute();
$materia = $query->fetch(PDO::FETCH_ASSOC);

if (!$materia) {
    echo "Materia no encontrada.";
    exit;
}

/* Extraer datos de la materia */
$subject_name = $materia['subject_name'];
$horas_consecutivas = $materia['hours_consecutive']; 
$is_specialization = $materia['is_specialization'];
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Modificar materia: <?= $subject_name; ?></h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Llene los datos</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/materias/update.php" method="post">
                                <!-- Añadir campo oculto para el ID de la materia -->
                                <input type="hidden" name="subject_id" value="<?= $subject_id; ?>">

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Nombre de la materia</label>
                                            <input type="text" name="nombres" value="<?= $subject_name; ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Horas Consecutivas</label> 
                                            <input type="number" name="horas_consecutivas" value="<?= $horas_consecutivas; ?>" class="form-control" required> 
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">¿Es especialización?</label>
                                            <select name="is_specialization" class="form-control" required>
                                                <option value="0" <?php if (!$is_specialization)
                                                    echo 'selected'; ?>>No</option>
                                                <option value="1" <?php if ($is_specialization)
                                                    echo 'selected'; ?>>Sí</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Actualizar</button>
                                            <a href="<?= APP_URL; ?>/admin/materias" class="btn btn-secondary">Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>
