<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtiene el ID del proyecto del cuerpo de la solicitud
    $data = json_decode(file_get_contents("php://input"), true);
    $id_proyecto = $data['id_proyecto'];

    // Verifica si el ID del proyecto es válido
    if (isset($id_proyecto) && is_numeric($id_proyecto)) {
        // Prepara la consulta para eliminar el proyecto
        $stmt = $conexion->prepare("DELETE FROM proyectos WHERE id = ?");
        $stmt->bind_param("i", $id_proyecto);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el proyecto.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'ID de proyecto no válido.']);
    }
}
?>
