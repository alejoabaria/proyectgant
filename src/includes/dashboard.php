<?php
require_once '../includes/conexion.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/barra.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css"> <!-- Agregar Bootstrap Icons -->
    <style>
       html, body {
    height: 100%; /* Asegura que el body ocupe toda la altura de la ventana */
    margin: 0; /* Eliminar márgenes por defecto */
    font-family: Arial, sans-serif;
}
    .menu{
        position:fixed;
        z-index:10000;
        display:block;
        margin:1%;
        margin-left:0,5%;
        float:left;
        height:95%;
        width:18%;
        background-color:#fff;
        border-radius:20px;
        box-shadow: 0 2px 25px rgba(0, 0, 0, 0.13); 
    }

    .superior{
        float:top;
        width:auto;
        height:10%;
        background-color:transparent;
        margin-top: 10px;
        border-bottom:4px solid #F0F0F0;
        display: flex;
        align-items: center;
    }

    .imagen{
        margin-left:5%;
        width:45px;
        height:45px;
    }
    /* botones */

    .button1{
        margin-left:2.5%;
        margin-top:3%;
        margin-bottom:3%;
        width:95%;  
        height:8%;
        background-color:transparent;
        border-radius:15px;
        border:0px solid;
        text-decoration:none;
        cursor:pointer;
        text-align:left;
        transition: background-color 0.5s ease; 
        color:#A3A5A4;
        display: flex; /* Usar flexbox para alinear los elementos */
        align-items: center; /* Centrar verticalmente los elementos */
    }

    .button1:hover{
        background-color:#F5F9FC;
    }
    .bi{
        font-size:20px;
        margin-left:7%;
    }
    /* colores logos */
    .bi-display{
        color:#74AEF5;
    }
    .bi-inboxes-fill{
        color:#429C46;
    }
    .bi-person-fill{
        color:#232527;
    }

    .bi-door-open-fill{
        color:#AD6E78;
    }
    /* /botones */
    .menu h1{
        font-weight:bold;
        color:#A3A5A4;
        font-size:15px;
        text-align:left;
        margin-left:10%;
        margin-top:8%;
        margin-bottom:8%;
    }

    .menu h2{
        font-weight:bold;
        color:#A3A5A4;
        font-size:15px;
        text-align:left;
        margin-left:10%;
    }

    .superior h3{
        font-size:18px;
        color:#7B8396;
        margin-left:10%;
    }
    .button{
        display:none;
    }
    .buttonn{
        
        display:none;

    }
    @media (max-width: 768px){
        .menu{
      margin:4%;
        display:none;
        position: fixed; /* Fijo en la pantalla */
        left: 0;
        top: 0;
        height: 100%;
        width: 60%; /* Ancho del menú en móviles */
        z-index: 1000; /* Por encima del contenido */
        background-color: #fff;
        transition: transform 0.5s ease; /* Transición para la entrada y salida */
        box-shadow: 0 2px 25px rgba(0, 0, 0, 0.13); 
    }
        .slide-in {
            transform: translateX(0); /* Mostrar el menú */
            animation: slideIn 0.5s forwards; /* Aplicar animación de entrada */
        }

        .slide-out {
            transform: translateX(-100%); /* Ocultar el menú fuera de la vista */
            animation: slideOut 0.5s forwards; /* Aplicar animación de salida */
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%); /* Comienza fuera de la pantalla a la izquierda */
            }
            to {
                transform: translateX(0); /* Termina en su posición original */
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0); /* Comienza en su posición original */
            }
            to {
                transform: translateX(-100%); /* Termina fuera de la pantalla a la izquierda */
            }
        }
        .superior .button{
        margin-left: auto;
        display:block;
        background-color:transparent;
        font-size:20px;
        font-weight:bold;
        color:#2E2E2E;
        text-decoration:none;
        border:0px solid;
        cursor:pointer;
        float:right;
    }

    .button{
        position: relative;
        z-index: 1000;
        margin-top:1%;
        padding:2px;
        width:55px;
        height:50px;
        display:block;
        background-color:#fff;
        font-size:40px;
        font-weight:bold;
        color:#2E2E2E;
        text-decoration:none;
        border:0px solid;
        cursor:pointer;
        float:right;

    }
    .buttonn{
        position: relative;
        z-index: 1000;
        margin-top:1%;
        padding:2px;
        width:55px;
        height:50px;
        display:block;
        background-color:#fff;
        border-radius: 0 20px 20px 0; /* Esquinas redondeadas solo a la derecha */
        font-size:40px;
        font-weight:bold;
        color:#2E2E2E;
        text-decoration:none;
        border:0px solid;
        cursor:pointer;
        float:left;
        box-shadow: 0 2px 25px rgba(0, 0, 0, 0.13); 

    }
    .buttonn .bi{
        display: flex;
        align-items: center;
        justify-content: center;
        font-size:30px;
        margin-left:none;
    }
    .superior h3 {
        font-size: 15px;
        font-weight: bold;
        color: #7B8396;
        margin-left: 8%;
    }

    }
    </style>
