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

    <title>Inicio administrativo | ReadPoint</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/dashboard.css?v=1">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <?php if (($_GET["password_actualizada"] ?? "") === "1"): ?>
            <div class="alert alert-success">
                Tu contraseña fue actualizada correctamente.
            </div>
        <?php endif; ?>

        <section class="encabezado-dashboard">

            <div>
                <span class="etiqueta-dashboard">
                    Panel administrativo
                </span>

                <h1>
                    Bienvenido, <?php echo $nombreUsuario; ?>
                </h1>

                <p>
                    Consulta el estado general de ReadPoint y accede
                    rápidamente a las funciones principales.
                </p>
            </div>

            <a
                class="boton-principal-dashboard"
                href="libro_form.php"
            >
                Agregar libro
            </a>

        </section>

        <section class="resumen-dashboard">

            <a class="tarjeta-resumen" href="usuarios.php">
                <span class="titulo-resumen">
                    Usuarios
                </span>

                <strong class="numero-resumen">
                    <?php echo $usuarios; ?>
                </strong>

                <span class="detalle-resumen">
                    Usuarios registrados
                </span>
            </a>

            <a class="tarjeta-resumen" href="estudiantes.php">
                <span class="titulo-resumen">
                    Estudiantes
                </span>

                <strong class="numero-resumen">
                    <?php echo $estudiantes; ?>
                </strong>

                <span class="detalle-resumen">
                    Estudiantes registrados
                </span>
            </a>

            <a class="tarjeta-resumen" href="categorias.php">
                <span class="titulo-resumen">
                    Categorías
                </span>

                <strong class="numero-resumen">
                    <?php echo $categorias; ?>
                </strong>

                <span class="detalle-resumen">
                    Categorías disponibles
                </span>
            </a>

            <a class="tarjeta-resumen" href="libros.php">
                <span class="titulo-resumen">
                    Libros
                </span>

                <strong class="numero-resumen">
                    <?php echo $libros; ?>
                </strong>

                <span class="detalle-resumen">
                    Libros registrados
                </span>
            </a>

        </section>

        <div class="columnas-dashboard">

            <section class="panel-dashboard">

                <div class="encabezado-panel">
                    <div>
                        <h2>Acciones rápidas</h2>

                        <p>
                            Funciones utilizadas con mayor frecuencia.
                        </p>
                    </div>
                </div>

                <div class="acciones-dashboard">

                    <a href="usuarios.php">
                        Gestionar usuarios
                    </a>

                    <a href="estudiantes.php">
                        Gestionar estudiantes
                    </a>

                    <a href="libro_form.php">
                        Registrar libro
                    </a>

                    <a href="categoria_form.php">
                        Crear categoría
                    </a>

                    <a href="reporte_reservas.php">
                        Consultar reservas
                    </a>

                    <a href="libro_exportar.php">
                        Exportar libros
                    </a>

                </div>

            </section>

            <section class="panel-dashboard">

                <div class="encabezado-panel">
                    <div>
                        <h2>Actividad reciente</h2>

                        <p>
                            Movimientos recientes del sistema.
                        </p>
                    </div>
                </div>

                <div class="estado-vacio-dashboard">
                    <strong>Sin actividad reciente</strong>

                    <span>
                        Todavía no hay movimientos registrados para mostrar.
                    </span>
                </div>

            </section>

        </div>

    </main>

</div>

</body>
</html>