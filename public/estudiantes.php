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
</head>
<body>

    <h2>Gestión de Estudiantes</h2>
    <?php include __DIR__ . '/menu.php'; ?>
    <?php if ($exito === "1"): ?>
        <p style="color:green;">Operación realizada con éxito.</p>
    <?php elseif ($error === "tienereservas"): ?>
        <p style="color:red;">No se puede eliminar: este estudiante tiene reservas registradas.</p>
    <?php endif; ?>

    <!-- Buscador -->
    <form action="estudiantes.php" method="GET">
        <input type="text" name="busqueda" placeholder="Buscar por CIP, nombre o apellido..."
               value="<?php echo htmlspecialchars($datos["busqueda"]); ?>">
        <button type="submit">Buscar</button>
        <a href="estudiantes.php">Limpiar</a>
    </form>

    <br>

    <a href="estudiante_form.php">+ Nuevo Estudiante</a>

    <br><br>

    <!-- Tabla de estudiantes -->
    <table border="1">
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
                        <td><?php echo $e["usuario_id"] ? "Sí" : "No"; ?></td>
                        <td>
                            <a href="estudiante_form.php?id=<?php echo $e["id"]; ?>">Editar</a>

                            <form action="estudiante_eliminar.php" method="POST" style="display:inline;"
                                  onsubmit="return confirm('¿Seguro que deseas eliminar este estudiante?');">
                                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                <input type="hidden" name="id" value="<?php echo $e["id"]; ?>">
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
            <a href="estudiantes.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($datos["busqueda"]); ?>">
                <?php echo $i; ?>
            </a>
        <?php endif; ?>
        &nbsp;
    <?php endfor; ?>

    <br><br>
    <a href="dashboard.php">Volver al panel</a>

</body>
</html>