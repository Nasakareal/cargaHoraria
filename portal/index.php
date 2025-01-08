<?php
include('../app/config.php');
include('../layout/parte1.php');
include('../app/controllers/materias/obtener_materias.php');
include('../app/controllers/materias/programas_no_asignados.php');


$grupos_materias_faltantes = isset($grupos_materias_faltantes) ? $grupos_materias_faltantes : [];
$total_grupos = count($grupos_materias_faltantes);

$materias_cubiertas = 0;
$materias_no_cubiertas = 0;


foreach ($grupos_materias_faltantes as $grupo) {
    $materias_cubiertas += $grupo['materias_asignadas'];
    $materias_no_cubiertas += $grupo['materias_no_cubiertas'];
}

$total_materias = $materias_cubiertas + $materias_no_cubiertas;
$porcentaje_cubiertas = $total_materias > 0 ? round(($materias_cubiertas / $total_materias) * 100, 2) : 0;
$porcentaje_no_cubiertas = 100 - $porcentaje_cubiertas;
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <br>
    <div class="container">
        <div class="row justify-content-center">
            <h1 class="text-center"><?= APP_NAME; ?></h1>
        </div>
        <br>
        <div class="row justify-content-center">
<!-- Listado de Grupos con Materias No Asignadas -->
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title text-center">Grupos con Materias Sin Profesor</h3>
        </div>
        <div class="card-body">
            <p class="text-center"><strong>Total de Grupos Faltantes:</strong> <?php echo $total_grupos; ?></p>
            <p class="text-center"><strong>Total de Materias Faltantes:</strong> <?php echo $materias_no_cubiertas; ?></p>

            <?php
            
            $grupos_con_materias_faltantes = array_filter($grupos_materias_faltantes, function ($grupo) {
                return isset($grupo['materias_faltantes']) && $grupo['materias_faltantes'] > 0;
            });
            ?>

            <?php if (!empty($grupos_con_materias_faltantes)): ?>
                <table id="listadoMaterias" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Materias Sin Profesor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grupos_con_materias_faltantes as $grupo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grupo['grupo']); ?></td>
                                <td><?php echo htmlspecialchars($grupo['materias_faltantes']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">Todos los grupos tienen sus materias asignadas a profesores.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


        </div>
    </div>
</div>

<?php
include('../app/controllers/horarios_grupos/grupos_disponibles.php');
include('../app/controllers/horarios_grupos/obtener_horario_grupo.php');
include('../app/controllers/horarios_grupos/procesar_horario_grupo.php');

$group_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Obtener los grupos disponibles
$grupos = obtenerGrupos($pdo);

// Si no se seleccionó un grupo, asignar el primero por defecto
if (empty($group_id) && !empty($grupos)) {
    $group_id = $grupos[0]['group_id']; // ID del primer grupo de la lista
    $resultado = procesarHorarioGrupo($group_id, $pdo);

    // Actualizamos los datos solo si hay un grupo por defecto
    $tabla_horarios = $resultado['tabla_horarios'];
    $turno = $resultado['turno'];
    $nombre_grupo = $resultado['nombre_grupo'];
    $horas = $resultado['horas'];
    $dias = $resultado['dias'];
} else if ($group_id) {
    $resultado = procesarHorarioGrupo($group_id, $pdo);

    if (isset($resultado['error'])) {
        echo $resultado['error'];
        include('../layout/parte2.php');
        exit;
    }

    $tabla_horarios = $resultado['tabla_horarios'];
    $turno = $resultado['turno'];
    $nombre_grupo = $resultado['nombre_grupo'];
    $horas = $resultado['horas'];
    $dias = $resultado['dias'];
} else {
    $tabla_horarios = [];
    $turno = null;
    $nombre_grupo = null;
    $horas = [];
    $dias = [];
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
 <center><h1>Por favor seleccione un grupo para ver los horarios disponibles en color amarillo</h1></center>
    <br>
    <div class="content">
        <div class="container">

        <!-- Filtro por Grupo -->
        <div class="row mb-4">
                <div class="col-md-6">
                    <form method="GET" action="">
                        <div class="form-group">
                            <label for="groupSelector">Seleccione un grupo:</label>
                            <select id="groupSelector" name="id" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Seleccionar grupo --</option>
                                <?php foreach ($grupos as $grupo): ?>
                                    <option value="<?= $grupo['group_id']; ?>" <?= $group_id == $grupo['group_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($grupo['group_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <!-- Actualizado para mostrar el nombre del grupo -->
                <h1>Horarios Asignados al Grupo <?= htmlspecialchars($nombre_grupo); ?> (Turno: <?= htmlspecialchars($turno); ?>)</h1> 
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Detalles del Horario</h3>

                            <div class="form-group d-flex justify-content-end">
                                <a href="<?= APP_URL; ?>/admin/horarios_grupos" class="btn btn-secondary">Volver</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table id="example1" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Hora/Día</th>
                                                <?php foreach ($dias as $dia): ?>
                                                    <th><?= htmlspecialchars($dia); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($horas as $hora): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($hora); ?></td>
                                                    <?php foreach ($dias as $dia): ?>
                                                        <?php
                                                        $contenido = $tabla_horarios[$hora][$dia] ?? '';
                                                        $sin_profesor = strpos($contenido, 'Sin profesor') !== false ? 'table-warning' : '';
                                                        ?>
                                                        <td class="<?= $sin_profesor; ?>"><?= $contenido; ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
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
<!-- /.content-wrapper -->

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 15,
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "dom": 'Bfrtip',
            buttons: [
                {
                    extend: 'collection',
                    text: 'Opciones',
                    buttons: [
                        'copy',
                        'csv',
                        'PDF',
                        'Imprimir',
                        'Excel', ]
                },
                {
                    extend: 'colvis',
                    text: 'Visor de columnas'
                }
            ]
        });
    });
</script>


<?php
include('../admin/layout/parte2.php');
include('../layout/mensajes.php');
?>

<script>
    $(document).ready(function () {
        $('#listadoMaterias').DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay grupos con materias sin profesor",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ grupos",
                "infoEmpty": "Mostrando 0 a 0 de 0 grupos",
                "infoFiltered": "(Filtrado de _MAX_ grupos en total)",
                "lengthMenu": "Mostrar _MENU_ grupos",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false
        });
    });
</script>

<style>
    .dataTables_filter input {
        background-color: #ffd800;
        color: #333;
        border: 1px solid #555;
        border-radius: 4px;
        padding: 5px;
        font-weight: bold;
    }

    .dataTables_filter label {
        color: #333;
        font-weight: bold;
    }
</style>

