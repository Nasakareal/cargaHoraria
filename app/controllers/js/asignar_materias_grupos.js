$(document).ready(function () {
    // Inicializar selección de materias asignadas y grupos asignados
    $('#materias_asignadas option').prop('selected', true);
    $('#grupos_asignados option').prop('selected', true);

    // Evento para cargar materias al seleccionar un grupo
    $(document).ready(function () {
        // Evento al hacer clic en "Seleccionar Grupo"
        $('#confirm_group').click(function () {
            var group_id = $('#grupos_disponibles').val(); // Obtener el ID del grupo seleccionado
            console.log("Grupo seleccionado:", group_id); // Mostrar en consola para depuración

            if (group_id) {
                // Solicitud AJAX para obtener las materias del grupo
                $.ajax({
                    url: '../../app/controllers/relacion_profesor_materias/obtener_materias.php',
                    type: 'POST',
                    data: { group_id: group_id },
                    success: function (response) {
                        console.log("Materias disponibles:", response); // Verificar respuesta en consola
                        $('#materias_disponibles').html(response); // Actualizar el select de materias
                    },
                    error: function () {
                        console.log('Error en la solicitud de materias');
                    }
                });
            } else {
                console.log("No se seleccionó un grupo válido.");
                $('#materias_disponibles').html('<option value="">Seleccione un grupo válido</option>');
            }
        });
    });





    // Función para mover materias entre listas
    $('#add_subject').click(function () {
        $('#materias_disponibles option:selected').each(function () {
            $(this).appendTo('#materias_asignadas');
        });
        calcularTotalHoras(); // Actualizar el total de horas asignadas
        $('#materias_asignadas option').prop('selected', true); // Seleccionar todo
    });

    $('#remove_subject').click(function () {
        $('#materias_asignadas option:selected').each(function () {
            $(this).appendTo('#materias_disponibles');
        });
        calcularTotalHoras(); // Actualizar el total de horas asignadas
        $('#materias_asignadas option').prop('selected', true); // Seleccionar todo
    });

    // Función para calcular el total de horas asignadas
    function calcularTotalHoras() {
        var totalHoras = 0;
        $('#materias_asignadas option').each(function () {
            totalHoras += parseInt($(this).data('hours')) || 0;
        });
        $('#total_hours').val(totalHoras); // Actualizar el campo de total de horas
    }

    // Inicializar el cálculo de horas al cargar la página
    calcularTotalHoras();

    // Log para depuración
    console.log("Archivo asignar_materias_grupos.js cargado correctamente.");
});
