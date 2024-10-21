<?php
session_start();
require_once '../includes/conexion.php';
require_once '../includes/dashboard.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://kit.fontawesome.com/eb6bbbbf4b.js" crossorigin="anonymous"></script>
  <title>Panel de Control</title>
  <link rel="stylesheet" href="../css/barra.css">
  <style>
    .card-button {
        cursor: pointer;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        height: 350px; /* Altura del botón */
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
    }
    .card-button:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    .card-body {
        padding: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .icon-container {
        width: 80px; /* Tamaño del círculo */
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px; /* Tamaño del icono */
        border-radius: 50%; /* Asegura que el círculo sea redondo */
        color: white;
        margin-bottom: 20px; /* Espacio entre el icono y el texto */
    }
    .bg-danger {
        background: linear-gradient(135deg, #ff6f6f, #ff3d3d);
    }
    .bg-success {
        background: linear-gradient(135deg, #4caf50, #2c6b2f);
    }
    .card-title {
        font-size: 1.5rem; /* Tamaño del título */
        font-weight: bold;
        margin-bottom: 10px; /* Espacio debajo del título */
    }
    .card-text {
        font-size: 1.2rem; /* Tamaño del texto */
        color: #666;
    }

    .main_content{
      z-index: 0;

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
          <li class="breadcrumb-item text-sm text-white active" aria-current="page">Panel</li>
        </ol>
      </nav>
    </div>
  </nav>
    <div class="container-fluid py-4">
      <div class="row d-flex justify-content-center">
        <?php if ($tipo_usuario === 'alumno'): ?>
          <div class="col-xl-4 col-sm-6 mb-4">
            <div class="card card-button" onclick="window.location.href='proyecto_alumno.php';">
              <div class="card-body">
                <div class="icon-container bg-danger">
                  <i class="fa fa-list"></i>
                </div>
                <p class="card-title">Mis proyectos</p>
                <h5 class="card-text">Actualmente estás participando en 10 proyectos</h5>
              </div>
            </div>
          </div>
        <?php elseif ($tipo_usuario === 'profesor'): ?>
          <div class="col-xl-4 col-sm-6 mb-4">
            <div class="card card-button" onclick="window.location.href='materias_profesor.php';">
              <div class="card-body">
                <div class="icon-container bg-success">
                  <i class="fa fa-book"></i> <!-- Icono de libro -->
                </div>
                <p class="card-title">Mis materias</p>
                <h5 class="card-text">Aquí puedes ver tus materias asignadas y crear proyectos en esta misma</h5>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>
