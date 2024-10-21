<?php
session_start();
require_once '../includes/conexion.php';
require_once '../includes/dashboard.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$dni_alumno = $_SESSION['usuario_id'];

$sql_proyectos_alumno = "
SELECT 
    p.id,
    p.nombre AS nombre_proyecto,
    p.descripcion,
    p.fecha_inicio,
    p.fecha_fin,
    CASE 
        WHEN p.fecha_fin IS NULL OR p.fecha_fin >= CURDATE() THEN 'En Proceso'
        ELSE 'Finalizado'
    END AS estado_proyecto
FROM 
    proyectos p
INNER JOIN 
    proyectointegrantes pi ON p.id = pi.proyecto_id
WHERE 
    pi.alumno_id = '$dni_alumno'
ORDER BY 
    p.fecha_inicio DESC;
";

$result_proyectos_alumno = $conexion->query($sql_proyectos_alumno);
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
            margin-top: 20px;
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
          <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Paginas</a></li>
          <li class="breadcrumb-item text-sm text-white active" aria-current="page">Proyectos</li>
        </ol>
      </nav>
    </div>
  </nav>
    <div class="container mt-5">
        <h2 class="text-center">Mis Proyectos</h2>
        <?php
if ($result_proyectos_alumno->num_rows > 0) {
    while ($row_proyecto = $result_proyectos_alumno->fetch_assoc()) {
        echo '<div class="project-card">';
        echo '<h3>' . htmlspecialchars($row_proyecto['nombre_proyecto']) . '</h3>';
        echo '<p>Objetivo del proyecto: ' . htmlspecialchars($row_proyecto['descripcion']) . '</p>';
        echo '<p>Fecha de Inicio: ' . htmlspecialchars($row_proyecto['fecha_inicio']) . '</p>';
        echo '<p>Fecha de Fin: ' . htmlspecialchars($row_proyecto['fecha_fin']) . '</p>';
        echo '<p>Estado: ' . htmlspecialchars($row_proyecto['estado_proyecto']) . '</p>';
        
        echo '<a href="gant.php?id_proyecto=' . urlencode($row_proyecto['id']) . '" class="btn">Ver Más</a>';
        
        echo '</div>';
    }
} else {
    echo "<div class='text-center'>No tienes proyectos asignados.</div>";
}
?>

    </div>
</main>
</body>
</html>