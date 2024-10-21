<?php
require_once '../includes/conexion.php';

session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Procesar los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $project_manager_id = $_POST['project_manager_id'];
    $cursos = $_POST['curso_id'];

    // Preparar y ejecutar la consulta para insertar en 'proyectos'
    $sql = "INSERT INTO proyectos (nombre, descripcion, fecha_inicio, project_manager_id, creador_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $creador_id = $_SESSION['usuario_id']; // ID del usuario actual
    $stmt->bind_param("sssii", $nombre, $descripcion, $fecha_inicio, $project_manager_id, $creador_id);

    if ($stmt->execute()) {
        $proyecto_id = $stmt->insert_id; // Obtener el ID del proyecto insertado

        // Insertar las relaciones con los cursos
        $sql_cursos = "INSERT INTO proyectos_cursos (proyecto_id, curso_id) VALUES (?, ?)";
        $stmt_cursos = $conexion->prepare($sql_cursos);
        foreach ($cursos as $curso_id) {
            $stmt_cursos->bind_param("ii", $proyecto_id, $curso_id);
            $stmt_cursos->execute();
        }

        echo "Proyecto creado con éxito.";
        // Redireccionar a una página de éxito o al dashboard
        header("Location: ../views/crear_proyecto.php?message=success");
    } else {
        echo "Error al crear el proyecto: " . $stmt->error;
    }

    $stmt->close();
    $conexion->close();
} else {
    echo "Método de solicitud no permitido.";
}
?>
