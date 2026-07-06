<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();
require_once __DIR__ . '/../app/Controllers/UsuarioController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$controller = new UsuarioController();
$datos = $controller->listar();

$token = Csrf::generarToken();
$exito = $_GET["exito"] ?? "";
$error = $_GET["error"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - Biblioteca Digital</title>
</head>
<body>

    <h2>Gestión de Usuarios</h2>
    <?php include __DIR__ . '/menu.php'; ?>
    <?php if ($exito === "1"): ?>
        <p style="color:green;">Operación realizada con éxito.</p>
    <?php elseif ($error === "automodificacion"): ?>
        <p style="color:red;">No puedes bloquear ni eliminar tu propio usuario.</p>
    <?php endif; ?>

    <!-- Buscador -->
    <form action="usuarios.php" method="GET">
        <input type="text" name="busqueda" placeholder="Buscar usuario..."
               value="<?php echo htmlspecialchars($datos["busqueda"]); ?>">
        <button type="submit">Buscar</button>
        <a href="usuarios.php">Limpiar</a>
    </form>

    <br>

    <a href="usuario_form.php">+ Nuevo Usuario</a>

    <br><br>

    <!-- Tabla de usuarios -->
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Intentos Fallidos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($datos["usuarios"])): ?>
                <tr>
                    <td colspan="6">No se encontraron usuarios.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($datos["usuarios"] as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u["id"]); ?></td>
                        <td><?php echo htmlspecialchars($u["usuario"]); ?></td>
                        <td><?php echo htmlspecialchars($u["rol"]); ?></td>
                        <td><?php echo ((int)$u["bloqueado"] === 1) ? "Bloqueado" : "Activo"; ?></td>
                        <td><?php echo htmlspecialchars($u["intentos_fallidos"]); ?></td>
                        <td>
                            <a href="usuario_form.php?id=<?php echo $u["id"]; ?>">Editar</a>

                            <!-- Bloquear / Reactivar -->
                            <form action="usuario_estado.php" method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                <input type="hidden" name="id" value="<?php echo $u["id"]; ?>">
                                <?php if ((int)$u["bloqueado"] === 1): ?>
                                    <input type="hidden" name="bloqueado" value="0">
                                    <button type="submit">Reactivar</button>
                                <?php else: ?>
                                    <input type="hidden" name="bloqueado" value="1">
                                    <button type="submit">Bloquear</button>
                                <?php endif; ?>
                            </form>

                            <!-- Eliminar -->
                            <form action="usuario_eliminar.php" method="POST" style="display:inline;"
                                  onsubmit="return confirm('¿Seguro que deseas eliminar este usuario? Esta acción no se puede deshacer.');">
                                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                <input type="hidden" name="id" value="<?php echo $u["id"]; ?>">
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
            <a href="usuarios.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($datos["busqueda"]); ?>">
                <?php echo $i; ?>
            </a>
        <?php endif; ?>
        &nbsp;
    <?php endfor; ?>

    <br><br>
    <a href="dashboard.php">Volver al panel</a>

</body>
</html>