<?php
header('Content-Type: application/json');
require_once('../includes/conexion.php');

if ($conexion->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conexion->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['tarea_id'])) {
    $tarea_id = $data['tarea_id'];

    // Primero, eliminar los registros relacionados en 'tarea_asignados'
    $delete_asignados_stmt = $conexion->prepare("DELETE FROM tarea_asignados WHERE tarea_id = ?");
    $delete_asignados_stmt->bind_param('i', $tarea_id);
    $delete_asignados_stmt->execute();
    $delete_asignados_stmt->close();

    // Luego, eliminar la tarea de la tabla 'tareas'
    $delete_tarea_stmt = $conexion->prepare("DELETE FROM tareas WHERE id = ?");
    $delete_tarea_stmt->bind_param('i', $tarea_id);

    if ($delete_tarea_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Tarea eliminada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la tarea: ' . $delete_tarea_stmt->error]);
    }

    $delete_tarea_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ID de tarea no proporcionado']);
}

$conexion->close();
?>
