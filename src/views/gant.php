<?php
session_start();
require_once("../includes/dashboard.php");
require_once("../includes/conexion.php");


if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$id_proyecto = isset($_GET['id_proyecto']) ? $_GET['id_proyecto'] : null;

if ($id_proyecto === null) {
    echo "No se ha proporcionado un ID de proyecto.";
    exit();
}


$sql_proyecto = "SELECT * FROM proyectos WHERE id = ?";
$stmt = $conexion->prepare($sql_proyecto);
if ($stmt === false) {
    echo "Error al preparar la declaración SQL.";
    exit();
}

$stmt->bind_param("i", $id_proyecto);
$stmt->execute();
$result_proyecto = $stmt->get_result();

if ($result_proyecto->num_rows > 0) {
    $proyecto = $result_proyecto->fetch_assoc();
} else {
    echo "No se encontró el proyecto.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagrama de Gantt</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
     <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <style>

        #ganttChartView {
            width: 100%;
            height: 600px; 
            margin-top: 20px;
        }
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
        .gantt_task_line.task_task {
            background-color: #ff9f00; 
        }

        .gantt_task_line.task_sprint {
            background-color: #00bfae; 
        }

/* Estilos generales del diagrama de Gantt */
.gantt_task_line {
    border-radius: 5px; /* Bordes redondeados para las barras de tarea */
}

/* Estilo para la línea de tarea cuando está en progreso */
.gantt_task_line.task_in_progress {
    background-color: #ffa500; /* Color naranja para tareas en progreso */
}

/* Estilo para la línea de tarea cuando está completada */
.gantt_task_line.task_completed {
    background-color: #32cd32; /* Color verde para tareas completadas */
}

/* Estilo para la línea de tarea cuando está retrasada */
.gantt_task_line.task_delayed {
    background-color: #ff4500; /* Color rojo para tareas retrasadas */
}

/* Estilo para las etiquetas de tarea */
.gantt_task_text {
    color: #ffffff; /* Color de texto blanco */
    font-weight: bold; /* Texto en negrita */
    font-size: 14px; /* Tamaño de fuente más grande */
}

/* Estilo para la línea de conexión entre tareas */
.gantt_link_line {
    stroke-width: 2px; /* Grosor de la línea de conexión */
    stroke: #0000ff; /* Color azul para la línea de conexión */
}

/* Estilo para la barra de la escala de tiempo */
.gantt_scale_line {
    background-color: #f0f0f0; /* Color de fondo gris claro para la escala de tiempo */
}

/* Estilo para el encabezado de la tabla de tareas */
.gantt_table_header {
    background-color: #e0e0e0; /* Color de fondo gris para el encabezado */
    color: #333333; /* Color de texto oscuro para el encabezado */
    font-weight: bold; /* Texto en negrita para el encabezado */
}

