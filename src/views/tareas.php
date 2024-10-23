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

if (!$id_proyecto) {
    die("Proyecto no especificado.");
}

// Obtener el estado deseado desde la solicitud (por defecto, "pendiente")
$estado = isset($_GET['estado']) ? $_GET['estado'] : 'pendiente';

// Mapeo de estados a sus respectivos IDs (ajusta según tu base de datos)
$estado_id = ($estado == 'pendiente') ? 1 : 2; // Asumiendo que 1 es "pendiente" y 2 es "finalizado"

// Mapeo de estado_id a nombres
$estado_nombres = [
    1 => "Pendiente",
    2 => "Finalizado"
];

// Consulta para obtener los sprints y sus tareas, incluyendo nombres de alumnos
$sql_sprints_tareas = "
    SELECT 
        s.id AS sprint_id, 
        s.nombre AS nombre_sprint,
        t.id AS tarea_id,
        t.titulo AS titulo_tarea,
        t.descripcion,
        t.fecha_inicio,
        t.fecha_fin,
        t.estado_id,
        GROUP_CONCAT(DISTINCT CONCAT(a.nombre, ' ', a.apellido) SEPARATOR ', ') AS alumnos_asignados
    FROM 
        sprints s
    LEFT JOIN 
        tareas t ON s.id = t.sprint_id
    LEFT JOIN 
        tarea_asignados ta ON t.id = ta.tarea_id
    LEFT JOIN 
        alumnos a ON ta.alumno_id = a.dni -- Aquí unimos con la tabla alumnos
    WHERE 
        s.proyecto_id = ? AND (t.estado_id = ? OR t.estado_id IS NULL)
    GROUP BY 
        s.id, t.id
    ORDER BY 
        s.id, t.fecha_inicio;";

$stmt = $conexion->prepare($sql_sprints_tareas);

// Manejo de errores si la preparación falla
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conexion->error);
}

$stmt->bind_param("ii", $id_proyecto, $estado_id);
$stmt->execute();
$result_sprints_tareas = $stmt->get_result();

// Manejo de errores en la consulta
if (!$result_sprints_tareas) {
    die("Error en la consulta: " . $stmt->error);
}

