<?php


include_once('../../app/config.php');  // Usar include_once para evitar inclusión duplicada
include_once('../../admin/layout/parte1.php');
include_once('../../app/controllers/parte1.php');

// Verificar que $pdo esté definido y conectado correctamente
if (!isset($pdo)) {
    echo "Error: No se pudo conectar a la base de datos.";
    exit;
}

// Consulta para obtener la lista de profesores
$sql_profesores = "SELECT teacher_id, teacher_name, program_id FROM teachers WHERE estado = 'ACTIVO'";
$stmt_profesores = $pdo->prepare($sql_profesores);
$stmt_profesores->execute();
$profesores = $stmt_profesores->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Profesores</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Profesores registrados</h3>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center">Número</th>
                                        <th class="text-center">Nombre del Profesor</th>
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
                                        <td class="text-center">
                                            <!-- Botón Asignar Horario que redirige a asignar_horario_profesor.php con el ID del profesor -->
                                            <a href="asignar_horario_profesor.php?teacher_id=<?= $profesor['teacher_id']; ?>" class="btn btn-info btn-sm">
                                                <i class="bi bi-calendar-plus"></i> Asignar Horario
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
include_once('../../admin/layout/parte2.php');
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
