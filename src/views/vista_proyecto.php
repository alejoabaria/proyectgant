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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .tasks-section {
            margin-top: 10px;
            display: flex;
            width: 100%;
            gap: 20px;
        }

        .task-report, .task-assigned {
            width: 48%;
            background-color: #ffffff;
            padding: 2px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-height: 800px;
            overflow-y: auto;
        }

        /* Estilo para el scroll azul */
        .task-report::-webkit-scrollbar, .task-assigned::-webkit-scrollbar {
            width: 8px;
        }

        .task-report::-webkit-scrollbar-track, .task-assigned::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .task-report::-webkit-scrollbar-thumb, .task-assigned::-webkit-scrollbar-thumb {
            background-color: #3765db;
            border-radius: 10px;
        }

        .task-report h2, .task-assigned h2 {
            text-align: center;
            font-size: 30px;
            font-weight: bold;
            color: #5271ff;
            padding-top: 15px;
            font-weight: bold;
            border-radius: 5px;
        }

        .task-card {
            background-color: #f2f2f2;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
        }

        .task-card p {
            text-align: center;

            margin: 0 0 10px;
            font-size: 14px;
            line-height: 1.5;
        }

        .btn-task {
            display: block;
            width: 100%;
            padding: 10px;
            text-align: center;
            background-color: #3765db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s, box-shadow 0.3s;
        }

        .btn-task:hover {
            background-color: #2d54b8;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-task:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .btn-task:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(55, 101, 219, 0.5);
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
    <div class="tasks-section">
        <div class="task-report">
            <h2>Informe de Tareas Realizada</h2>
            <div class="task-card">
                <p><strong>Tarea</strong></p>
                <p><strong>Detalles</strong><br>Asignados: alejo abaria<br>Jefe de proyecto: Alejo capillera</p>
                <button class="btn-task">Ver más</button>
            </div>
            <div class="task-card">
                <p><strong>Tarea</strong></p>
                <p><strong>Detalles</strong><br>Asignados: Fede diaz<br>Jefe de proyecto: Ciro isarte</p>
                <button class="btn-task">Ver más</button>
            </div>
        </div>

        <div class="task-assigned">
            <h2>Mis Tareas Asignadas</h2>
            <div class="task-card">
                <p><strong>Tarea</strong></p>
                <p><strong>Detalles</strong><br>Asignados: alejo abaria<br>Fecha inicio: 20/8/24<br>Fecha límite: 1/10/24<br>Jefe de proyecto: Alejo capillera</p>
                <button class="btn-task">Marcar Terminado</button>
            </div>
            <div class="task-card">
                <p><strong>Tarea</strong></p>
                <p><strong>Detalles</strong><br>Asignados: alejo abaria<br>Fecha inicio: 20/8/24<br>Fecha límite: 1/10/24<br>Jefe de proyecto: Alejo capillera</p>
                <button class="btn-task">Marcar Terminado</button>
            </div>
        </div>
    </div>
</main>
</body>
</html>
