<?php
session_start();
require_once '../includes/conexion.php';
require_once '../includes/dashboard.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Obtener el id_proyecto de la URL si está presente
$id_proyecto = isset($_GET['id_proyecto']) ? intval($_GET['id_proyecto']) : null;

// Ajustar la consulta para filtrar por id_proyecto si está definido
$sql_proyectos = " 
    SELECT DISTINCT
        p.id AS proyecto_id, 
        p.nombre AS nombre_proyecto,
        p.descripcion,
        p.fecha_inicio,
        p.fecha_fin,
        ep.nombre AS estado_proyecto
    FROM 
        proyectos p
    INNER JOIN 
        cupof c ON p.cupof = c.cupof
    INNER JOIN 
        materias m ON c.id_materias = m.id
    INNER JOIN 
        revista r ON c.cupof = r.cupof
    INNER JOIN 
        estados_proyectos ep ON p.etapa_general = ep.id
    WHERE 
        (p.fecha_fin IS NULL OR p.fecha_fin >= CURDATE())";

if ($id_proyecto) {
    $sql_proyectos .= " AND p.id = " . $id_proyecto; // Filtrar por id_proyecto
}

$sql_proyectos .= " ORDER BY p.fecha_inicio DESC;";


$result_proyectos = $conexion->query($sql_proyectos);

// Manejo de errores en la consulta
if (!$result_proyectos) {
    die("Error en la consulta: " . $conexion->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mis Proyectos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        h2 {
            color: #5271ff;
            padding-top: 10px;
            font-weight: bold;
        }
        .project-card {
            background-color: #edebeb;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .project-card h3 {
            color: #5271ff;
            font-weight: bold;
        }
        .project-card p {
            margin: 5px 0;
            font-size: 17px;
        }
        .project-card .btn {
            background-color: #5271ff;
            color: white;
            margin-top: 10px;
            margin-right: 5px;
            border-radius: 25px;
            padding: 10px 20px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .container {
            border-radius: 30px;
            background-color: white;
        }
    </style>
</head>
<body>
<main class="main-content position-relative border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur">
        <div class="container-fluid py-1 px-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                    <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Páginas</a></li>
                    <li class="breadcrumb-item text-sm text-white active" aria-current="page">Proyectos</li>
                </ol>
            </nav>
        </div>
    </nav>
    <div class="container mt-5">
        <h2 class="text-center"></h2>
        <?php
        if ($result_proyectos->num_rows > 0) {
            while ($row = $result_proyectos->fetch_assoc()) {
                echo '<div class="project-card">';
                echo '<h3>' . htmlspecialchars($row['nombre_proyecto']) . '</h3>';
                echo '<p>' . htmlspecialchars($row['descripcion']) . '</p>'; // Muestra la descripción del proyecto
                echo '<p><strong>Fecha Inicio:</strong> ' . htmlspecialchars($row['fecha_inicio']) . '</p>'; // Muestra la fecha de inicio
                echo '<p><strong>Estado:</strong> ' . htmlspecialchars($row['estado_proyecto']) . '</p>'; // Muestra el estado del proyecto
                echo '<a href="tareas.php?id_proyecto=' . urlencode($row['proyecto_id']) . '" class="btn">Ver Tareas</a>';
                echo '<a href="gant.php?id_proyecto=' . urlencode($row['proyecto_id']) . '" class="btn">Gantt</a>';
                echo '</div>';
            }
        } else {
            echo '<p class="text-center">No tienes proyectos asignados.</p>';
        }
        ?>
    </div>
</main>
</body>
</html>
