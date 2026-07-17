<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/CategoriaController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$token = Csrf::generarToken();

$error = $_GET["error"] ?? "";
$id = $_GET["id"] ?? null;

$categoriaActual = null;

if ($id !== null) {
    $controller = new CategoriaController();
    $categoriaActual = $controller->obtenerPorId((int)$id);

    if ($categoriaActual === null) {
        header("Location: categorias.php");
        exit;
    }
}

$esEdicion = $categoriaActual !== null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $esEdicion ? "Editar" : "Nueva"; ?> Categoría</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">
        <div class="content-card">

            <form class="form-card" action="categoria_procesar.php" method="POST">

                <div class="page-header">
                    <h2><?php echo $esEdicion ? "Editar Categoría" : "Nueva Categoría"; ?></h2>
                </div>

                <?php if ($error === "nombre"): ?>
                    <div class="alert alert-error">El nombre debe contener entre 3 y 100 caracteres.</div>
                <?php endif; ?>

                <?php if ($error === "duplicado"): ?>
                    <div class="alert alert-error">Ya existe una categoría con ese nombre.</div>
                <?php endif; ?>

                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

                <?php if ($esEdicion): ?>
                    <input type="hidden" name="id" value="<?php echo $categoriaActual["id"]; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Nombre de la categoría</label>
                    <input type="text" name="nombre" required maxlength="100"
                           value="<?php echo $esEdicion ? htmlspecialchars($categoriaActual["nombre"]) : ""; ?>">
                </div>

                <div class="form-actions">
                    <a class="btn btn-secondary" href="categorias.php">Cancelar</a>
                    <button class="btn btn-primary" type="submit">
                        <?php echo $esEdicion ? "Guardar cambios" : "Crear categoría"; ?>
                    </button>
                </div>

            </form>

        </div>
    </main>

</div>

</body>
</html>