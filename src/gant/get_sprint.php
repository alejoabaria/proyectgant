<?php
header('Content-Type: application/json');
require_once('../includes/conexion.php');

if ($conexion->connect_error) {
    die("Connection failed: " . $conexion->connect_error);
}

$proyecto_id = isset($_GET['proyecto_id']) ? intval($_GET['proyecto_id']) : null;

$query = "SELECT id AS id, nombre AS text, descripcion, fecha_inicio AS start_date, fecha_fin AS end_date, estado_id, proyecto_id, 'sprint' AS type FROM sprints WHERE 1=1";

if (!is_null($proyecto_id)) {
    $query .= " AND proyecto_id = " . $proyecto_id;
}

$result = $conexion->query($query);

$sprints = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sprints[] = $row;
    }
}

echo json_encode(['data' => $sprints]);

$conexion->close();
?>