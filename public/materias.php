<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/MateriaController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$controller = new MateriaController();
$datos = $controller->listar();

$token = Csrf::generarToken();
$exito = $_GET["exito"] ?? "";
$error = $_GET["error"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Materias - Biblioteca Digital</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/menu.php'; ?>
    <main class="main-content">
        <div class="content-card">
            <div class="page-header">
                <h2>Gestión de Materias</h2>
                <a class="btn btn-primary" href="materia_form.php">+ Nueva Materia</a>
            </div>

            <?php if ($exito === "1"): ?>
                <div class="alert alert-success">Operación realizada con éxito.</div>
            <?php elseif ($error === "tieneprofesores"): ?>
                <div class="alert alert-error">No se puede eliminar: hay profesores asignados a esta materia.</div>
            <?php endif; ?>

            <form class="actions-bar" action="materias.php" method="GET">
                <input class="search-input" type="text" name="busqueda" placeholder="Buscar materia..."
                       value="<?php echo htmlspecialchars($datos["busqueda"]); ?>">
                <button class="btn btn-secondary" type="submit">Buscar</button>
                <a class="btn btn-secondary" href="materias.php">Limpiar</a>
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
                    <?php if (empty($datos["materias"])): ?>
                        <tr>
                            <td colspan="3">No se encontraron materias.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($datos["materias"] as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m["id"]); ?></td>
                                <td><?php echo htmlspecialchars($m["nombre"]); ?></td>
                                <td>
                                    <a class="btn btn-link" href="materia_form.php?id=<?php echo $m["id"]; ?>">Editar</a>
                                    <form action="materia_eliminar.php" method="POST" style="display:inline;"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar esta materia?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="id" value="<?php echo $m["id"]; ?>">
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
                        <a href="materias.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($datos["busqueda"]); ?>">
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