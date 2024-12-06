<?php
require '../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

/* Ruta de la plantilla */
$templatePath = __DIR__ . '/../../../templates/plantilla_horario.xlsx';

/* Verificar si la plantilla existe */
if (!file_exists($templatePath)) {
    die('Error: La plantilla no existe.');
}

/* Cargar la plantilla */
$spreadsheet = IOFactory::load($templatePath);
$sheet = $spreadsheet->getActiveSheet();

/* Verificar que se enviaron los datos */
if (!isset($_POST['horarios']) || empty($_POST['horarios'])) {
    die('Error: No se enviaron datos para generar el archivo.');
}

/* Obtener los datos enviados desde el cliente */
$horarios = $_POST['horarios'];

/* Mapear los datos a la plantilla */
foreach ($horarios as $fila) {
    $hora = $fila[0];
    $dias = array_slice($fila, 1);

    /* Determinar la fila correspondiente en la plantilla */
    $horaFila = [
        '07:00' => 7,
        '08:00' => 10,
        '09:00' => 13,
        '10:00' => 16,
        '11:00' => 19,
        '12:00' => 22,
        '13:00' => 25,
        '14:00' => 28,
        '15:00' => 31,
        '16:00' => 34,
        '17:00' => 37,
        '18:00' => 40,
        '19:00' => 43
    ];

    if (!isset($horaFila[$hora])) {
        continue;
    }

    $filaPlantilla = $horaFila[$hora];
    $columnaInicial = 'B';

    /* Rellenar los días en la plantilla */
    foreach ($dias as $index => $contenido) {
        $columna = chr(ord($columnaInicial) + $index);
        $sheet->setCellValue($columna . $filaPlantilla, strip_tags($contenido));
    }
}

/* Configurar encabezados para descargar el archivo como Excel */
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Horario_Personalizado.xlsx"');

/* Generar y enviar el archivo Excel */
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');
exit;
?>
