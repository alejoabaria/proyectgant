<?php
require_once 'src/includes/conexion.php';

session_start();

$errores = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = filter_var($_POST['dni'], FILTER_SANITIZE_STRING);
    $contraseña = $_POST['contraseña'];

    if ($dni && $contraseña) {
        $dni = $conexion->real_escape_string($dni);
        $contraseña = $conexion->real_escape_string($contraseña);

        // Verifica si el usuario es un alumno
        $sql_alumno = "SELECT dni, clave AS contraseña FROM alumnos WHERE dni = '$dni' LIMIT 1";
        $resultado_alumno = $conexion->query($sql_alumno);

        if ($resultado_alumno->num_rows === 1) {
            $usuario = $resultado_alumno->fetch_assoc();

            // Verificar la contraseña
            if ($contraseña === $usuario['contraseña']) {
                // Iniciar sesión como alumno
                $_SESSION['usuario_id'] = $usuario['dni'];
                $_SESSION['tipo_usuario'] = 'alumno'; // Guardar tipo de usuario
                $_SESSION['logged_in'] = true;
                header("Location: src/views/inicio.php");
                exit();
            } else {
                $errores = 'DNI o contraseña incorrectos.';
            }
        } else {
            // Verifica si el usuario es un profesor
            $sql_profesor = "SELECT dni, pass AS contraseña FROM personal WHERE dni = '$dni' LIMIT 1";
            $resultado_profesor = $conexion->query($sql_profesor);

            if ($resultado_profesor->num_rows === 1) {
                $usuario = $resultado_profesor->fetch_assoc();

                // Verificar la contraseña
                if ($contraseña === $usuario['contraseña']) {
                    // Iniciar sesión como profesor
                    $_SESSION['usuario_id'] = $usuario['dni'];
                    $_SESSION['tipo_usuario'] = 'profesor'; // Guardar tipo de usuario
                    $_SESSION['logged_in'] = true;
                    header("Location: src/views/inicio.php");
                    exit();
                } else {
                    $errores = 'DNI o contraseña incorrectos.';
                }
            } else {
                $errores = 'DNI o contraseña incorrectos.';
            }
        }
    } else {
        $errores = 'Por favor, ingrese su DNI y contraseña.';
    }
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Inicio de Sesión</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="text-center">Iniciar Sesión</h2>
            <?php if ($errores): ?>
                <div class="alert alert-danger"><?php echo $errores; ?></div>
            <?php endif; ?>
            <form action="index.php" method="post">
                <div class="form-group">
                    <label for="dni">DNI:</label>
                    <input type="text" class="form-control" id="dni" name="dni" required>
                </div>
                <div class="form-group">
                    <label for="contraseña">Contraseña:</label>
                    <input type="password" class="form-control" id="contraseña" name="contraseña" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>