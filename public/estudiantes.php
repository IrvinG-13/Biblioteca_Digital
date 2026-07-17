<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/EstudianteController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$controller = new EstudianteController();
$datos = $controller->listar();

$token = Csrf::generarToken();
$exito = $_GET["exito"] ?? "";
$error = $_GET["error"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Estudiantes - Biblioteca Digital</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">
        <div class="content-card">

            <div class="page-header">
                <h2>Gestión de Estudiantes</h2>
                <a class="btn btn-primary" href="estudiante_form.php">+ Nuevo Estudiante</a>
            </div>

            <?php if ($exito === "1"): ?>
                <div class="alert alert-success">Operación realizada con éxito.</div>
            <?php elseif ($error === "tienereservas"): ?>
                <div class="alert alert-error">No se puede eliminar: este estudiante tiene reservas registradas.</div>
            <?php endif; ?>

            <form class="actions-bar" action="estudiantes.php" method="GET">
                <input class="search-input" type="text" name="busqueda"
                       placeholder="Buscar por CIP, nombre o apellido..."
                       value="<?php echo htmlspecialchars($datos["busqueda"]); ?>">

                <button class="btn btn-secondary" type="submit">Buscar</button>
                <a class="btn btn-secondary" href="estudiantes.php">Limpiar</a>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>CIP</th>
                        <th>Nombre completo</th>
                        <th>Fecha Nac.</th>
                        <th>Carrera</th>
                        <th>Cuenta vinculada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($datos["estudiantes"])): ?>
                        <tr>
                            <td colspan="6">No se encontraron estudiantes.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($datos["estudiantes"] as $e): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($e["cip"]); ?></td>

                                <td>
                                    <?php
                                        echo htmlspecialchars(
                                            $e["primer_nombre"] . " " .
                                            ($e["segundo_nombre"] ? $e["segundo_nombre"] . " " : "") .
                                            $e["primer_apellido"] . " " .
                                            ($e["segundo_apellido"] ?? "")
                                        );
                                    ?>
                                </td>

                                <td><?php echo htmlspecialchars($e["fecha_nacimiento"]); ?></td>
                                <td><?php echo htmlspecialchars($e["carrera_nombre"]); ?></td>

                                <td>
                                    <?php if ($e["usuario_id"]): ?>
                                        <span class="badge badge-green">Sí</span>
                                    <?php else: ?>
                                        <span class="badge badge-yellow">No</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <a class="btn btn-link" href="estudiante_form.php?id=<?php echo $e["id"]; ?>">
                                        Editar
                                    </a>

                                    <form action="estudiante_eliminar.php" method="POST" style="display:inline;"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este estudiante?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="id" value="<?php echo $e["id"]; ?>">
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
                        <a href="estudiantes.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($datos["busqueda"]); ?>">
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