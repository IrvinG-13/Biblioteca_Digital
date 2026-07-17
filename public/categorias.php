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

$controller = new CategoriaController();
$datos = $controller->listar();

$token = Csrf::generarToken();

$exito = $_GET["exito"] ?? "";
$error = $_GET["error"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Categorías</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">

        <div class="content-card">

            <div class="page-header">
                <h2>Gestión de Categorías</h2>

                <a href="categoria_form.php" class="btn btn-primary">
                    + Nueva Categoría
                </a>
            </div>

            <?php if ($exito === "1"): ?>

                <div class="alert alert-success">
                    Operación realizada correctamente.
                </div>

            <?php elseif ($error === "tienelibros"): ?>

                <div class="alert alert-error">
                    No se puede eliminar porque existen libros asociados.
                </div>

            <?php endif; ?>

            <form class="actions-bar" action="categorias.php" method="GET">

                <input
                    class="search-input"
                    type="text"
                    name="busqueda"
                    placeholder="Buscar categoría..."
                    value="<?php echo htmlspecialchars($datos["busqueda"]); ?>"
                >

                <button class="btn btn-secondary" type="submit">
                    Buscar
                </button>

                <a class="btn btn-secondary" href="categorias.php">
                    Limpiar
                </a>

            </form>

            <table class="table">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (empty($datos["categorias"])): ?>

                    <tr>

                        <td colspan="3">

                            No existen categorías registradas.

                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($datos["categorias"] as $categoria): ?>

                        <tr>

                            <td>

                                <?php echo htmlspecialchars($categoria["id"]); ?>

                            </td>

                            <td>

                                <?php echo htmlspecialchars($categoria["nombre"]); ?>

                            </td>

                            <td>

                                <a
                                    class="btn btn-link"
                                    href="categoria_form.php?id=<?php echo $categoria["id"]; ?>">
                                    Editar
                                </a>

                                <form
                                    action="categoria_eliminar.php"
                                    method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('¿Eliminar esta categoría?');"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php echo $token; ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?php echo $categoria["id"]; ?>"
                                    >

                                    <button class="btn btn-danger" type="submit">
                                        Eliminar
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

            <div class="pagination">

                <?php for ($i = 1; $i <= $datos["totalPaginas"]; $i++): ?>

                    <?php if ($i == $datos["paginaActual"]): ?>

                        <strong><?php echo $i; ?></strong>

                    <?php else: ?>

                        <a href="categorias.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($datos["busqueda"]); ?>">

                            <?php echo $i; ?>

                        </a>

                    <?php endif; ?>

                <?php endfor; ?>

            </div>

        </div>

    </main>

</div>

</body>
</html>