</head>
<div class="azul"></div>

    <div class="menu">

        <div class="superior">
        <img src="https://www.tecnica1lacosta.edu.ar/img/logoblanco.png" class="imagen">
        <h3>ProyectManager</h3>
            <button class="button" id=ocultar>
            <i class="bi bi-list"></i> 
            </button>
        </div>
        <a class="button1" href="../views/inicio.php">
        <i class="bi bi-display"></i><h2>Panel</h2>
</a>

         <!-- Botón para "Mis proyectos" según el tipo de usuario -->
        <?php if ($tipo_usuario === 'alumno'): ?>
            
            <a class="button1" href="../views/proyecto_alumno.php">
                <i class="bi bi-inboxes-fill"></i><h2>Mis proyectos</h2>
            </a>


        <?php elseif ($tipo_usuario === 'profesor'): ?>
                <a class="button1" href="../views/materias_profesor.php">
                    <i class="bi bi-inboxes-fill"></i><h2>Mis materias</h2>
                </a>
        <?php endif; ?>

        <!-- Sección de perfil y cierre de sesión -->
        <h1>MI PERFIL</h1>
        <button class="button1">
        <i class="bi bi-gear"></i><h2>Configuración</h2>
        </button>
        <a class="button1" href="../php/logout.php">
         <i class="bi bi-door-open-fill"></i><h2>Cerrar sesión</h2>
        </a>

    </div>

    <button class="buttonn" id=mostrar >
    <i class="bi bi-list"></i> <!-- Icono de menú -->
    </button>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mostrarBtn = document.getElementById('mostrar');
        const ocultarBtn = document.getElementById('ocultar');
        const menu = document.querySelector('.menu');
        const buttonn = document.querySelector('.buttonn'); // Selecciona el botón con clase 'button'

        mostrarBtn.addEventListener('click', function() {
            menu.style.display = 'block'; // Mostrar el menú
            buttonn.style.display = 'none'; // Ocultar el botón con clase 'button'
            menu.classList.remove('slide-out'); // Asegurarse de que no esté en la animación de salida
            menu.classList.add('slide-in'); // Agregar clase para animación de entrada
            setTimeout(() => {
                menu.classList.remove('slide-in'); // Limpiar la clase de animación después de que se muestre
            }, 500); // Esperar a que termine la animación
        });

        ocultarBtn.addEventListener('click', function() {
            menu.classList.remove('slide-in'); // Asegurarse de que no esté en la animación de entrada
            menu.classList.add('slide-out'); // Agregar clase para animación de salida
            setTimeout(() => {
                menu.style.display = 'none';
                buttonn.style.display = 'block'; // Mostrar el botón hamburguesa después de la animación
            }, 500);
        });

        // Evento para ajustar el menú cuando la ventana se redimensiona
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                // Si la pantalla es mayor a 768px, mostrar el menú grande
                menu.style.display = 'block';
                buttonn.style.display = 'none'; // Ocultar botón hamburguesa
            } else {
                // Si la pantalla es menor a 768px, ocultar el menú grande
                if (!menu.classList.contains('slide-in')) {
                    menu.style.display = 'none';
                    buttonn.style.display = 'block'; // Mostrar el botón hamburguesa
                }
            }
        });

        if (window.innerWidth > 768) {
            menu.style.display = 'block';
            buttonn.style.display = 'none';
        } else {
            menu.style.display = 'none';
            buttonn.style.display = 'block';
        }
    });
</script>

</body>

</html>