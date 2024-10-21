<?php
session_start();
require_once '../includes/conexion.php';
require_once '../includes/dashboard.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

$dni_profesor = $_SESSION['usuario_id'];

$sql = "
SELECT 
    r.dni_personal AS dni_profesor,
    m.nombre AS nombre_materia,
    c.turno,
    c.hsmodcar,
    r.fd AS fecha_desde
FROM 
    revista r
INNER JOIN 
    cupof c ON r.cupof = c.cupof
INNER JOIN 
    materias m ON c.id_materias = m.id
WHERE 
    r.dni_personal = '$dni_profesor' AND
    r.fd = (
        SELECT MAX(r2.fd)
        FROM revista r2
        WHERE r2.cupof = r.cupof
    )
AND 
    (r.fh = '0000-00-00' OR r.fh >= CURDATE())
ORDER BY 
    r.fd DESC;
";

$result = $conexion->query($sql);

if ($result === false) {
    die("Error en la consulta: " . $conexion->error);
}
$conexion->close();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Materias Asignadas</title>
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
    <style>
        h2 {
            color: #5271ff;
            padding-top: 10px;
            font-weight: bold;

        }
        .container {
            margin-top: 10px;
            border-radius: 30px;
            background-color: white;
        }
    </style>
<main class="main-content position-relative border-radius-lg">
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur">
    <div class="container-fluid py-1 px-3">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
          <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Paginas</a></li>
          <li class="breadcrumb-item text-sm text-white active" aria-current="page">Mis materias</li>
        </ol>
      </nav>
    </div>
  </nav>
<div class="container mt-5">
    <h2 class="text-center">Materias Asignadas</h2>
    <?php if ($result->num_rows > 0): ?>
        <table id="materiasTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Nombre de la Materia</th>
                    <th>Turno</th>
                    <th>Horas Modulares</th>
                    <th>Fecha Desde</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["nombre_materia"]); ?></td>
                    <td><?php echo htmlspecialchars($row["turno"]); ?></td>
                    <td><?php echo htmlspecialchars($row["hsmodcar"]); ?></td>
                    <td><?php echo htmlspecialchars($row["fecha_desde"]); ?></td>
                    <td><a href="proyecto_curso.php?materia=<?php echo urlencode($row["nombre_materia"]); ?>" class="btn btn-primary btn-sm">Ver proyectos</a> <a href="crear_proyecto.php?materia=<?php echo urlencode($row["nombre_materia"]); ?>" class="btn btn-success btn-sm">Crear proyecto</a></td>
                
                    </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div id="noMateriasAlert" style="display: none;">No se encontraron materias asignadas.</div>
    <?php endif; ?>
</div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#materiasTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json"
        }
    });

    // Mostrar alerta SweetAlert2 si no hay materias
    <?php if ($result->num_rows === 0): ?>
        Swal.fire({
            icon: 'warning',
            title: 'Sin Materias Asignadas',
            text: 'No se encontraron materias asignadas.',
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Volver a la pestaña anterior en el historial
                window.history.back();
            }
        });
    <?php endif; ?>
});
</script>
</body>
</html>
