<?php
header('Content-Type: application/json');
require_once('../includes/conexion.php'); // Asegúrate de que esta ruta sea correcta

$proyectoId = isset($_GET['proyecto_id']) ? (int)$_GET['proyecto_id'] : 0;

// Verifica si $proyectoId es válido
if ($proyectoId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID del proyecto no es válido.']);
    exit;
}

// Consulta para obtener project managers e integrantes del proyecto
$query = "
    SELECT a.dni AS id, a.nombre AS nombre, 'Integrante' AS tipo
    FROM proyectointegrantes pi
    JOIN alumnos a ON a.dni = pi.alumno_id
    WHERE pi.proyecto_id = ?
    
    UNION ALL
    
    SELECT a.dni AS id, a.nombre AS nombre, 'Project Manager' AS tipo
    FROM proyectos p
    JOIN alumnos a ON a.dni = p.project_manager_id
    WHERE p.id = ?;
";

if ($stmt = $conexion->prepare($query)) {
    // Enlaza los parámetros
    $stmt->bind_param("ii", $proyectoId, $proyectoId);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $alumnos = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Verifica si hay resultados
        if (!empty($alumnos)) {
            echo json_encode($alumnos);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontraron alumnos.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la ejecución de la consulta: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $conexion->error]);
}

$conexion->close();
?>
