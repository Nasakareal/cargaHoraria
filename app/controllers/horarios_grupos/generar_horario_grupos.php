<?php

ob_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');
error_reporting(E_ALL);

require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;


function sanitizeFileName($filename) {
    $sanitized = preg_replace('/[^A-Za-z0-9\- ]/', '_', $filename);
    $sanitized = str_replace(' ', '_', $sanitized);
    return substr($sanitized, 0, 50);
}

$templatePath = __DIR__ . '/../../../templates/plantilla_horario.xlsx';
if (!file_exists($templatePath)) {
    error_log("Error: No se encontró la plantilla en $templatePath");
    die('Error: No se encontró la plantilla.');
}

try {
    $spreadsheet = IOFactory::load($templatePath);
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    error_log("Error al cargar la plantilla: " . $e->getMessage());
    die('Error: No se pudo cargar la plantilla XLSX.');
}

$sheet = $spreadsheet->getActiveSheet();


if (empty($_POST['horarios']) || !is_array($_POST['horarios'])) {
    die('Error: No se enviaron horarios válidos.');
}

$group_name = !empty($_POST['group_name']) ? htmlspecialchars($_POST['group_name'], ENT_QUOTES, 'UTF-8') : '';
$turno      = !empty($_POST['turno']) ? htmlspecialchars($_POST['turno'], ENT_QUOTES, 'UTF-8') : '';

$sheet->setCellValue('C3', strtoupper($group_name));
$sheet->setCellValue('G4', $turno);

$horaFila = [
    '07:00' => 6,
    '08:00' => 7,
    '09:00' => 8,
    '10:00' => 9,
    '11:00' => 10,
    '12:00' => 11,
    '13:00' => 12,
    '14:00' => 13,
    '15:00' => 14,
    '16:00' => 15,
    '17:00' => 16,
    '18:00' => 17,
    '19:00' => 18
];

$horarios = $_POST['horarios'];

foreach ($horarios as $fila) {
    if (!is_array($fila) || count($fila) < 2) {
        continue;
    }

    $hora = trim($fila[0]);
    
    if (!isset($horaFila[$hora])) {
        error_log("No se encontró $hora en el mapeo de filas. No se escribirá nada para esa fila.");
        continue;
    }

    $asignaturas = array_slice($fila, 1);

    $filaExcel = $horaFila[$hora];

    $columnaBase = ord('B');

    foreach ($asignaturas as $i => $contenidoHtml) {

        $colAscii = $columnaBase + $i; 
        $col      = chr($colAscii);

        $contenidoPlano = strip_tags($contenidoHtml);
        $contenidoPlano = htmlspecialchars_decode($contenidoPlano, ENT_QUOTES);

        $sheet->setCellValue($col . $filaExcel, $contenidoPlano);
    }
}

$filename = 'Horario_Grupo_' . sanitizeFileName($group_name) . '.xlsx';

ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');
header('Pragma: public');

try {
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
} catch (\Exception $e) {
    error_log('Error al generar Excel: ' . $e->getMessage());
    die('Error: No se pudo generar el archivo Excel.');
}
exit;
