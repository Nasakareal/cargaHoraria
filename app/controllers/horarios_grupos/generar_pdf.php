<?php
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

/* Configurar Dompdf */
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

/* Verificar datos */
if (!isset($_POST['horarios']) || empty($_POST['horarios'])) {
    die('Error: No se enviaron datos para generar el PDF.');
}

/* Obtener los datos */
$horarios = $_POST['horarios'];

/* Ruta de la imagen y conversión a base64 */
$imagePath = realpath(__DIR__ . '/../../../templates/encabezado_horario.png');

if (!$imagePath) {
    die('Error: La ruta al encabezado no es válida.');
}

$imageData = base64_encode(file_get_contents($imagePath));
$imageBase64 = 'data:image/png;base64,' . $imageData;

/* Generar contenido HTML */
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario Escolar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 100%;
            height: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
            font-size: 12px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="' . $imageBase64 . '" alt="Encabezado">
    </div>
    <table>
        <thead>
            <tr>
                <th>Hora/Día</th>
                <th>Lunes</th>
                <th>Martes</th>
                <th>Miércoles</th>
                <th>Jueves</th>
                <th>Viernes</th>
            </tr>
        </thead>
        <tbody>';

/* Rellenar las filas dinámicamente */
foreach ($horarios as $fila) {
    $html .= '<tr>';
    foreach ($fila as $celda) {
        $html .= '<td>' . htmlspecialchars($celda) . '</td>';
    }
    $html .= '</tr>';
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

/* Cargar el contenido en Dompdf */
$dompdf->loadHtml($html);

/* Configurar tamaño y orientación del PDF */
$dompdf->setPaper('A4', 'landscape');

/* Renderizar el PDF */
$dompdf->render();

/* Enviar el PDF al navegador */
$dompdf->stream('Horario_Personalizado.pdf', ['Attachment' => false]);
exit;
?>
