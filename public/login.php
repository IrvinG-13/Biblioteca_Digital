<?php
session_start();

require_once __DIR__ . '/../app/Core/Csrf.php';

$token = Csrf::generarToken();
$error = $_GET["error"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login - Biblioteca Digital</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="login-page">

<div class="login-container">

    <div class="login-card">

        <div class="login-header">
            <h1>Biblioteca Digital</h1>
            <p>Inicia sesión para continuar</p>
        </div>

        <?php if ($error === "credenciales"): ?>

            <div class="alert alert-error">
                Usuario o contraseña incorrectos.
            </div>

        <?php elseif ($error === "bloqueado"): ?>

            <div class="alert alert-error">
                La cuenta fue bloqueada por 3 intentos fallidos.
            </div>

        <?php elseif ($error === "datos"): ?>

            <div class="alert alert-error">
                Datos inválidos. La contraseña debe tener entre 8 y 12 caracteres.
            </div>

        <?php endif; ?>

        <form action="procesar_login.php" method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo $token; ?>"
            >

            <div class="form-group">
                <label>Usuario</label>

                <input
                    type="text"
                    name="usuario"
                    placeholder="Ingrese su usuario"
                    required
                >
            </div>

            <div class="form-group">
                <label>Contraseña</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Ingrese su contraseña"
                    required
                >
            </div>

            <button class="btn btn-primary btn-full" type="submit">
                Ingresar
            </button>

        </form>

    </div>

</div>

</body>
</html>