/* Estilo para las celdas de la tabla de tareas */
.gantt_table_cell {
    border: 1px solid #cccccc; /* Borde gris claro para las celdas */
    padding: 5px; /* Espaciado interno de las celdas */
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
                    <li class="breadcrumb-item text-sm text-white active" aria-current="page">Diagrama</li>
                </ol>
            </nav>
        </div>
    </nav>
    <div class="container mt-5">
        <center><h2>Diagrama de Gantt</h2></center>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addSprintModal">Añadir Sprint</button>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addTareaModal">Añadir Tarea</button>
        <div id="ganttChartView"></div>
    </div>
</main>

<div class="modal fade" id="addSprintModal" tabindex="-1" role="dialog" aria-labelledby="addSprintModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSprintModalLabel">Añadir Sprint</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="sprintForm">
                    <div class="form-group">
                        <label for="sprintName">Nombre del Sprint</label>
                        <input type="text" class="form-control" id="sprintName" required>
                    </div>
                    <div class="form-group">
                        <label for="sprintStartDate">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="sprintStartDate" required>
                    </div>
                    <div class="form-group">
                        <label for="sprintEndDate">Fecha de Fin</label>
                        <input type="date" class="form-control" id="sprintEndDate" required>
                    </div>
                    <div class="form-group">
                        <label for="sprintDescription">Descripción</label>
                        <textarea class="form-control" id="sprintDescription"></textarea>
                    </div>
                    
                    <input type="hidden" id="sprintEstadoId" value="1"> 
                    <input type="hidden" id="sprintProyectoId" name="proyecto_id" value="<?php echo $id_proyecto; ?>">
                    <button type="submit" class="btn btn-primary">Guardar Sprint</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addTareaModal" tabindex="-1" role="dialog" aria-labelledby="addTareaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTareaModalLabel">Añadir Tarea</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="tareaForm">
                        <div class="form-group">
                        <label for="tareaSprintId">Sprint</label>
                        <select class="form-control" id="tareaSprintId" required></select>
                    </div>
                    <div class="form-group">
                        <label for="tareaTitulo">Título de la Tarea</label>
                        <input type="text" class="form-control" id="tareaTitulo" required>
                    </div>
                    <div class="form-group">
                        <label for="tareaDescripcion">Descripción</label>
                        <textarea class="form-control" id="tareaDescripcion"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="tareaFechaInicio">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="tareaFechaInicio">
                    </div>
                    <div class="form-group">
                        <label for="tareaFechaFin">Fecha de Fin</label>
                        <input type="date" class="form-control" id="tareaFechaFin">
                    </div>
                    <div class="form-group">
                        <label for="tareaEstadoId">Estado</label>
                        <select class="form-control" id="tareaEstadoId" required>
                            <option value="1">En proceso</option>
                            <option value="2">Finalizada</option>
                        </select>
                    </div>
                    <div class="form-group">
                     <label for="asignadoA">Asignado A</label>
                  <select id="asignadoA" multiple="multiple" style="width: 100%;"></select>
                 </select>
                </div>
                    <button type="submit" class="btn btn-primary">Guardar Tarea</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Sprint -->
<div class="modal fade" id="editSprintModal" tabindex="-1" role="dialog" aria-labelledby="editSprintModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSprintModalLabel">Editar Sprint</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editSprintForm">
                    <div class="form-group">
                        <label for="editSprintName">Nombre del Sprint</label>
                        <input type="text" class="form-control" id="editSprintName" required>
                    </div>
                    <div class="form-group">
                        <label for="editSprintStartDate">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="editSprintStartDate" required>
                    </div>
                    <div class="form-group">
                        <label for="editSprintEndDate">Fecha de Fin</label>
                        <input type="date" class="form-control" id="editSprintEndDate" required>
                    </div>
                    <div class="form-group">
                        <label for="editSprintDescription">Descripcion</label>
                        <textarea class="form-control" id="editSprintDescription"></textarea>
                    </div>
                    <input type="hidden" id="editSprintId">
                    <input type="hidden" id="sprintEstadoId" value="1"> 
                    <input type="hidden" id="sprintProyectoId" value="1"> 
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Modal para editar Tarea -->
    <div class="modal fade" id="editTareaModal" tabindex="-1" role="dialog" aria-labelledby="editTareaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTareaModalLabel">Editar Tarea</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editTareaForm">
                        <div class="form-group">
                            <label for="editTareaTitulo">Título de la Tarea</label>
                            <input type="text" class="form-control" id="editTareaTitulo" required>
                        </div>
                        <div class="form-group">
                            <label for="editTareaDescripcion">Descripción</label>
                            <textarea class="form-control" id="editTareaDescripcion"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="tareaEstadoId">Estado</label>
                            <select class="form-control" id="tareaEstadoId" required>
                                <option value="1">En proceso</option>
                                <option value="2">Finalizado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editTareaFechaInicio">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="editTareaFechaInicio">
                        </div>
                        <div class="form-group">
                            <label for="editTareaFechaFin">Fecha de Fin</label>
                            <input type="date" class="form-control" id="editTareaFechaFin">
                        </div>
                        <input type="hidden" id="editTareaSprintId">
                        <input type="hidden" id="editTareaId">
                        <div class="form-group">
                            <label for="editTareaAsignadoA">Asignado A</label>
                            <input type="number" class="form-control" id="editTareaAsignadoA"> 
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <button type="button" class="btn btn-danger" onclick="eliminarTarea()">Eliminar tarea</button>
                        </form>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<!-- Include Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script><script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
gantt.config.date_format = "%Y-%m-%d";
gantt.config.scale_unit = "month";
gantt.config.date_scale = "%F, %Y";
gantt.config.subscales = [{ unit: "day", step: 1, date: "%d" }];
gantt.config.duration_unit = "day";
gantt.config.readonly = true; 

gantt.locale = {
    date: {
        month_full: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
        month_short: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
        day_full: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"],
        day_short: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"]
    },
    labels: {
        new_task: "Nueva tarea",
        new_sprint: "Nuevo sprint",
        icon_save: "Guardar",
        icon_cancel: "Cancelar",
        column_text: "Elemento",
        column_start_date: "Inicio",
        column_duration: "Duracion",
    }
};

gantt.templates.task_class = function (start, end, task) {
    if (task.type === "sprint") return "task_sprint";
    if (task.status === "in_progress") return "task_in_progress";
    if (task.status === "completed") return "task_completed";
    if (task.status === "delayed") return "task_delayed";
    return "task_task";
};

gantt.templates.link_class = function (link) {
    return "gantt_link_line";
};

gantt.templates.task_text = function (start, end, task) {
    return "<span class='gantt_task_text'>" + task.text + "</span>";
};

gantt.templates.scale_class = function (date) {
    return "gantt_scale_line";
};

gantt.templates.table_header_class = function () {
    return "gantt_table_header";
};

gantt.templates.table_cell_class = function () {
    return "gantt_table_cell";
};

// Asignar clase CSS según el tipo de tarea o sprint
gantt.templates.task_class = function (start, end, task) {
    return task.type === "sprint" ? "task_sprint" : "task_task";
};

// Asignar clase CSS según el tipo de sprint
gantt.templates.timeline_class = function (task) {
    return task.type === "sprint" ? "task_sprint" : "task_task";
};

gantt.config.lightbox = {};

gantt.attachEvent("onEmptyClick", function (event) {
    return false;
});

gantt.config.drag_move = false;
gantt.config.drag_resize = false;

gantt.config.columns = [
    { name: "text", label: "Elemento", tree: true, width: 200 },
    { name: "start_date", label: "Inicio", align: "center", width: 100 },
    { name: "duration", label: "Duracion", align: "center", width: 80 }
];

function loadSprints() {
    const id_proyecto = <?php echo json_encode($id_proyecto); ?>; // Obtén el ID del proyecto desde PHP

    fetch(`../gant/get_sprint.php?proyecto_id=${id_proyecto}`)  
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data.data)) {
                const sprintSelect = document.getElementById('tareaSprintId');
                sprintSelect.innerHTML = ''; 

                data.data.forEach(sprint => {
                    const option = document.createElement('option');
                    option.value = sprint.id;
                    option.text = sprint.text;  
                    option.setAttribute('data-fecha-inicio', sprint.start_date);
                    option.setAttribute('data-fecha-fin', sprint.end_date);

                    sprintSelect.appendChild(option);
                });

                if (sprintSelect.options.length > 0) {
                    setFechaLimits(sprintSelect.options[0]);
                }
            } else {
                console.error('No se encontraron sprints válidos');
            }
        })
        .catch(error => {
            console.error('Error al cargar los sprints:', error);
        });
}

