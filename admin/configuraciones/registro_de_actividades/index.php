<?php
include ('../../../app/config.php');
include('../../../app/helpers/verificar_admin.php');
include ('../../../admin/layout/parte1.php');

$query = $pdo->prepare("SELECT * FROM registro_eventos ORDER BY fyh_creacion DESC");
$query->execute();
$registros = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
      <div class="container">
        <div class="row">
          <h1>Registro de Actividades</h1>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">Historial de Actividades del Sistema</h3>
                    </div>

                    <div class="card-body">
                    <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th><center>Número</center></th>
                                <th><center>Email del usuario</center></th>
                                <th><center>Acción</center></th>
                                <th><center>Descripción</center></th>
                                <th><center>IP</center></th>
                                <th><center>Fecha y hora</center></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $contador_registros = 0;
                            foreach ($registros as $registro){
                                $contador_registros++; ?>
                            <tr>
                                <td style="text-align: center"><?=$contador_registros;?></td>
                                <td><?=$registro['usuario_email'];?></td>
                                <td><?=$registro['accion'];?></td>
                                <td><?=$registro['descripcion'];?></td>
                                <td><?=$registro['ip_usuario'];?></td>
                                <td><?=$registro['fyh_creacion'];?></td>
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
include ('../../../admin/layout/parte2.php');
include ('../../../layout/mensajes.php');
?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
              "emptyTable": "No hay información",
              "info": "Mostrando _START_ a _END_ de _TOTAL_ Registros",
              "infoEmpty": "Mostrando 0 a 0 de 0 Registros",
              "infoFiltered": "(Filtrado de _MAX_ total Registros)",
              "lengthMenu": "Mostrar _MENU_ Registros",
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
            "responsive": true, "lengthChange": true, "autoWidth": false,
            buttons: [{
              extend: 'collection',
              text: 'Opciones',
              buttons: [{
                extend: 'copy',
                text: 'Copiar'
              }, {
                extend: 'pdf',
                text: 'Descargar PDF'
              }, {
                extend: 'csv',
                text: 'Descargar CSV'
              }, {
                extend: 'excel',
                text: 'Descargar Excel'
              }, {
                extend: 'print',
                text: 'Imprimir'
              }]
            },
              {
                extend: 'colvis',
                text: 'Visor de columnas',
                collectionLayout: 'fixed three-column'
              }
            ],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>
