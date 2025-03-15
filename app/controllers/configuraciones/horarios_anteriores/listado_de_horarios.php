<?php
include ('../../../../app/config.php');

$sql = "SELECT * FROM schedule_history ORDER BY fecha_registro DESC";
$result = mysqli_query($conn, $sql);

echo "<table border='1'>";
echo "<tr><th>Profesor</th><th>Materia</th><th>Grupo</th><th>Salón</th><th>Día</th><th>Hora Inicio</th><th>Hora Fin</th><th>Fecha Registro</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>{$row['teacher_id']}</td>
            <td>{$row['subject_id']}</td>
            <td>{$row['group_id']}</td>
            <td>{$row['classroom_id']}</td>
            <td>{$row['schedule_day']}</td>
            <td>{$row['start_time']}</td>
            <td>{$row['end_time']}</td>
            <td>{$row['fecha_registro']}</td>
          </tr>";
}

echo "</table>";
mysqli_close($conn);
?>