function loadData() {
    const id_proyecto = <?php echo json_encode($id_proyecto); ?>;

    Promise.all([
        fetch(`../gant/get_sprint.php?proyecto_id=${id_proyecto}`).then(response => response.json()),
        fetch(`../gant/get_task.php?proyecto_id=${id_proyecto}`).then(response => response.json())
    ]).then(([sprintsData, tasksData]) => {
        if (Array.isArray(sprintsData.data) && Array.isArray(tasksData.data)) {
            let sprintFechasMap = {};
            sprintsData.data.forEach(sprint => {
                sprintFechasMap[sprint.id] = {
                    fechaInicio: sprint.start_date,
                    fechaFin: sprint.end_date
                };
            });

            tasksData.data.forEach(task => {
                if (task.sprint_id && sprintFechasMap[task.sprint_id]) {
                    let sprintFechas = sprintFechasMap[task.sprint_id];

                    if (new Date(task.start_date) < new Date(sprintFechas.fechaInicio)) {
                        task.start_date = sprintFechas.fechaInicio;
                    }

                    if (new Date(task.end_date) > new Date(sprintFechas.fechaFin)) {
                        task.end_date = sprintFechas.fechaFin;
                    }
                }
            });

            let data = { data: [...sprintsData.data, ...tasksData.data] };
            gantt.parse(data);
        } else {
            console.error('Datos de sprints o tareas no válidos');
        }
    }).catch(error => {
        console.error('Error al cargar los datos:', error);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    gantt.init('ganttChartView');
    loadData();

    $('#addTareaModal').on('show.bs.modal', function () {
        loadSprints();
    });
});

gantt.attachEvent("onTaskDblClick", function (id, e) {
    const task = gantt.getTask(id);

    const formatFecha = (date) => {
        if (!date) return '';
        let d = new Date(date);
        let month = '' + (d.getMonth() + 1);
        let day = '' + d.getDate();
        let year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    };

    if (task.$level === 0) { 
        document.getElementById('editSprintName').value = task.text || '';
        document.getElementById('editSprintStartDate').value = formatFecha(task.start_date) || '';
        document.getElementById('editSprintEndDate').value = formatFecha(task.end_date) || '';
        
        document.getElementById('editSprintDescription').value = task.descripcion || 'Sin descripción';
        document.getElementById('editSprintId').value = task.id || '';

        $('#editSprintModal').modal('show');
    } else { 
        document.getElementById('editTareaTitulo').value = task.text || '';
        
        document.getElementById('editTareaDescripcion').value = task.descripcion || 'Sin descripción';
        document.getElementById('editTareaFechaInicio').value = formatFecha(task.start_date) || '';
        document.getElementById('editTareaFechaFin').value = formatFecha(task.end_date) || '';
        document.getElementById('editTareaId').value = task.id || '';

        $('#editTareaModal').modal('show');
    }

    return false; 
});

document.getElementById('sprintForm').addEventListener('submit', function (event) {
    event.preventDefault();

    let sprintData = {
        text: document.getElementById('sprintName').value,
        start_date: document.getElementById('sprintStartDate').value,
        end_date: document.getElementById('sprintEndDate').value,
        description: document.getElementById('sprintDescription').value,
        estado_id: document.getElementById('sprintEstadoId').value,
        proyecto_id: document.getElementById('sprintProyectoId').value
    };

    fetch('../gant/add_sprint.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(sprintData)
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              $('#addSprintModal').modal('hide'); // Asegúrate de que el ID sea correcto
              loadData(); // Cargar los datos actualizados

              // Mostrar SweetAlert de éxito
              Swal.fire({
                  title: 'Éxito!',
                  text: 'El sprint se ha añadido correctamente.',
                  icon: 'success',
                  confirmButtonText: 'Aceptar'
              });
          } else {
              // Mostrar SweetAlert de error
              Swal.fire({
                  title: 'Error!',
                  text: data.message,
                  icon: 'error',
                  confirmButtonText: 'Aceptar'
              });
          }
      }).catch(error => {
          console.error('Error:', error);
          // Mostrar SweetAlert de error en caso de un fallo en la petición
          Swal.fire({
              title: 'Error!',
              text: 'Ocurrió un error al intentar agregar el sprint.',
              icon: 'error',
              confirmButtonText: 'Aceptar'
          });
      });
});


