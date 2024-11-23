<?php
if (!isset($_POST['horarios']) || empty($_POST['horarios'])) {
    die('Error: No se enviaron datos para generar la vista.');
}

$horarios = $_POST['horarios'];

// Ruta absoluta del encabezado (imagen del logotipo o encabezado)
$imagePath = realpath(__DIR__ . '/../../../templates/encabezado_horario.png');

if (!$imagePath) {
    die('Error: La ruta al encabezado no es válida.');
}

// Convertir la imagen en base64 para asegurar compatibilidad en la vista HTML
$imageData = base64_encode(file_get_contents($imagePath));
$imageBase64 = 'data:image/png;base64,' . $imageData;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario Personalizado</title>
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
        h1 {
            text-align: center;
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
        <img src="<?= $imageBase64 ?>" alt="Encabezado">
    </div>
    <h1>Horario Personalizado</h1>
    <table>
        <thead>
            <tr>
                <th>Hora/Día</th>
                <th>Lunes</th>
                <th>Martes</th>
                <th>Miércoles</th>
                <th>Jueves</th>
                <th>Viernes</th>
                <th>Sábado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($horarios as $fila): ?>
                <tr>
                    <?php foreach ($fila as $celda): ?>
                        <td><?= htmlspecialchars($celda); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <script>
        window.print();
    </script>
</body>
</html>
