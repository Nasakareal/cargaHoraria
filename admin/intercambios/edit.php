<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/horarios_grupos/grupos_disponibles.php');
include('../../app/controllers/horarios_grupos/obtener_horario_grupo.php');
include('../../app/controllers/horarios_grupos/procesar_horario_grupo.php');
include('../../app/controllers/horarios_grupos/listado_asignaciones.php');


$group_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);


$asignaciones = listarAsignaciones($pdo);


if ($group_id) {
    $resultado = procesarHorarioGrupo($group_id, $pdo);

    if (isset($resultado['error'])) {
        echo $resultado['error'];
        include('../../layout/parte2.php');
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


$grupos = obtenerGrupos($pdo);
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Horarios por Grupo</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../../admin/horarios_grupos">Horarios</a></li>
                        <li class="breadcrumb-item active">Detalles</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

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

            <!-- Tabla de Horarios -->
            <?php if ($group_id): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Horario del Grupo: <?= htmlspecialchars($nombre_grupo); ?> (Turno: <?= htmlspecialchars($turno); ?>)</h3>
                                <div class="card-tools">
                                    <a href="../../admin/intercambios" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left-circle"></i> Volver</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="horarioTabla" class="table table-bordered table-hover">
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
                                                    <td ondblclick="abrirModalIntercambio('<?= htmlspecialchars($hora); ?>', '<?= htmlspecialchars($dia); ?>', '<?= $tabla_horarios[$hora][$dia] ?? ''; ?>')">
                                                        <?= $tabla_horarios[$hora][$dia] ?? ''; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para Intercambio de Asignaciones -->
<div id="intercambioModal" class="modal">
    <form id="intercambioForm">
        <h4 id="modalTitle">Intercambiar Asignación</h4>
        <div class="form-group">
            <label for="horaSeleccionada">Hora:</label>
            <input type="text" id="horaSeleccionada" name="horaSeleccionada" class="form-control" readonly>
        </div>
        <div class="form-group">
            <label for="diaSeleccionado">Día:</label>
            <input type="text" id="diaSeleccionado" name="diaSeleccionado" class="form-control" readonly>
        </div>
        <div class="form-group">
            <label for="asignacionActual">Asignación Actual:</label>
            <input type="text" id="asignacionActual" name="asignacionActual" class="form-control" readonly>
        </div>
        <div class="form-group">
            <label for="grupoSeleccionado">Grupo:</label>
            <select id="grupoSeleccionado" name="grupoSeleccionado" class="form-control" onchange="cargarAsignacionesPorGrupo()">
                <option value="">Seleccionar grupo</option>
                <?php foreach ($grupos as $grupo): ?>
                    <option value="<?= $grupo['group_id']; ?>">
                        <?= htmlspecialchars($grupo['group_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="nuevaAsignacion">Nueva Asignación:</label>
            <select id="nuevaAsignacion" name="nuevaAsignacion" class="form-control">
                <option value="">Seleccionar nueva asignación</option>
                <!-- Opciones dinámicas cargadas por JavaScript -->
            </select>
        </div>
        <button type="button" class="btn btn-primary" onclick="guardarIntercambio()">Guardar Intercambio</button>
        <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
    </form>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>


<!-- Estilos -->
<style>
    .modal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        z-index: 1050;
        width: 400px;
        max-width: 90%;
        overflow-y: auto;
    }
</style>

<script>
    function abrirModalIntercambio(hora, dia, asignacion) {
        document.getElementById('horaSeleccionada').value = hora;
        document.getElementById('diaSeleccionado').value = dia;
        document.getElementById('asignacionActual').value = asignacion;
        document.getElementById('grupoSeleccionado').value = "";
        document.getElementById('nuevaAsignacion').innerHTML = "<option value=''>Seleccionar nueva asignación</option>";
        document.getElementById('intercambioModal').style.display = 'block';
    }

    function cerrarModal() {
        document.getElementById('intercambioModal').style.display = 'none';
    }

    function cargarAsignacionesPorGrupo() {
    const grupoId = document.getElementById('grupoSeleccionado').value;
    const selectAsignaciones = document.getElementById('nuevaAsignacion');

    if (!grupoId) {
        console.log('No se seleccionó ningún grupo.');
        selectAsignaciones.innerHTML = "<option value=''>Seleccionar nueva asignación</option>";
        return;
    }

    console.log(`Cargando asignaciones para el grupo ID: ${grupoId}`);

    fetch('../../app/controllers/horarios_grupos/obtener_horarios_por_grupo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `group_id=${grupoId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Error del servidor:', data.error);
            alert(data.error);
            return;
        }

        console.log('Asignaciones obtenidas:', data);

        
        selectAsignaciones.innerHTML = "<option value=''>Seleccionar nueva asignación</option>";
        data.forEach(asignacion => {
            const option = document.createElement('option');
            option.value = asignacion.assignment_id;
            option.textContent = `${asignacion.schedule_day} ${asignacion.start_time} - ${asignacion.end_time} | ${asignacion.subject_name} | ${asignacion.teacher_name} | ${asignacion.classroom_name}`;
            selectAsignaciones.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error en la solicitud Fetch:', error);
        alert('Error al cargar asignaciones.');
    });
}



    function guardarIntercambio() {
        const hora = document.getElementById('horaSeleccionada').value;
        const dia = document.getElementById('diaSeleccionado').value;
        const nuevaAsignacion = document.getElementById('nuevaAsignacion').value;

        if (!nuevaAsignacion) {
            alert('Por favor, selecciona una nueva asignación.');
            return;
        }

        fetch('../../app/controllers/intercambios/intercambio_asignacion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ hora, dia, nuevaAsignacion })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Intercambio realizado correctamente.');
                location.reload();
            } else {
                alert('Error al realizar el intercambio.');
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>

