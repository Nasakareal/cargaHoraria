<?php
header('Content-Type: text/html; charset=utf-8');

include('../../app/config.php');
include('../../app/helpers/verificar_admin.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/grupos/listado_de_grupos.php');
include('../../app/controllers/materias/listado_de_materias.php');
include('../../app/controllers/profesores/listado_de_profesores.php');

$total_groups = count($groups);
$total_subjects = count($subjects);
$total_teachers = count($teachers);

$query_assignments = $pdo->query("SELECT COUNT(*) AS total FROM teacher_subjects");
$total_assignments = $query_assignments->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Gestión de Vaciado de Tablas</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Tablas Registradas</h3>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center">Número</th>
                                        <th class="text-center">Nombre de Tabla</th>
                                        <th class="text-center">Total de Registros</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Fila para Grupos -->
                                    <tr>
                                        <td class="text-center">1</td>
                                        <td class="text-center">Grupos</td>
                                        <td class="text-center"><?= $total_groups; ?></td>
                                        <td class="text-center">
                                            <form action="../../app/controllers/vaciados/vaciar_grupos.php" method="post" id="formVaciarGrupos">
                                                <button type="button" class="btn btn-danger" onclick="confirmarVaciado('formVaciarGrupos', 'Grupos')">
                                                    <i class="bi bi-trash"></i> Vaciar Tabla de Grupos
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Fila para Materias -->
                                    <tr>
                                        <td class="text-center">2</td>
                                        <td class="text-center">Materias</td>
                                        <td class="text-center"><?= $total_subjects; ?></td>
                                        <td class="text-center">
                                            <form action="../../app/controllers/vaciados/vaciar_materias.php" method="post" id="formVaciarMaterias">
                                                <button type="button" class="btn btn-danger" onclick="confirmarVaciado('formVaciarMaterias', 'Materias')">
                                                    <i class="bi bi-trash"></i> Vaciar Tabla de Materias
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Fila para Profesores -->
                                    <tr>
                                        <td class="text-center">3</td>
                                        <td class="text-center">Profesores</td>
                                        <td class="text-center"><?= $total_teachers; ?></td>
                                        <td class="text-center">
                                            <form action="../../app/controllers/vaciados/vaciar_profesores.php" method="post" id="formVaciarProfesores">
                                                <button type="button" class="btn btn-danger" onclick="confirmarVaciado('formVaciarProfesores', 'Profesores')">
                                                    <i class="bi bi-trash"></i> Vaciar Tabla de Profesores
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Fila para Asignaciones de Materias a Profesores -->
                                    <tr>
                                        <td class="text-center">4</td>
                                        <td class="text-center">Asignaciones de Materias a Profesores</td>
                                        <td class="text-center"><?= $total_assignments; ?></td>
                                        <td class="text-center">
                                            <form action="../../app/controllers/vaciados/vaciar_asignaciones.php" method="post" id="formVaciarAsignaciones">
                                                <button type="button" class="btn btn-danger" onclick="confirmarVaciado('formVaciarAsignaciones', 'Asignaciones de Materias a Profesores')">
                                                    <i class="bi bi-trash"></i> Vaciar Asignaciones
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Fila para Schedule Assignments -->
                                    <tr>
                                        <td class="text-center">5</td>
                                        <td class="text-center">Horario Escolar</td>
                                        <td class="text-center">
                                            <?php
                                            $query_schedule_assignments = $pdo->query("SELECT COUNT(*) AS total FROM schedule_assignments");
                                            $total_schedule_assignments = $query_schedule_assignments->fetch(PDO::FETCH_ASSOC)['total'];
                                            echo $total_schedule_assignments;
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <form action="../../app/controllers/vaciados/vaciar_horarios.php" method="post" id="formVaciarScheduleAssignments">
                                                <button type="button" class="btn btn-danger" onclick="confirmarVaciado('formVaciarScheduleAssignments', 'Schedule Assignments')">
                                                    <i class="bi bi-trash"></i> Vaciar Horarios
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

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
include('../../admin/layout/mensajes.php');
?>

<script>
    function confirmarVaciado(formId, nombreTabla) {
        Swal.fire({
            title: 'Vaciar ' + nombreTabla,
            text: '¿Desea eliminar todos los registros de la tabla ' + nombreTabla + ' y sus relaciones en la base de datos?',
            icon: 'warning',
            showDenyButton: true,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: '#a5161d',
            denyButtonColor: '#007bff',
            denyButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) { 
                document.getElementById(formId).submit();
            }
        });
    }
</script>
