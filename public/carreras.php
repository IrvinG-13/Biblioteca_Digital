<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/CarreraController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$controller = new CarreraController();
$datos = $controller->listar();

$token = Csrf::generarToken();
$exito = $_GET["exito"] ?? "";
$error = $_GET["error"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Carreras - Biblioteca Digital</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">
        <div class="content-card">

            <div class="page-header">
                <h2>Gestión de Carreras</h2>
                <a class="btn btn-primary" href="carrera_form.php">+ Nueva Carrera</a>
            </div>

            <?php if ($exito === "1"): ?>
                <div class="alert alert-success">Operación realizada con éxito.</div>
            <?php elseif ($error === "tieneestudiantes"): ?>
                <div class="alert alert-error">No se puede eliminar: hay estudiantes asignados a esta carrera.</div>
            <?php endif; ?>

            <form class="actions-bar" action="carreras.php" method="GET">
                <input class="search-input" type="text" name="busqueda" placeholder="Buscar carrera..."
                       value="<?php echo htmlspecialchars($datos["busqueda"]); ?>">

                <button class="btn btn-secondary" type="submit">Buscar</button>
                <a class="btn btn-secondary" href="carreras.php">Limpiar</a>
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
                    <?php if (empty($datos["carreras"])): ?>
                        <tr>
                            <td colspan="3">No se encontraron carreras.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($datos["carreras"] as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c["id"]); ?></td>
                                <td><?php echo htmlspecialchars($c["nombre"]); ?></td>
                                <td>
                                    <a class="btn btn-link" href="carrera_form.php?id=<?php echo $c["id"]; ?>">Editar</a>

                                    <form action="carrera_eliminar.php" method="POST" style="display:inline;"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar esta carrera?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="id" value="<?php echo $c["id"]; ?>">
                                        <button class="btn btn-danger" type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php for ($i = 1; $i <= $datos["totalPaginas"]; $i++): ?>
                    <?php if ($i === $datos["paginaActual"]): ?>
                        <strong><?php echo $i; ?></strong>
                    <?php else: ?>
                        <a href="carreras.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($datos["busqueda"]); ?>">
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