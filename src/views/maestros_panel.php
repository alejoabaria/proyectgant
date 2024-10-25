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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        .btn {
        margin-top: 20px;
        border-radius: 25px;
        padding: 10px 20px;
        text-transform: uppercase;
        font-weight: bold;
        transition: background-color 0.3s, transform 0.3s;
    }
    .btn-ver-tareas {
        background-color: #5271ff;
        color: white;
    }
    .btn-ver-tareas:hover {
        color: white;

        background-color: #415ab4;
        transform: scale(1.05);
    }
    .btn-ver-mas {
        background-color: #6c757d;
        color: white;
    }
    .btn-ver-mas:hover {
        color: white;

        background-color: #5a6268;
        transform: scale(1.05);
    }
    .btn-editar {
        background-color: #28a745; /* Verde */
        color: white;
    }
    .btn-editar:hover {
        background-color: #218838;
        color: white;

        transform: scale(1.05);
    }
    .btn-eliminar {
        background-color: #dc3545; /* Rojo */
        color: white;
    }
    .btn-eliminar:hover {
        background-color: #c82333;
        color: white;

        transform: scale(1.05);
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
                echo '<a href="tareas.php?id_proyecto=' . urlencode($row['proyecto_id']) . '" class="btn btn-ver-tareas">Ver Tareas</a>';
                echo '<a href="gant.php?id_proyecto=' . urlencode($row['proyecto_id']) . '" class="btn btn-ver-mas">Diagrama Gantt</a>';
                
                // Botón de Editar
                echo '<button class="btn btn-editar" onclick="abrirModalEditar(' . $row['proyecto_id'] . ', \'' . htmlspecialchars($row['nombre_proyecto']) . '\', \'' . htmlspecialchars($row['descripcion']) . '\', \'' . htmlspecialchars($row['fecha_inicio']) . '\', \'' . htmlspecialchars($row['fecha_fin']) . '\')">Editar</button>';
                
                // Botón de Eliminar
                echo '<button class="btn btn-eliminar" onclick="eliminarProyecto(' . $row['proyecto_id'] . ')">Eliminar</button>';
                echo '</div>';
            }
        } else {
            echo '<p class="text-center">No tienes proyectos asignados.</p>';
        }
        ?>
    </div>
</main>
<!-- Modal para Editar Proyecto -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarLabel">Editar Proyecto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditarProyecto">
                    <input type="hidden" id="editarProyectoId" name="id">
                    <div class="form-group">
                        <label for="editarNombre">Nombre del Proyecto</label>
                        <input type="text" class="form-control" id="editarNombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="editarDescripcion">Descripción</label>
                        <textarea class="form-control" id="editarDescripcion" name="descripcion" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editarFechaInicio">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="editarFechaInicio" name="fecha_inicio" required>
                    </div>
                    <div class="form-group">
                        <label for="editarFechaFin">Fecha de Fin</label>
                        <input type="date" class="form-control" id="editarFechaFin" name="fecha_fin">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function eliminarProyecto(proyectoId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminarlo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../php/eliminar_proyecto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id_proyecto: proyectoId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Eliminado!',
                        'El proyecto ha sido eliminado.',
                        'success'
                        ).then(() => {
                        setTimeout(() => {
                            location.reload(); // Recargar la página para reflejar los cambios
                        }, 1000);
                    });
                } else {
                    Swal.fire(
                        'Error!',
                        data.message,
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire(
                    'Error!',
                    'Ocurrió un error al intentar eliminar el proyecto.',
                    'error'
                );
            });
        }
    });
}
function abrirModalEditar(id, nombre, descripcion, fechaInicio, fechaFin) {
    // Establecer valores en el modal
    document.getElementById('editarProyectoId').value = id;
    document.getElementById('editarNombre').value = nombre;
    document.getElementById('editarDescripcion').value = descripcion;
    document.getElementById('editarFechaInicio').value = fechaInicio;
    document.getElementById('editarFechaFin').value = fechaFin;

    // Mostrar el modal
    $('#modalEditar').modal('show');
}

// Enviar el formulario de edición
document.getElementById('formEditarProyecto').addEventListener('submit', function(event) {
    event.preventDefault(); // Evitar el envío normal del formulario

    const formData = new FormData(this);
    
    fetch('../php/editar_proyecto.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire(
                'Éxito',
                'El proyecto ha sido actualizado.',
                'success'
            ).then(() => {
                location.reload(); // Recargar la página para reflejar los cambios
            });
        } else {
            Swal.fire(
                'Error',
                data.message,
                'error'
            );
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire(
            'Error',
            'Ocurrió un error al intentar actualizar el proyecto.',
            'error'
        );
    });
});
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>


