<?php
include '../includes/conexion.php'; 

header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Error al decodificar JSON']);
        exit();
    }

    error_log("Datos recibidos: " . print_r($data, true));

    if (isset($data['id_sprint']) && isset($data['nombre']) && isset($data['fecha_inicio']) && isset($data['fecha_fin']) && isset($data['descripcion']) && isset($data['estado_id']) && isset($data['proyecto_id'])) {
        $idSprint = (int)$data['id_sprint'];
        $nombre = $data['nombre'];
        $fechaInicio = $data['fecha_inicio'];
        $fechaFin = $data['fecha_fin'];
        $estado_id = (int)$data['estado_id'];
        $proyecto_id = (int)$data['proyecto_id'];
        $descripcion = $data['descripcion'];

        error_log("Descripción antes de bind_param: '" . $descripcion . "'");

        if (!isset($conexion)) {
            echo json_encode(['status' => 'error', 'message' => 'Conexión a la base de datos no establecida']);
            exit();
        }

        // Usa 'sssssii' en lugar de 'sssiiii' para los parámetros
        $sql = "UPDATE sprints SET nombre = ?, fecha_inicio = ?, fecha_fin = ?, descripcion = ?, estado_id = ?, proyecto_id = ? WHERE id = ?";

        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param('ssssiii', $nombre, $fechaInicio, $fechaFin, $descripcion, $estado_id, $proyecto_id, $idSprint);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Sprint actualizado correctamente']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el sprint', 'error' => $stmt->error]);
                error_log("Error al actualizar el sprint: " . $stmt->error);
            }

            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en la preparación de la consulta', 'error' => $conexion->error]);
            error_log("Error en la preparación de la consulta: " . $conexion->error);
        }

        $conexion->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de solicitud no válido']);
}
?>