document.getElementById('tareaSprintId').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    setFechaLimits(selectedOption);
});

function setFechaLimits(option) {
    const fechaInicio = option.getAttribute('data-fecha-inicio');
    const fechaFin = option.getAttribute('data-fecha-fin');

    const tareaFechaInicio = document.getElementById('tareaFechaInicio');
    const tareaFechaFin = document.getElementById('tareaFechaFin');

    tareaFechaInicio.min = fechaInicio;
    tareaFechaInicio.max = fechaFin;
    tareaFechaFin.min = fechaInicio;
    tareaFechaFin.max = fechaFin;

    tareaFechaInicio.value = fechaInicio;
    tareaFechaFin.value = fechaFin;
}
$(document).ready(function() {
    const id_proyecto = <?php echo json_encode($id_proyecto); ?>;

    if (id_proyecto && id_proyecto > 0) {
        $.ajax({
            url: '../php/alumnos.php',
            type: 'GET',
            data: {
                proyecto_id: id_proyecto
            },
            success: function(response) {
                // Verifica si la respuesta es un arreglo
                if (Array.isArray(response)) {
                    // Limpia el select antes de agregar nuevas opciones
                    $('#asignadoA').empty();
                    
                    response.forEach(function(item) {
                        $('#asignadoA').append(new Option(item.nombre + " (" + item.tipo + ")", item.id));
                    });

                    // Inicializa Select2
                    $('#asignadoA').select2({
                        placeholder: 'Selecciona uno o más alumnos',
                        allowClear: true
                    });
                } else {
                    alert('La respuesta no es un arreglo: ' + JSON.stringify(response));
                }
            },
            error: function() {
                alert('Error al cargar los datos');
            }
        });
    } else {
        console.error('ID del proyecto no es válido.');
    }
});

