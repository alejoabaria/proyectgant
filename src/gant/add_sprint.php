<?php
header('Content-Type: application/json');
require_once('../includes/conexion.php');

if ($conexion->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conexion->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['text']) && isset($data['start_date']) && isset($data['end_date']) && isset($data['estado_id']) && isset($data['proyecto_id'])) {
    $text = $data['text'];
    $start_date = $data['start_date'];
    $end_date = $data['end_date'];
    $description = isset($data['description']) ? $data['description'] : '';
    $estado_id = $data['estado_id'];
    $proyecto_id = $data['proyecto_id'];

    $stmt = $conexion->prepare("INSERT INTO sprints (nombre, descripcion, fecha_inicio, fecha_fin, estado_id, proyecto_id) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die(json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $conexion->error]));
    }
    $stmt->bind_param("ssssii", $text, $description, $start_date, $end_date, $estado_id, $proyecto_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Datos faltantes']);
}

$conexion->close();
?>
