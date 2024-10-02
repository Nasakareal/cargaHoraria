<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/grupos/listado_de_grupos.php'); // Incluye el listado de grupos
include('../../app/controllers/salones/listado_de_salones.php'); // Incluye el listado de salones

// Lógica para asignar salones según la capacidad
function asignarSalones($pdo, $capacidad_grupo, $salones_asignados) {
    // Asegúrate de que el nombre de la columna es correcto
    $salones_asignados_str = implode(',', array_map('intval', $salones_asignados));
    $sql = "SELECT * FROM classrooms WHERE capacity >= :capacidad_grupo";

    if (!empty($salones_asignados)) {
        // Cambia 'id' por el nombre correcto de la columna
        $sql .= " AND classroom_id NOT IN ($salones_asignados_str)"; // Asegúrate de que 'classroom_id' es el nombre correcto
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
                                        <th><center>Numero</center></th>
                                        <th><center>Nombre del Grupo</center></th>
                                        <th><center>Capacidad</center></th>
                                        <th><center>Salón Asignado</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_grupos = 0;
                                $salones_asignados = []; // Inicializamos el array de salones asignados
                                foreach ($groups as $group) {
                                    $contador_grupos++;
                                    $capacidad_grupo = $group['volumen_grupo']; // Usa el volumen del grupo como capacidad
                                    
                                    $salones_asignados_temp = asignarSalones($pdo, $capacidad_grupo, $salones_asignados);
                                    $salon_asignado = null;

                                    if (!empty($salones_asignados_temp)) {
                                        $salon_asignado = $salones_asignados_temp[0]; // Tomamos el primer salón disponible
                                        $salones_asignados[] = $salon_asignado['classroom_id']; // Cambia 'id' por 'classroom_id'
                                    }
                                    
                                    $salon_nombre = $salon_asignado ? $salon_asignado['classroom_name'] : 'No disponible';
                                    ?>
                                    <tr>
                                        <td style="text-align: center"><?= $contador_grupos; ?></td>
                                        <td><?= $group['grupo']; ?></td>
                                        <td style="text-align: center"><?= $capacidad_grupo; ?></td>
                                        <td style="text-align: center"><?= $salon_nombre; ?></td>
                                        <td style="text-align: center">
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <a href="show.php?id=<?= $group['group_id']; ?>" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                <a href="edit.php?id=<?= $group['group_id']; ?>" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                <form action="<?= APP_URL; ?>/app/controllers/grupos/delete.php" onclick="preguntar<?= $group['group_id']; ?>(event)" method="post" id="miFormulario<?= $group['group_id']; ?>">
                                                    <input type="hidden" name="group_id" value="<?= $group['group_id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 0px 5px 5px 0px"><i class="bi bi-trash"></i></button>
                                                </form>

                                                <script>
                                                    function preguntar<?= $group['group_id']; ?>(event){
                                                        event.preventDefault();
                                                        Swal.fire({
                                                            title: 'Eliminar Grupo',
                                                            text: '¿Desea eliminar este Grupo?',
                                                            icon: 'question',
                                                            showDenyButton: true,
                                                            confirmButtonText: 'Eliminar',
                                                            confirmButtonColor: '#a5161d',
                                                            denyButtonColor: '#007bff',
                                                            denyButtonText: 'Cancelar',
                                                        }).then((result) => {
                                                            if (result.isConfirmed) { 
                                                                var form = $('#miFormulario<?= $group['group_id']; ?>');
                                                                form.submit();
                                                            }
                                                        });
                                                        return false;
                                                    }
                                                </script>
                                            </div>
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
