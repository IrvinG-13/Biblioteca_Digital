<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$rolActual = $_SESSION["rol"] ?? "";

if ($rolActual === "estudiante" || $rolActual === "profesor") {
    header("Location: catalogo.php");
    exit;
}

if ($rolActual !== "admin") {
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
require_once __DIR__ . '/../app/Core/Database.php';

NoCache::aplicar();

$usuarios = 0;
$estudiantes = 0;
$categorias = 0;
$libros = 0;

try {
    $conexion = new Database();
    $db = $conexion->conectar();

    $usuarios = (int)$db
        ->query("SELECT COUNT(*) FROM usuarios")
        ->fetchColumn();

    $estudiantes = (int)$db
        ->query("SELECT COUNT(*) FROM estudiantes")
        ->fetchColumn();

    $categorias = (int)$db
        ->query("SELECT COUNT(*) FROM categorias")
        ->fetchColumn();

    $libros = (int)$db
        ->query("SELECT COUNT(*) FROM libros")
        ->fetchColumn();
} catch (Throwable $e) {
    $usuarios = 0;
    $estudiantes = 0;
    $categorias = 0;
    $libros = 0;
}

$nombreUsuario = htmlspecialchars(
    (string)($_SESSION["usuario"] ?? ""),
    ENT_QUOTES,
    "UTF-8"
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Panel Administrativo</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">

        <div class="content-card">

            <?php if (($_GET["password_actualizada"] ?? "") === "1"): ?>
                <div class="alert alert-success">
                    Tu contraseña fue actualizada correctamente.
                </div>
            <?php endif; ?>

            <section class="dashboard-hero">
                <h1>Biblioteca Digital</h1>
                <p>
                    Sistema de administración de libros y reservas.
                </p>
                <p>
                    Bienvenido,
                    <strong>
                        <?php echo $nombreUsuario; ?>
                    </strong>
                </p>
            </section>

            <section class="stats-grid">

                <div class="stat-card">

                    <div class="stat-title">
                        Usuarios registrados
                    </div>

                    <div class="stat-number">
                        <?php echo $usuarios; ?>
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-title">
                        Estudiantes registrados
                    </div>

                    <div class="stat-number">
                        <?php echo $estudiantes; ?>
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-title">
                        Categorías de libros
                    </div>

                    <div class="stat-number">
                        <?php echo $categorias; ?>
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-title">
                        Libros registrados
                    </div>

                    <div class="stat-number">
                        <?php echo $libros; ?>
                    </div>

                </div>

            </section>

            <section class="quick-actions">

                <h3>Acciones rápidas</h3>

                <div class="quick-actions-grid">

                    <a
                        class="btn btn-secondary"
                        href="usuarios.php"
                    >
                        Usuarios
                    </a>

                    <a
                        class="btn btn-secondary"
                        href="estudiantes.php"
                    >
                        Estudiantes
                    </a>

                    <a
                        class="btn btn-secondary"
                        href="carreras.php"
                    >
                        Carreras
                    </a>

                    <a
                        class="btn btn-secondary"
                        href="categorias.php"
                    >
                        Categorías
                    </a>

                    <a
                        class="btn btn-secondary"
                        href="libros.php"
                    >
                        Libros
                    </a>

                    <a
                        class="btn btn-secondary"
                        href="libro_form.php"
                    >
                        + Agregar libro
                    </a>

                    <a
                        class="btn btn-secondary"
                        href="categoria_form.php"
                    >
                        + Nueva categoría
                    </a>

                    <a
                        class="btn btn-secondary"
                        href="libro_exportar.php"
                    >
                        Exportar libros
                    </a>

                    <a
                        class="btn btn-secondary"
                        href="reporte_reservas.php"
                    >
                        Reporte de reservas
                    </a>

                </div>

            </section>

            <section class="recent-activity">

                <h3>Últimas actividades</h3>

                <div class="activity-empty">
                    No hay actividad reciente registrada.
                </div>

            </section>

        </div>

    </main>

</div>

</body>

</html>