// Agrupar tareas por sprint
$sprints = [];
while ($row = $result_sprints_tareas->fetch_assoc()) {
    $sprint_id = $row['sprint_id'];
    if (!isset($sprints[$sprint_id])) {
        $sprints[$sprint_id] = [
            'nombre_sprint' => $row['nombre_sprint'],
            'tareas' => []
        ];
    }
    if ($row['tarea_id']) {
        $sprints[$sprint_id]['tareas'][] = [
            'tarea_id' => $row['tarea_id'], // Asegúrate de que esto esté incluido
            'titulo_tarea' => $row['titulo_tarea'],
            'descripcion' => $row['descripcion'],
            'fecha_inicio' => $row['fecha_inicio'],
            'fecha_fin' => $row['fecha_fin'],
            'estado_id' => $row['estado_id'],
            'estado_texto' => isset($estado_nombres[$row['estado_id']]) ? $estado_nombres[$row['estado_id']] : 'Desconocido',
            'alumnos_asignados' => $row['alumnos_asignados']
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Ver Tareas de los Sprints</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        h2 {
            text-align: center;
            color: #ffffff;
        }
        h3 {
            text-align: center;
            color: #5271ff;
            font-weight: bold;
        }
        .sprint-card {
            background-color: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .task-card {
            background-color: #f1f1f1;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }
        .task-card h5 {
            color: #007bff;
        }
        .task-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        .accordion .card {
            border: none;
            margin-bottom: 15px;
        }
        .accordion .card-header {
            background-color: #F0F0F0;
            color: #ffffff;
            border-radius: 5px;
            cursor: pointer;
            padding: 15px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .accordion .card-header:hover {
            color: #fff;
            background-color: #405ab5;
        }
        .accordion .card-header h3:hover {
            color: #fff;
        }
        
        .accordion .card-body {
            background-color: #ffffff;
            border-radius: 0 0 5px 5px;
        }
        .alert {
        border-radius: 10px; /* Bordes redondeados */
        font-size: 18px; /* Tamaño de fuente más grande */
        padding: 15px; /* Relleno adicional */
    }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
<main class="main-content position-relative border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur">
        <div class="container-fluid py-1 px-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                    <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Páginas</a></li>
                    <li class="breadcrumb-item text-sm text-white active" aria-current="page">Sprints</li>
                </ol>
            </nav>
        </div>
    </nav>

    <div class="text-center mb-4">
        <h2 class="text-center">Tareas de los Sprints</h2>
        <a href="?id_proyecto=<?php echo $id_proyecto; ?>&estado=pendiente" class="btn btn-primary">Pendientes</a>
        <a href="?id_proyecto=<?php echo $id_proyecto; ?>&estado=finalizado" class="btn btn-success">Finalizadas</a>
    </div>

    <?php
    if (!empty($sprints)) {
        echo '<div class="accordion" id="accordionSprints">';
        foreach ($sprints as $sprint_id => $sprint) {
            echo '<div class="card">';
            echo '<div class="card-header" id="heading' . $sprint_id . '" data-toggle="collapse" data-target="#collapse' . $sprint_id . '" aria-expanded="true" aria-controls="collapse' . $sprint_id . '">';
            echo '<h3 class="mb-0">' . htmlspecialchars($sprint['nombre_sprint']) . '</h3>';
            echo '</div>';

            echo '<div id="collapse' . $sprint_id . '" class="collapse" aria-labelledby="heading' . $sprint_id . '" data-parent="#accordionSprints">';
            echo '<div class="card-body">';
            
            if (!empty($sprint['tareas'])) {
                echo '<div class="task-grid">';
                foreach ($sprint['tareas'] as $tarea) {
                    echo '<div class="task-card">';
                    echo '<h5>' . htmlspecialchars($tarea['titulo_tarea']) . '</h5>';
                    echo '<p><strong>Descripción:</strong> ' . htmlspecialchars($tarea['descripcion']) . '</p>';
                    echo '<p><strong>Fecha Inicio:</strong> ' . htmlspecialchars($tarea['fecha_inicio']) . '</p>';
                    echo '<p><strong>Fecha Fin:</strong> ' . htmlspecialchars($tarea['fecha_fin']) . '</p>';
                    echo '<p><strong>Estado:</strong> ' . htmlspecialchars($tarea['estado_texto']) . '</p>'; // Mostrar estado como texto
                    echo '<p><strong>Alumnos Asignados:</strong> ' . htmlspecialchars($tarea['alumnos_asignados']) . '</p>';
                    if (isset($tarea['tarea_id'])) {
                        echo '<button class="btn btn-warning"  data-toggle="modal" data-target="#editarModal" onclick="editarTarea(' . $tarea['tarea_id'] . ')" style="margin-right: 10px;">Editar</button>';

                        echo '<button class="btn btn-danger" onclick="eliminarTarea(' . $tarea['tarea_id'] . ')">Eliminar</button>';
                        
                    } else {
                        echo '<p class="text-danger">ID de tarea no disponible.</p>'; // Mensaje alternativo
                    }
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<div class="alert alert-warning text-center" role="alert">';
                echo '<strong>No hay tareas en este sprint.</strong>';
                echo '</div>';            }

            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p class="text-center">No hay sprints con tareas en este proyecto.</p>';
    }
    ?>
</main>
<div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarModalLabel">Editar Tarea</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" id="tarea_id" name="tarea_id">
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin">Fecha de Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select class="form-control" id="estado" name="estado">
                            <option value="1">Pendiente</option>
                            <option value="2">Finalizado</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="guardarEdicion()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

</body>
<script>
    
    function eliminarTarea(tareaId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta tarea será eliminada permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../gant/eliminar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ tarea_id: tareaId })
            })
            .then(response => response.text())
            .then(data => {
                let jsonData;
                try {
                    jsonData = JSON.parse(data);
                    if (jsonData.success) {
                        Swal.fire('Eliminado!', 'La tarea ha sido eliminada.', 'success');
                        location.reload();
                    } else {
                        Swal.fire('Error!', jsonData.message, 'error');
                    }
                } catch (e) {
                    console.error('Error de JSON:', e);
                    Swal.fire('Error!', 'Hubo un problema con la respuesta del servidor.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Ocurrió un error al intentar eliminar la tarea.', 'error');
            });
        }
    });
}

//NO ANDA
//  {function editarTarea(tareaId) {
//     fetch('../gant/get_.php', {
//         method: 'POST',
//         headers: {
//             'Content-Type': 'application/json'
//         },
//         body: JSON.stringify({ tarea_id: tareaId })
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.success) {
//             document.getElementById('tarea_id').value = data.tarea.id;
//             document.getElementById('titulo').value = data.tarea.titulo;
//             document.getElementById('descripcion').value = data.tarea.descripcion;
//             document.getElementById('fecha_inicio').value = data.tarea.fecha_inicio;
//             document.getElementById('fecha_fin').value = data.tarea.fecha_fin;
//             document.getElementById('estado').value = data.tarea.estado_id;

//             $('#editarModal').modal('show');
//         } else {
//             Swal.fire('Error!', 'Error al obtener los datos de la tarea: ' + data.message, 'error');
//         }
//     })
//     .catch(error => {
//         console.error('Error:', error);
//         Swal.fire('Error!', 'Ocurrió un error al intentar obtener los datos de la tarea.', 'error');
//     });
// }}



</script>

</script>
</html>
