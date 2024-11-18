$(document).ready(function () {
    $('#confirm_group').click(function () {
        var group_id = $('#grupos_disponibles').val(); // Obtener el ID del grupo seleccionado
        console.log("Grupo seleccionado:", group_id);

        if (group_id) {
            // Agregar el grupo al campo oculto `grupos_asignados`
            var gruposAsignados = $('#grupos_asignados').val().split(',').filter(Boolean);
            if (!gruposAsignados.includes(group_id)) {
                gruposAsignados.push(group_id);
                $('#grupos_asignados').val(gruposAsignados.join(','));
            }

            // Realizar la solicitud AJAX para obtener materias
            $.ajax({
                url: '../../app/controllers/relacion_profesor_materias/obtener_materias.php',
                type: 'POST',
                data: { group_id: group_id },
                success: function (response) {
                    console.log("Materias disponibles:", response);
                    $('#materias_disponibles').html(response); // Actualizar el listado de materias
                },
                error: function () {
                    console.error('Error al cargar las materias.');
                }
            });
        } else {
            alert("Por favor, selecciona un grupo válido.");
        }
    });

    function calcularTotalHoras() {
        var totalHoras = 0;
        $('#materias_asignadas option').each(function () {
            totalHoras += parseInt($(this).data('hours')) || 0;
        });
        $('#total_hours').val(totalHoras);
    }

    $('#add_subject').click(function () {
        $('#materias_disponibles option:selected').each(function () {
            $(this).appendTo('#materias_asignadas');
        });
        calcularTotalHoras();
    });

    $('#remove_subject').click(function () {
        $('#materias_asignadas option:selected').each(function () {
            $(this).appendTo('#materias_disponibles');
        });
        calcularTotalHoras();
    });

    calcularTotalHoras();
});
