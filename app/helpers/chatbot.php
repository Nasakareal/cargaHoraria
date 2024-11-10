<?php
require __DIR__ . '/../../vendor/autoload.php';

/* Array asociativo para almacenar las respuestas */
$respuestas = [
    'Hola' => '¡Hola! ¿En qué puedo ayudarte con el sistema de carga horaria?',
    '¿Cómo veo mi horario?' => 'Para ver tu horario, ve a la sección de horarios en el menú principal.',
    'agregar grupo' => 'Para agregar un grupo, dirígete a la sección de grupos y selecciona "Agregar Nuevo".',
    '¿Cómo te llamas?' => 'Mi nombre es Dalia, la inteligencia artificial de la UTM, actualmente no poseo mucho entrenamiento pero puedo ayudarte con lo que necesitas en este sistema de carga horaria".',
    /* Añade más respuestas aquí según sea necesario */
];

/* Captura el mensaje desde el POST */
$messageText = $_POST['mensaje'] ?? '';

/* Busca la respuesta en el array */
if (array_key_exists($messageText, $respuestas)) {
    echo $respuestas[$messageText];
} else {
    echo "Recuerda que no tengo mucho entrenamiento, debes hacer la pregunta correcta";
}
