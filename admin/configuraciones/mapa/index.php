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
                <h1>Mapa Interactivo de Salones</h1>
            </div>

            <!-- Sección para el mapa -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Mapa de la Universidad</h3>
                        </div>
                        <div class="card-body">
                            <!-- Mapa SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="-100 -650 1400 1200" style="width: 100%; height: 500px; border: 1px solid #ccc;">
                                <!-- Incluir áreas específicas desde archivos externos -->
                                <a href="detalles_edificio_d.php" style="cursor: pointer;"><?php include('edificio_d.php'); ?></a>
                                <?php include('auditorio.php'); ?>
                                <a href="detalles_edificio_b.php" style="cursor: pointer;"><?php include('edificio_b.php'); ?></a>
                                <?php include('cafeteria.php'); ?>
                                <?php include('Laboratorio_P2.php'); ?>
                                <?php include('Laboratorio_P1.php'); ?>
                                <?php include('explanada.php'); ?>
                                <?php include('administracion.php'); ?>
                                <?php include('cancha_futbol.php'); ?>
                                <?php include('estacionamiento.php'); ?>
                                <a href="detalles_edificio_a.php" style="cursor: pointer;"><?php include('edificio_a.php'); ?></a>

                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('../../../admin/layout/parte2.php');
include('../../../layout/mensajes.php');
?>

<!-- Tooltip y Scripts -->
<style>
    rect {
        cursor: pointer;
        transition: opacity 0.3s;
    }

    rect:hover {
        opacity: 1;
        stroke-width: 2px;
    }

    .tooltip {
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: #fff;
        padding: 5px 10px;
        border-radius: 5px;
        display: none;
        z-index: 1000;
        font-size: 12px;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const infoAreas = {
            "cancha_futbol": { nombre: "Cancha de Fútbol", detalle: "Área de deportes al aire libre." },
            "estacionamiento": { nombre: "Estacionamiento", detalle: "Capacidad para 50 vehículos." },
            "edificio_a": { nombre: "Edificio A", detalle: "Salones y oficinas administrativas." }
        };

        const tooltip = document.createElement("div");
        tooltip.className = "tooltip";
        document.body.appendChild(tooltip);

        document.querySelectorAll("rect, circle, line").forEach(area => {
            area.addEventListener("mouseenter", function (e) {
                const info = infoAreas[this.id];
                if (info) {
                    tooltip.innerHTML = `<b>${info.nombre}</b><br>${info.detalle}`;
                    tooltip.style.display = "block";
                    tooltip.style.left = `${e.pageX + 10}px`;
                    tooltip.style.top = `${e.pageY + 10}px`;
                }
            });

            area.addEventListener("mousemove", function (e) {
                tooltip.style.left = `${e.pageX + 10}px`;
                tooltip.style.top = `${e.pageY + 10}px`;
            });

            area.addEventListener("mouseleave", function () {
                tooltip.style.display = "none";
            });
        });
    });
</script>
