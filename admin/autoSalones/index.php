<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/grupos/listado_de_grupos.php');
include('../../app/controllers/salones/listado_de_salones.php');

/* Lógica para asignar salones según la capacidad */
function asignarSalones($pdo, $capacidad_grupo, $salones_asignados)
{
    $salones_asignados_str = implode(',', array_map('intval', $salones_asignados));
    $sql = "SELECT * FROM classrooms WHERE capacity >= :capacidad_grupo";

    if (!empty($salones_asignados)) {
        $sql .= " AND classroom_id NOT IN ($salones_asignados_str)";
    }

    $sql .= " ORDER BY capacity ASC";

    $query = $pdo->prepare($sql);
    $query->execute(['capacidad_grupo' => $capacidad_grupo]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Grupos y Asignación de Salones</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Grupos registrados</h3>
                            <div class="card-tools d-flex">
                                <a href="create.php" class="btn btn-primary me-2">
                                    <i class="bi bi-plus-square"></i> Agregar nuevo grupo
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center"><center>Numero</center></th>
                                        <th class="text-center"><center>Nombre del Grupo</center></th>
                                        <th class="text-center"><center>Volumen</center></th>
                                        <th class="text-center"><center>Salón Asignado</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_grupos = 0;
                                $salones_asignados = [];
                                foreach ($groups as $group) {
                                    $contador_grupos++;
                                    $capacidad_grupo = $group['volume'];
                                
                                    $salones_asignados_temp = asignarSalones($pdo, $capacidad_grupo, $salones_asignados);
                                    $salon_asignado = null;

                                    if (!empty($salones_asignados_temp)) {
                                        $salon_asignado = $salones_asignados_temp[0];
                                        $salones_asignados[] = $salon_asignado['classroom_id'];
                                    }

                                    // Concatenar el nombre del salón con el último dígito del edificio
                                    $salon_nombre = $salon_asignado ? $salon_asignado['classroom_name'] . ' (' . substr($salon_asignado['building'], -1) . ')' : 'No disponible';
                                    ?>
                                    <tr>
                                        <td style="text-align: center"><?= $contador_grupos; ?></td>
                                        <td class="text-center"><?= $group['group_name']; ?></td>
                                        <td style="text-align: center"><?= $capacidad_grupo; ?></td>
                                        <td style="text-align: center"><?= $salon_nombre; ?></td> <!-- Mostrar el nombre del salón -->
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
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

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Grupos",
                "infoEmpty": "Mostrando 0 a 0 de 0 Grupos",
                "infoFiltered": "(Filtrado de _Max_ total Grupos)",
                "lengthMenu": "Mostrar _MENU_ Grupos",
                "loadingRecord": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscador:",
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
            "autoWidth": false,
            buttons: [{
                extend: 'collection',
                text: 'Opciones',
                orientation: 'landscape',
                buttons: [{
                    text: 'Copiar',
                    extend: 'copy',
                }, {
                    extend: 'pdf'
                }, {
                    extend: 'csv'
                }, {
                    extend: 'excel'
                }, {
                    text: 'Imprimir',
                    extend: 'print'
                }]
            },
            {
                extend: 'colvis',
                text: 'Visor de columnas',
                collectionLayout: 'fixed three-column'
            }],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>
