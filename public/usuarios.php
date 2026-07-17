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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">
        <div class="content-card">

            <div class="page-header">
                <h2>Gestión de Usuarios</h2>
                <a class="btn btn-primary" href="usuario_form.php">+ Nuevo Usuario</a>
            </div>

            <?php if ($exito === "1"): ?>
                <div class="alert alert-success">Operación realizada con éxito.</div>
            <?php elseif ($error === "automodificacion"): ?>
                <div class="alert alert-error">No puedes bloquear ni eliminar tu propio usuario.</div>
            <?php endif; ?>

            <form class="actions-bar" action="usuarios.php" method="GET">
                <input class="search-input" type="text" name="busqueda" placeholder="Buscar usuario..."
                       value="<?php echo htmlspecialchars($datos["busqueda"]); ?>">

                <button class="btn btn-secondary" type="submit">Buscar</button>
                <a class="btn btn-secondary" href="usuarios.php">Limpiar</a>
            </form>

            <table class="table">
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

                                <td>
                                    <?php if ((int)$u["bloqueado"] === 1): ?>
                                        <span class="badge badge-red">Bloqueado</span>
                                    <?php else: ?>
                                        <span class="badge badge-green">Activo</span>
                                    <?php endif; ?>
                                </td>

                                <td><?php echo htmlspecialchars($u["intentos_fallidos"]); ?></td>

                                <td>
                                    <a class="btn btn-link" href="usuario_form.php?id=<?php echo $u["id"]; ?>">Editar</a>

                                    <form action="usuario_estado.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="id" value="<?php echo $u["id"]; ?>">

                                        <?php if ((int)$u["bloqueado"] === 1): ?>
                                            <input type="hidden" name="bloqueado" value="0">
                                            <button class="btn btn-link" type="submit">Reactivar</button>
                                        <?php else: ?>
                                            <input type="hidden" name="bloqueado" value="1">
                                            <button class="btn btn-link" type="submit">Bloquear</button>
                                        <?php endif; ?>
                                    </form>

                                    <form action="usuario_eliminar.php" method="POST" style="display:inline;"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este usuario? Esta acción no se puede deshacer.');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                                        <input type="hidden" name="id" value="<?php echo $u["id"]; ?>">
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
                        <a href="usuarios.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($datos["busqueda"]); ?>">
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