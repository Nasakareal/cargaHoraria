<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

/* Consulta para obtener la lista de profesores sin duplicar filas */
$query = "
    SELECT 
        t.teacher_id, 
        t.teacher_name, 
        t.es_local
    FROM 
        teachers t
    GROUP BY 
        t.teacher_id, t.teacher_name, t.es_local
";

$result = $pdo->query($query);
$profesores = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Autoasignación de Horarios para Profesores</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Listado de Profesores</h3>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center">Número</th>
                                        <th class="text-center">Nombre del Profesor</th>
                                        <th class="text-center">Es Local</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_profesores = 0;
                                foreach ($profesores as $profesor) {
                                    $contador_profesores++;
                                    ?>
                                    <tr>
                                        <td style="text-align: center"><?= $contador_profesores; ?></td>
                                        <td class="text-center"><?= htmlspecialchars($profesor['teacher_name']); ?></td>
                                        <!-- Verificar correctamente si el profesor es local o foráneo -->
                                        <td class="text-center"><?= $profesor['es_local'] == 1 ? 'Sí (Local)' : 'No (Foráneo)'; ?></td>
                                        <td class="text-center">
                                            <!-- Botón para ver el horario del profesor -->
                                            <a href="show.php?id=<?= $profesor['teacher_id']; ?>" class="btn btn-info btn-sm">
                                                <i class="bi bi-eye"></i> Ver Horario
                                            </a>
                                        </td>
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
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Profesores",
                "infoEmpty": "Mostrando 0 a 0 de 0 Profesores",
                "infoFiltered": "(Filtrado de _MAX_ total Profesores)",
                "lengthMenu": "Mostrar _MENU_ Profesores",
                "loadingRecords": "Cargando...",
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
