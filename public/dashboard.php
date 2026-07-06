<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin</title>
</head>
<body>

    <h2>Panel Administrativo</h2>

    <?php include __DIR__ . '/menu.php'; ?>

    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION["usuario"]); ?></p>
    <p>Rol: <?php echo htmlspecialchars($_SESSION["rol"]); ?></p>

</body>
</html>