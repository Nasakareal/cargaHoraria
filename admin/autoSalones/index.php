<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/grupos/listado_de_grupos.php');
include('../../app/controllers/salones/listado_de_salones.php');

// Obtener datos de grupos y salones asignados para mostrar
$sql_grupos = "
    SELECT g.group_id, g.group_name, g.volume AS capacidad_grupo, s.shift_name AS turn, g.classroom_assigned AS salon_asignado 
    FROM `groups` g
    JOIN shifts s ON g.turn_id = s.shift_id
    WHERE g.volume > 0 
    ORDER BY g.volume DESC";
$query_grupos = $pdo->prepare($sql_grupos);
$query_grupos->execute();
$grupos_con_salones = $query_grupos->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content Wrapper -->
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
                                
                                <form action="../../app/controllers/autoSalones/logica.php" method="POST" style="display:inline;">
                                    <button type="submit" name="auto-assign" class="btn btn-secondary">
                                        <i class="bi bi-arrow-repeat"></i> Asignar Salones
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center"><center>Numero</center></th>
                                        <th class="text-center"><center>Nombre del Grupo</center></th>
                                        <th class="text-center"><center>Turno</center></th>
                                        <th class="text-center"><center>Volumen</center></th>
                                        <th class="text-center"><center>Salón Asignado</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (is_array($grupos_con_salones) && count($grupos_con_salones) > 0) {
                                    $contador_grupos = 0;
                                    foreach ($grupos_con_salones as $grupo) {
                                        $contador_grupos++;
                                        ?>
                                        <tr>
                                            <td style="text-align: center"><?= $contador_grupos; ?></td>
                                            <td class="text-center"><?= $grupo['group_name']; ?></td>
                                            <td class="text-center"><?= $grupo['turn']; ?></td>
                                            <td style="text-align: center"><?= $grupo['capacidad_grupo']; ?></td>
                                            <td style="text-align: center"><?= $grupo['salon_asignado'] ?? 'No disponible'; ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>No hay información disponible</td></tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Grupos",
                "infoEmpty": "Mostrando 0 a 0 de 0 Grupos",
                "infoFiltered": "(Filtrado de _MAX_ total Grupos)",
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
