<?php
header('Content-Type: application/json');
require_once('../includes/conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $proyectoId = isset($_GET['proyecto_id']) ? intval($_GET['proyecto_id']) : 0;

    if ($proyectoId > 0) {
        $query = "
            SELECT a.id AS id, a.nombre AS nombre
            FROM alumnos a
            JOIN proyectointegrantes pi ON a.id = pi.alumno_id
            WHERE pi.proyecto_id = ?
        ";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $proyectoId);
            $stmt->execute();
            $result = $stmt->get_result();
            $alumnos = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode($alumnos);
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
