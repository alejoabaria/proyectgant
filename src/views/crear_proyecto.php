<?php
session_start();
require_once '../includes/conexion.php';
require_once '../includes/dashboard.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

$dni_profesor = $_SESSION['usuario_id'];
$materia = isset($_GET['materia']) ? $_GET['materia'] : '';

if (empty($materia)) {
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Materia no especificada.'
                     }).then(function() {
                    setTimeout(function() {
                        window.location.href = 'materias_profesor.php';
                    }, 1000);
                }).then(function() {
                    window.location.href = 'index.php';
                });
            });
          </script>";
    exit();
}

// Escapar la entrada para evitar inyecciones SQL
$materia = $conexion->real_escape_string($materia);

// Obtener el ID de la materia actual
$sql_materia = "SELECT id FROM materias WHERE nombre = '$materia'";
$result_materia = $conexion->query($sql_materia);

if ($result_materia === false) {
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error en la consulta de materia: " . $conexion->error . "'
                }).then(function() {
                    window.location.href = 'index.php';
                });
            });
          </script>";
    exit();
}

if ($result_materia->num_rows > 0) {
    $row_materia = $result_materia->fetch_assoc();
    $materia_id = $row_materia['id'];

    // Obtener el ID del curso actual basado en la materia
    $sql_curso = "
    SELECT cc.id AS curso_id
    FROM cursosciclolectivo cc
    INNER JOIN asignacionesalumnos aa ON cc.id = aa.id_cursosciclolectivo
    INNER JOIN alumnos a ON aa.dni_alumnos = a.dni
    WHERE a.dni IN (
        SELECT a.dni
        FROM alumnos a
        INNER JOIN asignacionesalumnos aa ON a.dni = aa.dni_alumnos
        WHERE aa.id_cursosciclolectivo IN (
            SELECT id
            FROM cursosciclolectivo
            WHERE id_cursos = $materia_id
        )
    )
    AND cc.ciclolectivo = YEAR(CURDATE())
    LIMIT 1";
    
    $result_curso = $conexion->query($sql_curso);

    if ($result_curso === false) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error en la consulta del curso: " . $conexion->error . "'
                    }).then(function() {
                        window.location.href = 'index.php';
                    });
                });
              </script>";
        exit();
    }

    if ($result_curso->num_rows > 0) {
        $row_curso = $result_curso->fetch_assoc();
        $curso_id = $row_curso['curso_id'];
    } else {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se encontró el ID del curso para la materia.'
                    }).then(function() {
                        window.location.href = 'index.php';
                    });
                });
              </script>";
        exit();
    }

    // Consulta para obtener los alumnos del curso correspondiente
    $sql_alumnos = "
    SELECT a.dni, a.nombre, a.apellido
    FROM alumnos a
    INNER JOIN asignacionesalumnos aa ON a.dni = aa.dni_alumnos
    INNER JOIN cursosciclolectivo cc ON aa.id_cursosciclolectivo = cc.id
    WHERE cc.id = $curso_id";
    
    $result_alumnos = $conexion->query($sql_alumnos);

    if ($result_alumnos === false) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error en la consulta de alumnos: " . $conexion->error . "'
                    }).then(function() {
                        window.location.href = 'index.php';
                    });
                });
              </script>";
        exit();
    }
  
    // Manejo del formulario de creación de proyecto
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = $_POST['nombre'];
        $objetivo = $_POST['objetivo'];
        $tiene_salida = $_POST['tiene_salida'];
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
        $tiene_pm = isset($_POST['tiene_project_manager']) ? true : false;
        $project_manager = $tiene_pm ? $_POST['project_manager'] : $dni_profesor;
        $integrantes = $_POST['integrantes'];

        // Validar si el Project Manager seleccionado también está en los integrantes
        if ($tiene_pm && in_array($project_manager, $integrantes)) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'El Project Manager no puede ser también un integrante.'
                                                 }).then(function() {
                    setTimeout(function() {
                        window.location.href = 'materias_profesor.php';
                    }, 1000);
                        }).then(function() {
                            window.location.href = 'index.php';
                        });
                    });
                  </script>";
            exit();
        }

        // Continuar con la lógica de creación de proyecto...

        // Verifica si el cupof existe en la tabla cupof
        $sql_cupof = "SELECT cupof FROM cupof WHERE id_materias = $materia_id";
        $result_cupof = $conexion->query($sql_cupof);

        if ($result_cupof->num_rows > 0) {
            $row_cupof = $result_cupof->fetch_assoc();
            $cupof = $row_cupof['cupof'];

            // Insertar el nuevo proyecto en la base de datos con el estado "En proceso" (ID = 1)
            $sql_proyecto = "INSERT INTO proyectos (nombre, descripcion, fecha_inicio, fecha_fin, cupof, objetivo, tiene_salida, project_manager_id, creador_id, etapa_general) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt_proyecto = $conexion->prepare($sql_proyecto);

            if ($stmt_proyecto === false) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error en la preparación de la consulta de proyectos: " . $conexion->error . "'
                            }).then(function() {
                                window.location.href = 'index.php';
                            });
                        });
                      </script>";
                exit();
            }

            $estado_inicial = 1;
            $stmt_proyecto->bind_param('ssssssssii', $nombre, $objetivo, $fecha_inicio, $fecha_fin, $cupof, $objetivo, $tiene_salida, $project_manager, $dni_profesor, $estado_inicial);

            if ($stmt_proyecto->execute()) {
                $proyecto_id = $stmt_proyecto->insert_id;

                // Insertar los integrantes
                foreach ($integrantes as $integrante) {
                    $sql_integrantes_alumno = "INSERT INTO proyectointegrantes (proyecto_id, alumno_id) 
                                               VALUES (?, ?)";
                    $stmt_integrantes_alumno = $conexion->prepare($sql_integrantes_alumno);

                    if ($stmt_integrantes_alumno === false) {
                        echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Error en la preparación de la consulta de integrantes: " . $conexion->error . "'
                                    }).then(function() {
                                        window.location.href = 'index.php';
                                    });
                                });
                              </script>";
                        exit();
                    }

                    $stmt_integrantes_alumno->bind_param('ii', $proyecto_id, $integrante);

                    if (!$stmt_integrantes_alumno->execute()) {
                        echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Error al insertar integrante: " . $stmt_integrantes_alumno->error . "'
                                    }).then(function() {
                                        window.location.href = 'index.php';
                                    });
                                });
                              </script>";
                    }

                    $stmt_integrantes_alumno->close();
                }

                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'El proyecto fue creado exitosamente.'

                            }).then(function() {
                                window.location.href = 'materias_profesor.php';
                            });
                        });
                      </script>";
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al crear el proyecto: " . $stmt_proyecto->error . "'
                            }).then(function() {
                                window.location.href = 'index.php';
                            });
                        });
                      </script>";
            }
            $stmt_proyecto->close();
        }
    }
}
?>

