$(document).ready(function () {
    var teacher_id = $('input[name="teacher_id"]').val();

    /* Obtener el valor inicial de horas asignadas desde la base de datos */
    $.ajax({
        url: '../../app/controllers/relacion_profesor_materias/obtener_horas.php',
        type: 'POST',
        data: { teacher_id: teacher_id },
        success: function (response) {
            console.log("Horas iniciales del servidor:", response);
            var initialHours = parseInt(response) || 0;
            $('#total_hours').val(initialHours);
        },
        error: function () {
            console.error('Error al obtener las horas iniciales del profesor.');
            $('#total_hours').val(0);
        }
    });

    /* Confirmar grupo seleccionado */
    $('#confirm_group').click(function () {
        var group_id = $('#grupos_disponibles').val();
        console.log("Grupo seleccionado:", group_id);

        if (group_id) {
            // Verificar si ya hay materias asignadas
            if ($('#materias_asignadas option').length > 0) {
                // Si hay materias asignadas, mostrar un mensaje al usuario
                var confirmDelete = confirm("Ya tienes materias asignadas. ¿Quieres eliminarlas antes de seleccionar otro grupo?");

                // Si el usuario acepta, limpiar las materias asignadas y continuar
                if (confirmDelete) {
                    $('#materias_asignadas').empty(); // Limpiar materias asignadas
                    $('#total_hours').val(0); // Resetear horas
                    console.log("Materias asignadas eliminadas.");

                    // Continuar con la selección del grupo
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
                            $('#materias_disponibles').html(response);
                        },
                        error: function () {
                            console.error('Error al cargar las materias.');
                        }
                    });

                } else {
                    console.log("El usuario decidió no eliminar las materias.");
                }
            } else {
                // Si no hay materias asignadas, proceder normalmente
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
                        $('#materias_disponibles').html(response);
                    },
                    error: function () {
                        console.error('Error al cargar las materias.');
                    }
                });
            }

        } else {
            alert("Por favor, selecciona un grupo válido.");
        }
    });

    /* Calcular el total de horas asignadas */
    function calcularTotalHoras() {
        var totalHoras = parseInt($('#total_hours').val()) || 0;
        $('#materias_asignadas option').each(function () {
            totalHoras += parseInt($(this).data('hours')) || 0;
        });
        $('#total_hours').val(totalHoras);
    }

    /* Mover materias disponibles a asignadas */
    $('#add_subject').click(function () {
        $('#materias_disponibles option:selected').each(function () {
            $(this).appendTo('#materias_asignadas');
        });
        calcularTotalHoras();
    });

    /* Mover materias asignadas a disponibles */
    $('#remove_subject').click(function () {
        $('#materias_asignadas option:selected').each(function () {
            $(this).appendTo('#materias_disponibles');
        });
        calcularTotalHoras();
    });

    /* Seleccionar todas las materias asignadas antes de enviar el formulario */
    $('form').submit(function () {
        $('#materias_asignadas option').prop('selected', true);
    });
});
