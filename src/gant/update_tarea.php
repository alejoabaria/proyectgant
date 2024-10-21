<?php
header('Content-Type: application/json');
require_once('../includes/conexion.php');

if ($conexion->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conexion->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Error al decodificar JSON']);
    exit();
}

if (isset($data['id_tarea']) && isset($data['titulo']) && isset($data['descripcion']) && isset($data['fecha_inicio']) && isset($data['fecha_fin']) && isset($data['estado_id']) && isset($data['asignado_a'])) {
    $idTarea = (int)$data['id_tarea'];
    $titulo = $data['titulo'];
    $descripcion = isset($data['descripcion']) ? $data['descripcion'] : '';
    $fechaInicio = $data['fecha_inicio'];
    $fechaFin = $data['fecha_fin'];
    $estado_id = (int)$data['estado_id'];
    $asignado_a = isset($data['asignado_a']) ? (int)$data['asignado_a'] : NULL;

    $stmt = $conexion->prepare("UPDATE tareas SET titulo = ?, descripcion = ?, fecha_inicio = ?, fecha_fin = ?, estado_id = ?, asignado_a = ? WHERE id = ?");
    $stmt->bind_param('ssssiii', $titulo, $descripcion, $fechaInicio, $fechaFin, $estado_id, $asignado_a, $idTarea);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Tarea actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar tarea: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
}

$conexion->close();
?>
