<?php
header('Content-Type: application/json');
require_once('../includes/conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $proyectoId = isset($_GET['proyecto_id']) ? intval($_GET['proyecto_id']) : 0;

    if ($proyectoId > 0) {
        $query = "
            SELECT pm.id AS id, pm.nombre AS nombre
            FROM personal pm
            JOIN proyectos p ON pm.id = p.project_manager_id
            WHERE p.id = ?
        ";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $proyectoId);
            $stmt->execute();
            $result = $stmt->get_result();
            $projectManagers = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode($projectManagers);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error en la consulta.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID de proyecto inválido.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>
