<?php

ob_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

error_reporting(E_ALL);

require '../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

/* Función para sanitizar el nombre del archivo */
function sanitizeFileName($filename) {

    $sanitized = preg_replace('/[^A-Za-z0-9\- ]/', '_', $filename);
    $sanitized = str_replace(' ', '_', $sanitized);
    $sanitized = substr($sanitized, 0, 50);
    return $sanitized;
}

$templatePath = __DIR__ . '/../../../templates/plantilla_horario.xlsx';

if (!file_exists($templatePath)) {
    error_log('Error: La plantilla no existe en ' . $templatePath);
    die('Error: La plantilla no existe.');
}

try {
    $spreadsheet = IOFactory::load($templatePath);
    error_log('Plantilla cargada correctamente.');
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    error_log('Error al cargar la plantilla: ' . $e->getMessage());
    die('Error: No se pudo cargar la plantilla.');
}

$sheet = $spreadsheet->getActiveSheet();

if (!isset($_POST['horarios']) || empty($_POST['horarios'])) {
    error_log('Error: No se enviaron datos para generar el archivo.');
    die('Error: No se enviaron datos para generar el archivo.');
}

$horarios = $_POST['horarios'];

$teacher_name = isset($_POST['teacher_name']) ? htmlspecialchars(trim($_POST['teacher_name']), ENT_QUOTES, 'UTF-8') : '';
$teacher_hours = isset($_POST['hours']) ? htmlspecialchars(trim($_POST['hours']), ENT_QUOTES, 'UTF-8') : '';
$clasificacion = isset($_POST['clasificacion']) ? htmlspecialchars(trim($_POST['clasificacion']), ENT_QUOTES, 'UTF-8') : 'Sin clasificar';

if (empty($teacher_name) || empty($teacher_hours)) {
    error_log('Error: Faltan datos del profesor.');
    die('Error: Faltan datos del profesor.');
}

$sheet->setCellValue('C3', strtoupper($teacher_name));
error_log('Nombre del profesor establecido en C3: ' . strtoupper($teacher_name));

$sheet->setCellValue('G4', $teacher_hours);
error_log('Horas del profesor establecidas en G4: ' . $teacher_hours);

$sheet->setCellValue('F25', $clasificacion);
$sheet->setCellValue('A3', 'Nombre del ' . $clasificacion . ':');
$sheet->getStyle('F25')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
$sheet->getStyle('F25')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);


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

foreach ($horarios as $fila) {
    if (!is_array($fila) || count($fila) < 7) {
        error_log('Formato de fila incorrecto: ' . print_r($fila, true));
        continue;
    }

    $hora = $fila[0];
    $dias = array_slice($fila, 1, 6);

    if (!isset($horaFila[$hora])) {
        error_log('Hora no reconocida: ' . $hora);
        continue;
    }

    $filaPlantilla = $horaFila[$hora];
    $columnaInicial = 'B';

    foreach ($dias as $index => $contenido) {
        $columnaAscii = ord($columnaInicial) + $index;
        if ($columnaAscii > ord('Z')) {
            error_log('Índice de columna fuera de rango para el contenido: ' . $contenido);
            continue;
        }
        $columna = chr($columnaAscii);
        $contenido_limpio = strip_tags($contenido);
        $contenido_limpio = htmlspecialchars_decode($contenido_limpio, ENT_QUOTES);
        $sheet->setCellValue($columna . $filaPlantilla, $contenido_limpio);
    }
}

/* Sanitizar el nombre del archivo usando el nombre del profesor */
$sanitized_teacher_name = sanitizeFileName($teacher_name);
$filename = "Horario_{$sanitized_teacher_name}.xlsx";

ob_end_clean();

/* Configurar encabezados para descargar el archivo como Excel con nombre personalizado */
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Cache-Control: max-age=0');
header('Expires: Fri, 11 Nov 2011 11:11:11 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');

try {
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    error_log('Archivo Excel generado y enviado correctamente: ' . $filename);
} catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
    error_log('Error al generar el archivo Excel: ' . $e->getMessage());
    die('Error: No se pudo generar el archivo Excel.');
}
exit;
?>
