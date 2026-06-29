<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin</title>
</head>
<body>

    <h2>Panel Administrativo</h2>

    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION["usuario"]); ?></p>
    <p>Rol: <?php echo htmlspecialchars($_SESSION["rol"]); ?></p>

    <a href="logout.php">Cerrar sesión</a>

</body>
</html>