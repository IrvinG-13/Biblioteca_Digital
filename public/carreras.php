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
</head>
<body>

    <h2>Gestión de Carreras</h2>
    <?php include __DIR__ . '/menu.php'; ?>
    <?php if ($exito === "1"): ?>
        <p style="color:green;">Operación realizada con éxito.</p>
    <?php elseif ($error === "tieneestudiantes"): ?>
        <p style="color:red;">No se puede eliminar: hay estudiantes asignados a esta carrera.</p>
    <?php endif; ?>

    <!-- Buscador -->
    <form action="carreras.php" method="GET">
        <input type="text" name="busqueda" placeholder="Buscar carrera..."
               value="<?php echo htmlspecialchars($datos["busqueda"]); ?>">
        <button type="submit">Buscar</button>
        <a href="carreras.php">Limpiar</a>
    </form>

    <br>

    <a href="carrera_form.php">+ Nueva Carrera</a>

    <br><br>

    <!-- Tabla de carreras -->
    <table border="1">
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
                            <a href="carrera_form.php?id=<?php echo $c["id"]; ?>">Editar</a>

                            <form action="carrera_eliminar.php" method="POST" style="display:inline;"
                                  onsubmit="return confirm('¿Seguro que deseas eliminar esta carrera?');">
                                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                <input type="hidden" name="id" value="<?php echo $c["id"]; ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <br>

    <!-- Paginación -->
    <?php for ($i = 1; $i <= $datos["totalPaginas"]; $i++): ?>
        <?php if ($i === $datos["paginaActual"]): ?>
            <strong><?php echo $i; ?></strong>
        <?php else: ?>
            <a href="carreras.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($datos["busqueda"]); ?>">
                <?php echo $i; ?>
            </a>
        <?php endif; ?>
        &nbsp;
    <?php endfor; ?>

    <br><br>
    <a href="dashboard.php">Volver al panel</a>

</body>
</html>