<!-- Asegúrate de incluir las librerías de SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>









<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Proyectos de <?php echo htmlspecialchars($materia); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
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
        .hidden {
            display: none;
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
          <li class="breadcrumb-item text-sm text-white active" aria-current="page">Crear proyecto</li>
        </ol>
      </nav>
    </div>
  </nav>
    <div class="container mt-5">
        <h2 class="text-center">Proyectos de <?php echo htmlspecialchars($materia); ?></h2>
        
        <!-- Formulario de creación de proyecto -->
            <div class="card-body">
                <h5 class="card-title">Crear nuevo proyecto</h5>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?materia=' . urlencode($materia); ?>" method="post">
                    <div class="form-group mb-3">
                        <label for="nombre">Nombre del proyecto</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre del Proyecto" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="objetivo">Objetivo del proyecto</label>
                        <input type="text" class="form-control" id="objetivo" name="objetivo" placeholder="Objetivo del Proyecto" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="tiene_salida">¿Tiene salida fuera de la institución?</label>
                        <select class="form-control" id="tiene_salida" name="tiene_salida" required>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="fecha_inicio">Fecha de inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="fecha_fin">Fecha de fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                    </div>
                    <div class="form-group mb-3">
                        <label>
                            <input type="checkbox" id="tiene_project_manager" name="tiene_project_manager">
                            ¿Tiene lider de proyecto? si no tiene se le asignara a usted y tendra que encargarse de gestionar el proyecto
                        </label>
                        <div id="project_manager_container" class="hidden">
                        <label for="project_manager">Seleccionar lider de proyecto</label>
                       <select class="form-control" id="project_manager" name="project_manager"> 
                       <?php
                               $result_alumnos = $conexion->query($sql_alumnos);

                              if ($result_alumnos === false) {
                                  die("Error en la consulta de alumnos: " . $conexion->error);
                                   }

                              if ($result_alumnos->num_rows > 0) {
                                 while ($row_alumno = $result_alumnos->fetch_assoc()) {
                                    echo "<option value=\"" . htmlspecialchars($row_alumno['dni']) . "\">" . htmlspecialchars($row_alumno['nombre']) . " " . htmlspecialchars($row_alumno['apellido']) . "</option>";
                                    }
                                 } else {
                                    echo "<option value=\"\">No hay alumnos disponibles</option>";
                                 }
                            ?>
                        </select>
                    </div>
                    <label for="integrantes">Seleccionar integrantes</label>
                        <select class="form-control" id="integrantes" name="integrantes[]" multiple="multiple" required>
                        <?php
                               $result_alumnos = $conexion->query($sql_alumnos);

                              if ($result_alumnos === false) {
                                  die("Error en la consulta de alumnos: " . $conexion->error);
                                   }

                              if ($result_alumnos->num_rows > 0) {
                                 while ($row_alumno = $result_alumnos->fetch_assoc()) {
                                    echo "<option value=\"" . htmlspecialchars($row_alumno['dni']) . "\">" . htmlspecialchars($row_alumno['nombre']) . " " . htmlspecialchars($row_alumno['apellido']) . "</option>";
                                    }
                                 } else {
                                    echo "<option value=\"\">No hay alumnos disponibles</option>";
                                 }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Crear proyecto</button>
                </form>
            </div>
        </main>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializar Select2 para mejor UX
        $('#proyectos').DataTable();
        $('#integrantes').select2();

        $('#tiene_project_manager').change(function() {
    if ($(this).is(':checked')) {
        $('#project_manager_container').removeClass('hidden');
        $('#project_manager').prop('required', true);
    } else {
        $('#project_manager_container').addClass('hidden');
        $('#project_manager').prop('required', false);
        $('#project_manager').val(''); // Limpiar selección de PM
        $('#integrantes option').prop('disabled', false); // Habilitar todos los integrantes
    }
});

$('#project_manager').change(function() {
    var selectedPM = $(this).val(); // Obtener el valor seleccionado
    $('#integrantes option').each(function() {
        if ($(this).val() === selectedPM) {
            $(this).prop('disabled', true); // Deshabilitar la opción si es el mismo que PM
        } else {
            $(this).prop('disabled', false); // Habilitar de nuevo si cambia
        }
    });

    // Refrescar Select2 para reflejar cambios
    $('#integrantes').trigger('change.select2');
});

    });
</script>

</body>
</html>