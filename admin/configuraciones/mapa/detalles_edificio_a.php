<?php
include('../../../app/config.php');
include('../../../app/helpers/verificar_admin.php');
include('../../../admin/layout/parte1.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Edificio A - Salones</h1>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Mapa de los Salones del Edificio A</h3>
                        </div>
                        <div class="card-body">
                            <!-- Mapa SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -50 500 300" style="width: 100%; height: 500px; border: 1px solid #ccc;">
                                <!-- Incluir salones del Edificio A -->
                                <?php include('salones_edificio_a.php'); ?>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar información del salón -->
<div id="editModal" class="modal">
    <form id="editForm">
        <h4 id="modalTitle">Editar Salón</h4>
        <div class="form-group">
            <label for="salonName">Nombre del salón:</label>
            <input type="text" id="salonName" name="salonName" class="form-control" readonly>
        </div>
        <div class="form-group">
            <label for="salonCapacity">Capacidad:</label>
            <input type="number" id="salonCapacity" name="salonCapacity" class="form-control" readonly>
        </div>
        <div class="form-group">
            <label for="salonState">Estado:</label>
            <select id="salonState" name="salonState" class="form-control">
                <option value="buen-estado">Buen Estado</option>
                <option value="estado-medio">Estado Medio</option>
                <option value="mal-estado">Mal Estado</option>
            </select>
        </div>
        <input type="hidden" id="salonId" name="salonId">
        <button type="button" class="btn btn-primary" onclick="saveChanges()">Guardar Cambios</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
    </form>
</div>

<?php
include('../../../admin/layout/parte2.php');
include('../../../layout/mensajes.php');
?>

<!-- Estilos y Scripts -->
<style>
    rect {
        cursor: pointer;
        transition: opacity 0.3s;
    }

    rect:hover {
        opacity: 1;
        stroke-width: 2px;
    }

    .modal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        z-index: 1050;
        width: 400px;
        max-width: 90%;
    }

    .tooltip {
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: #fff;
        padding: 5px;
        border-radius: 5px;
        display: none;
        z-index: 1000;
        font-size: 12px;
    }

    .buen-estado {
        fill: #FFFFFF;
    }

    .estado-medio {
        fill: #FFFF00;
    }

    .mal-estado {
        fill: #FF0000;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tooltip = document.createElement("div");
        tooltip.className = "tooltip";
        document.body.appendChild(tooltip);

        const salones = {
            "classroom_A1": { id: "classroom_A1", nombre: "Salón 1", capacidad: 40, estado: "buen-estado" },
            "classroom_A2": { id: "classroom_A2", nombre: "Salón 2", capacidad: 30, estado: "estado-medio" },
            "classroom_A3": { id: "classroom_A3", nombre: "Salón 3", capacidad: 35, estado: "mal-estado" },
            "classroom_A4": { id: "classroom_A4", nombre: "Salón 4", capacidad: 25, estado: "buen-estado" },
            "classroom_A5": { id: "classroom_A5", nombre: "Salón 5", capacidad: 20, estado: "estado-medio" },
            "classroom_A6": { id: "classroom_A6", nombre: "Salón 6", capacidad: 30, estado: "mal-estado" },
            "classroom_A7": { id: "classroom_A7", nombre: "Salón 7", capacidad: 30, estado: "buen-estado" },
            "classroom_A8": { id: "classroom_A8", nombre: "Salón 8", capacidad: 20, estado: "estado-medio" },
            "classroom_A9": { id: "classroom_A9", nombre: "Salón 9", capacidad: 15, estado: "mal-estado" },
        };

        document.querySelectorAll("rect").forEach(area => {
            area.addEventListener("click", function () {
                const salon = salones[this.id];
                if (salon) {
                    document.getElementById("modalTitle").innerText = `Editar ${salon.nombre}`;
                    document.getElementById("salonName").value = salon.nombre;
                    document.getElementById("salonCapacity").value = salon.capacidad;
                    document.getElementById("salonState").value = salon.estado;
                    document.getElementById("salonId").value = salon.id;

                    document.getElementById("editModal").style.display = "block";
                }
            });
        });
    });

    function closeModal() {
        document.getElementById("editModal").style.display = "none";
    }

    function saveChanges() {
        const salonId = document.getElementById("salonId").value;
        const nuevoEstado = document.getElementById("salonState").value;

        fetch('actualizar_estado_salon.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: salonId, estado: nuevoEstado })
        })
        .then(response => {
            if (response.ok) {
                alert("Estado actualizado correctamente.");
                document.getElementById(salonId).classList = nuevoEstado;
                closeModal();
            } else {
                alert("Error al guardar el estado.");
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
