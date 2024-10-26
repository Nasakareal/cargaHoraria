<?php

header('Content-Type: text/html; charset=utf-8');

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/grupos/listado_de_grupos.php');  // Controlador de grupos

// Contador de grupos
$total_groups = count($groups);

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Gestión de Grupos</h1>
                <p>Total de Grupos Registrados: <?= $total_groups; ?></p>
                <form action="../../app/controllers/vaciados/vaciar_grupos.php" method="post" id="formVaciarGrupos">
                    <button type="button" class="btn btn-danger" onclick="confirmarVaciado()">
                        <i class="bi bi-trash"></i> Vaciar Tabla de Grupos
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
?>

<script>
    function confirmarVaciado() {
        Swal.fire({
            title: 'Vaciar Grupos',
            text: '¿Desea eliminar todos los grupos y sus relaciones en la base de datos?',
            icon: 'warning',
            showDenyButton: true,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: '#a5161d',
            denyButtonColor: '#007bff',
            denyButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) { 
                document.getElementById('formVaciarGrupos').submit();
            }
        });
    }
</script>
