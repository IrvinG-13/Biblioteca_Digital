<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/ProfesorController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$controller = new ProfesorController();
$datos = $controller->listar();

$token = Csrf::generarToken();
$exito = $_GET["exito"] ?? "";
$error = $_GET["error"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Profesores - Biblioteca Digital</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/menu.php'; ?>
    <main class="main-content">
        <div class="content-card">
            <div class="page-header">
                <h2>Gestión de Profesores</h2>
                <a class="btn btn-primary" href="profesor_form.php">+ Nuevo Profesor</a>
            </div>

            <?php if ($exito === "1"): ?>
                <div class="alert alert-success">Operación realizada con éxito.</div>
            <?php endif; ?>

            <form class="actions-bar" action="profesores.php" method="GET">
                <input class="search-input" type="text" name="busqueda"
                       placeholder="Buscar por cédula, nombre o apellido..."
                       value="<?php echo htmlspecialchars($datos["busqueda"]); ?>">
                <button class="btn btn-secondary" type="submit">Buscar</button>
                <a class="btn btn-secondary" href="profesores.php">Limpiar</a>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre completo</th>
                        <th>Materia</th>
                        <th>Cuenta vinculada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($datos["profesores"])): ?>
                        <tr>
                            <td colspan="5">No se encontraron profesores.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($datos["profesores"] as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p["cedula"]); ?></td>
                                <td>
                                    <?php
                                        echo htmlspecialchars(
                                            $p["primer_nombre"] . " " .
                                            ($p["segundo_nombre"] ? $p["segundo_nombre"] . " " : "") .
                                            $p["primer_apellido"] . " " .
                                            ($p["segundo_apellido"] ?? "")
                                        );
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($p["materia_nombre"]); ?></td>
                                <td>
                                    <?php if ($p["usuario_id"]): ?>
                                        <span class="badge badge-green">Sí</span>
                                    <?php else: ?>
                                        <span class="badge badge-yellow">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a class="btn btn-link" href="profesor_form.php?id=<?php echo $p["id"]; ?>">
                                        Editar
                                    </a>
                                    <form action="profesor_eliminar.php" method="POST" style="display:inline;"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este profesor?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="id" value="<?php echo $p["id"]; ?>">
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
                        <a href="profesores.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($datos["busqueda"]); ?>">
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