// Código para el formulario
document.getElementById('tareaForm').addEventListener('submit', function(event) { 
    event.preventDefault();

    let tareaData = {
        titulo: document.getElementById('tareaTitulo').value,
        descripcion: document.getElementById('tareaDescripcion').value,
        fecha_inicio: document.getElementById('tareaFechaInicio').value,
        fecha_fin: document.getElementById('tareaFechaFin').value,
        estado_id: document.getElementById('tareaEstadoId').value,
        sprint_id: document.getElementById('tareaSprintId').value,
        asignado_a: $('#asignadoA').val() // Cambiado para obtener múltiples selecciones
    };

    fetch('../gant/add_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(tareaData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#addTareaModal').modal('hide'); // Cerrar el modal
            loadData();  // Cargar los datos actualizados

            // Mostrar SweetAlert de éxito
            Swal.fire({
                title: 'Éxito!',
                text: 'La tarea se ha añadido correctamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        } else {
            // Mostrar SweetAlert de error
            Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Mostrar SweetAlert de error en caso de un fallo en la petición
        Swal.fire({
            title: 'Error!',
            text: 'Ocurrió un error al intentar agregar la tarea.',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    });
});

function eliminarTarea() {
    const tareaId = document.getElementById('editTareaId').value;

    // Mostrar SweetAlert para confirmar la eliminación
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminarla',
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
            .then(response => response.text()) // Cambiar a .text() para ver el contenido de la respuesta
            .then(data => {
                console.log(data); // Ver qué devuelve realmente el servidor
                let jsonData;
                try {
                    jsonData = JSON.parse(data);
                    if (jsonData.success) {
                        // Mostrar SweetAlert de éxito
                        Swal.fire(
                            'Eliminada!',
                            'La tarea ha sido eliminada correctamente.',
                            'success'
                        ).then(() => {
                            location.reload(); // Recargar la página para reflejar los cambios
                        });
                    } else {
                        // Mostrar SweetAlert de error
                        Swal.fire(
                            'Error!',
                            jsonData.message,
                            'error'
                        );
                    }
                } catch (e) {
                    console.error('Error de JSON:', e, 'Respuesta del servidor:', data);
                    Swal.fire(
                        'Error!',
                        'Hubo un problema con la respuesta del servidor.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire(
                    'Error!',
                    'Ocurrió un error al intentar eliminar la tarea.',
                    'error'
                );
            });
        }
    });
}


document.getElementById('editSprintForm').addEventListener('submit', function (event) {
    event.preventDefault();

    let sprintData = {
        nombre: document.getElementById('editSprintName').value.trim(),
        fecha_inicio: document.getElementById('editSprintStartDate').value.trim(),
        fecha_fin: document.getElementById('editSprintEndDate').value.trim(),
        descripcion: document.getElementById('editSprintDescription').value.trim().replace(/\n/g, ''),
        estado_id: document.getElementById('sprintEstadoId').value.trim(),
        proyecto_id: document.getElementById('sprintProyectoId').value.trim(),
        id_sprint: document.getElementById('editSprintId').value.trim()
    };

    console.log("Datos a enviar:", sprintData); // Agrega esta línea para depuración

    // Mostrar SweetAlert de confirmación
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Quieres guardar los cambios en el sprint?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../gant/update_sprint.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(sprintData)
            }).then(response => response.json())
              .then(data => {
                  if (data.status === 'success') {
                      $('#editSprintModal').modal('hide');
                      loadData();
                      Swal.fire(
                          'Guardado!',
                          'Los cambios han sido guardados.',
                          'success'
                      );
                  } else {
                      Swal.fire(
                          'Error!',
                          data.message,
                          'error'
                      );
                  }
              }).catch(error => {
                  console.error('Error:', error);
                  Swal.fire(
                      'Error!',
                      'Ocurrió un error al intentar guardar los cambios.',
                      'error'
                  );
              });
        }
    });
});


document.getElementById('editTareaForm').addEventListener('submit', function (event) {
    event.preventDefault();

    // Recolectar los datos del formulario
    let tareaData = {
        id: document.getElementById('editTareaId').value, // Cambiado a 'id' para coincidir con el backend
        titulo: document.getElementById('editTareaTitulo').value,
        descripcion: document.getElementById('editTareaDescripcion').value,
        fecha_inicio: document.getElementById('editTareaFechaInicio').value,
        fecha_fin: document.getElementById('editTareaFechaFin').value,
        estado_id: document.getElementById('tareaEstadoId').value,
        asignados: document.getElementById('editTareaAsignadoA').value ? [parseInt(document.getElementById('editTareaAsignadoA').value)] : [] // Convertir a array si existe
    };

    // Realizar la solicitud al backend
    fetch('../gant/update_tarea.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(tareaData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log("Tarea actualizada correctamente", data);
        } else {
            console.error("Error al actualizar la tarea:", data.message);
        }
    })
    .catch(error => console.error('Error en la petición:', error));

    // Cerrar el modal después de enviar la solicitud
    $('#editTareaModal').modal('hide');
});



</script>
</body>
</html>
