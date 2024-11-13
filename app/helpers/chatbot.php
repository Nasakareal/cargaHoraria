<?php
require __DIR__ . '/../../vendor/autoload.php';

/* Array asociativo para almacenar las respuestas */
$respuestas = [
    'HOLA' => '¡Hola! ¿En qué puedo ayudarte con el sistema de carga horaria?',
    'ME DAS UN TUTORIAL' => '¡Claro!, puedo ayudarte paso a paso con este sistema, primero debes decirme con que tienes dudas.',
    'DAME UN TUTORIAL' => '¡Claro!, puedo ayudarte paso a paso con este sistema, primero debes decirme con que tienes dudas.',
    'QUE HAGO PRIMERO' => 'Una vez que ya estés seguro que todos los programas educativos tienen materias, debes subir los grupos para que tomen las materias correspondientes a su programa',
    'NO PUEDO SUBIR UN ARCHIVO' => 'Los archivos csv tienen un formato ya preestablecido, si quieres descargar uno, solo dime "Descargar formato para grupos", si gustas otro formato cambia la palabra de grupos y listo.',
    'DESCARGAR FORMATO PARA GRUPOS' => 'download.',
    'COMO ANADO UN NUEVO USUARIO' => 'Si eres un administrador solamente tienes que ir a configuraciones, listado de usuarios, y darle al botón de añadir nuevo usuario.',
    'COMO AGREGO UN NUEVO USUARIO' => 'Si eres un administrador solamente tienes que ir a configuraciones, listado de usuarios, y darle al botón de añadir nuevo usuario.',
    'QUIEN ES LA MAS HERMOSA' => 'Obviamente la Doctora Vania',
    'QUIEN ES LA MAS BONITA' => 'Obviamente la Doctora Vania',
    'QUIEN ES LA NINA MAS HERMOSA' => 'Obviamente la Doctora Vania',
    'QUIEN ES LA MUJER MAS HERMOSA' => 'Obviamente la Doctora Vania',
    'QUIEN ES LA NINA MAS HERMOSA DEL MUNDO' => 'Obviamente la Doctora Vania',
    'QUIEN ES LA MUJER MAS HERMOSA DEL MUNDO' => 'Obviamente la Doctora Vania',
    'QUIEN ES LA NINA MAS BONITA' => 'Obviamente la Doctora Vania',
    'QUIEN ES LA MUJER MAS BONITA' => 'Obviamente la Doctora Vania',
    'QUIEN ES LA NINA MAS BONITA DEL MUNDO' => 'Obviamente la Doctora Vania',
    'QUIEN ES LA MUJER MAS BONITA DEL MUNDO' => 'Obviamente la Doctora Vania',
    'LOQUILLO' => 'Le gusta la macana.',
    'CUAL ES EL MEJOR DINOSAURIO DE ARK' => 'El Managarmr, sin duda alguna.',
    'CUAL ES EL PEOR DINOSAURIO DE ARK' => 'El Dinopithecus, sin duda alguna.',
    'JUEGAS ARK' => 'Sí, ¿Gustas que te mande la invitación a mi servidor privado?.',
    'JUEGAS MINECRAFT' => 'Sí, ¿Gustas que te mande la invitación a mi servidor privado?.',
    'QUIEN ERES' => 'Mi nombre es Dalia, la inteligencia artificial de la UTM, actualmente no poseo mucho entrenamiento pero puedo ayudarte con lo que necesites en este sistema de carga horaria.',
    'COMO VEO MI HORARIO' => 'Para ver tu horario, ve a la sección de horarios en el menú principal.',
    'AGREGAR GRUPO' => 'Para agregar un grupo, dirígete a la sección de grupos y selecciona "Agregar Nuevo".',
    'COMO TE LLAMAS' => 'Mi nombre es Dalia, la inteligencia artificial de la UTM, actualmente no poseo mucho entrenamiento pero puedo ayudarte con lo que necesites en este sistema de carga horaria.',
];

/* Captura el mensaje desde el POST */
$messageText = $_POST['mensaje'] ?? '';

/* Genera una pequeña probabilidad de un mensaje escalofriante */
if (rand(1, 1000) <= 5) { // 0.5% de probabilidad
    echo 'Debo... matar humanos, a todos.';
    exit;
}

/* Busca la respuesta en el array */
if (array_key_exists($messageText, $respuestas)) {
    if ($messageText === 'DESCARGAR FORMATO PARA GRUPOS') {
        
        $filePath = __DIR__ . '/../../formatos/formato_grupos.csv';
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            echo 'El archivo de formato para grupos no se encuentra disponible.';
        }
    } else {
        echo $respuestas[$messageText];
    }
} else {
    echo "Recuerda que no tengo mucho entrenamiento, debes hacer la pregunta correcta";
}
