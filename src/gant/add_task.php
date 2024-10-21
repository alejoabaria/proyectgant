<?php
header('Content-Type: application/json');
require_once('../includes/conexion.php');

if ($conexion->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conexion->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['titulo']) && isset($data['estado_id']) && isset($data['sprint_id'])) {
    $titulo = $data['titulo'];
    $descripcion = isset($data['descripcion']) ? $data['descripcion'] : '';
    $fecha_inicio = isset($data['fecha_inicio']) ? $data['fecha_inicio'] : NULL;
    $fecha_fin = isset($data['fecha_fin']) ? $data['fecha_fin'] : NULL;
    $estado_id = $data['estado_id'];
    $asignado_a = isset($data['asignado_a']) ? $data['asignado_a'] : []; // Cambiado a un arreglo
    $sprint_id = $data['sprint_id'];

    // Inserta la tarea en la tabla 'tareas'
    $stmt = $conexion->prepare("INSERT INTO tareas (titulo, descripcion, fecha_inicio, fecha_fin, estado_id, sprint_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssii', $titulo, $descripcion, $fecha_inicio, $fecha_fin, $estado_id, $sprint_id);

    if ($stmt->execute()) {
        $tarea_id = $stmt->insert_id; // Obtiene el ID de la tarea recién creada

        // Ahora inserta los asignados en la tabla 'tarea_asignados'
        if (!empty($asignado_a)) {
            $insert_asignados_stmt = $conexion->prepare("INSERT INTO tarea_asignados (tarea_id, alumno_id) VALUES (?, ?)");

            foreach ($asignado_a as $alumno_id) {
                $insert_asignados_stmt->bind_param('ii', $tarea_id, $alumno_id);
                $insert_asignados_stmt->execute();
            }

            $insert_asignados_stmt->close();
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al insertar tarea: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
}

$conexion->close();
?>
