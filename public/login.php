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
</head>
<body>

    <h2>Biblioteca Digital</h2>
    <h3>Inicio de sesión</h3>

    <?php if ($error === "credenciales"): ?>
        <p style="color:red;">Usuario o contraseña incorrectos.</p>
    <?php elseif ($error === "bloqueado"): ?>
        <p style="color:red;">La cuenta fue bloqueada por 3 intentos fallidos.</p>
    <?php elseif ($error === "datos"): ?>
        <p style="color:red;">Datos inválidos. La contraseña debe tener entre 8 y 12 caracteres.</p>
    <?php endif; ?>

    <form action="procesar_login.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

        <label>Usuario:</label><br>
        <input type="text" name="usuario" required><br><br>

        <label>Contraseña:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Ingresar</button>
    </form>

</body>
</html>