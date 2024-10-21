<?php
header('Content-Type: application/json');
require_once('../includes/conexion.php');

$proyecto_id = isset($_GET['proyecto_id']) ? intval($_GET['proyecto_id']) : null;

$query = "SELECT id AS id, titulo AS text, descripcion, estado_id, fecha_inicio AS start_date, fecha_fin AS end_date, sprint_id AS parent, 'task' AS type 
          FROM tareas 
          WHERE sprint_id IN (SELECT id FROM sprints WHERE proyecto_id = ?)";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $proyecto_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {
    $tasks = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['data' => $tasks]);
} else {
    echo json_encode(['data' => []]);
}

$conexion->close();

?>
