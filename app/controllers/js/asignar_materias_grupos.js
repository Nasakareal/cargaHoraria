$(document).ready(function () {
    /* Seleccionar todas las opciones en 'materias_asignadas' y 'grupos_asignados' al cargar la página */
    $('#materias_asignadas option').prop('selected', true);
    $('#grupos_asignados option').prop('selected', true);

    /* Evento al cambiar el programa o cuatrimestre */
    $('#programa_id, #cuatrimestre_id').change(function () {
        var programa_id = $('#programa_id').val();
        var cuatrimestre_id = $('#cuatrimestre_id').val();

        if (programa_id && cuatrimestre_id) {
            /* Obtener IDs de materias asignadas */
            var assignedSubjectIds = [];
            $('#materias_asignadas option').each(function () {
                assignedSubjectIds.push($(this).val());
            });

            /* Solicitud AJAX para obtener las materias disponibles */
            $.ajax({
                url: '../../app/controllers/relacion_profesor_materias/obtener_materias.php',
                type: 'POST',
                data: {
                    programa_id: programa_id,
                    cuatrimestre_id: cuatrimestre_id,
                    assigned_subjects: assignedSubjectIds
                },
                success: function (response) {
                    $('#materias_disponibles').html(response);
                },
                error: function () {
                    console.log('Error en la solicitud de materias');
                }
            });

            /* Obtener IDs de grupos asignados */
            var assignedGroupIds = [];
            $('#grupos_asignados option').each(function () {
                assignedGroupIds.push($(this).val());
            });

            /* Solicitud AJAX para obtener los grupos disponibles */
            $.ajax({
                url: '../../app/controllers/relacion_profesor_grupos/obtener_grupos.php',
                type: 'POST',
                data: {
                    programa_id: programa_id,
                    cuatrimestre_id: cuatrimestre_id,
                    assigned_groups: assignedGroupIds
                },
                success: function (response) {
                    $('#grupos_disponibles').html(response);
                },
                error: function () {
                    console.log('Error en la solicitud de grupos');
                }
            });
        }
    });

    /* Eventos para mover materias entre listas */
    $('#add_subject').click(function () {
        $('#materias_disponibles option:selected').each(function () {
            $(this).appendTo('#materias_asignadas');
        });
        calcularTotalHoras();
        /* Seleccionar todas las opciones en 'materias_asignadas' */
        $('#materias_asignadas option').prop('selected', true);
    });

    $('#remove_subject').click(function () {
        $('#materias_asignadas option:selected').each(function () {
            $(this).appendTo('#materias_disponibles');
        });
        calcularTotalHoras();
        /* Seleccionar todas las opciones en 'materias_asignadas' */
        $('#materias_asignadas option').prop('selected', true);
    });

    /* Eventos para mover grupos entre listas */
    $('#add_group').click(function () {
        $('#grupos_disponibles option:selected').each(function () {
            $(this).appendTo('#grupos_asignados');
        });
        /* Seleccionar todas las opciones en 'grupos_asignados' */
        $('#grupos_asignados option').prop('selected', true);
    });

    $('#remove_group').click(function () {
        $('#grupos_asignados option:selected').each(function () {
            $(this).appendTo('#grupos_disponibles');
        });
        /* Seleccionar todas las opciones en 'grupos_asignados' */
        $('#grupos_asignados option').prop('selected', true);
    });

    /* Seleccionar todas las opciones antes de enviar el formulario */
    $('form').submit(function () {
        $('#materias_asignadas option').prop('selected', true);
        $('#grupos_asignados option').prop('selected', true);
    });

    /* Función para calcular el total de horas asignadas */
    function calcularTotalHoras() {
        var totalHoras = 0;
        $('#materias_asignadas option').each(function () {
            totalHoras += parseInt($(this).data('hours'));
        });
        $('#total_hours').val(totalHoras);
    }

    /* Inicializar el cálculo de horas al cargar la página */
    calcularTotalHoras();
});