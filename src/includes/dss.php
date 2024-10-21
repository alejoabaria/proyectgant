<?php
require_once '../includes/conexion.php';

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
  <link rel="apple-touch-icon" href="./assets/img/logoescuela.png">
  <title>EESTN1</title>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <link href="../includes/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../includes/assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="../css/barra.css">
  <link href="../includes/assets/css/argon-dashboard.css?v=2.0.4" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
<div class="azul"></div>

  <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="https://www.tecnica1lacosta.edu.ar/" target="_blank">
        <img src="../img/logoescuela.png" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-2 font-weight-bold">ProjectManager</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" href="inicio.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Panel</span>
          </a>
        </li>

        <?php if ($tipo_usuario === 'alumno'): ?>
          <li class="nav-item">
            <a class="nav-link" href="proyecto_alumno.php">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="ni ni-collection text-info text-sm opacity-10"></i>
              </div>
              <span class="nav-link-text ms-1">Mis proyectos</span>
            </a>
          </li>
        <?php elseif ($tipo_usuario === 'profesor'): ?>
          <li class="nav-item">
            <a class="nav-link" href="../views/materias_profesor.php">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                <i class="ni ni-credit-card text-success text-sm opacity-10"></i>
              </div>
              <span class="nav-link-text ms-1">Mis materias</span>
            </a>
          </li>
          <!-- <li class="nav-item">
            <a class="nav-link" href="../views/gant.php">
              <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-chart-bar text-success text-sm opacity-10"></i>
              </div>
              <span class="nav-link-text ms-1">Diagrama Gant</span>
            </a>
          </li> -->
        <?php endif; ?>

        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Mi perfil</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Perfil</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../php/logout.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-button-power text-danger text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Cerrar sesion</span>
          </a>
        </li>
      </ul>
    </div>
  </aside>

    
    <!-- End Navbar -->
  </main>

  <!-- JavaScript Files -->
  <script src="../includes/assets/js/core/popper.min.js"></script>
  <script src="../includes/assets/js/core/bootstrap.min.js"></script>
  <script src="../includes/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../includes/assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../includes/assets/js/plugins/chartjs.min.js"></script>
  <script src="../includes/assets/js/argon-dashboard.min.js?v=2.0.4"></script>

</body>

</html>
