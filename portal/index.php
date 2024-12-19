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

