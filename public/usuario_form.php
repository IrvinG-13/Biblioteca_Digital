<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/UsuarioController.php';
require_once __DIR__ . '/../app/Models/UsuarioModel.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$token = Csrf::generarToken();
$error = $_GET["error"] ?? "";
$id = $_GET["id"] ?? null;

$usuarioActual = null;

if ($id !== null) {

    $modelo = new UsuarioModel();

    $usuarioActual = $modelo->obtenerPorId((int)$id);

    if ($usuarioActual === null) {
        header("Location: usuarios.php");
        exit;
    }

}

$esEdicion = $usuarioActual !== null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>
        <?php echo $esEdicion ? "Editar Usuario" : "Nuevo Usuario"; ?>
    </title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">
        <div class="content-card">

            <form class="form-card" action="usuario_procesar.php" method="POST">

                <div class="page-header">
                    <h2><?php echo $esEdicion ? "Editar Usuario" : "Nuevo Usuario"; ?></h2>
                </div>

                <?php if ($error === "usuario"): ?>
                    <div class="alert alert-error">El nombre de usuario debe tener entre 3 y 50 caracteres.</div>
                <?php elseif ($error === "password"): ?>
                    <div class="alert alert-error">La contraseña debe tener entre 8 y 12 caracteres.</div>
                <?php elseif ($error === "rol"): ?>
                    <div class="alert alert-error">Rol inválido.</div>
                <?php elseif ($error === "duplicado"): ?>
                    <div class="alert alert-error">Ese nombre de usuario ya existe.</div>
                <?php endif; ?>

                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

                <?php if ($esEdicion): ?>
                    <input type="hidden" name="id" value="<?php echo $usuarioActual["id"]; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" name="usuario" required
                           placeholder="Ej. juan.perez"
                           value="<?php echo $esEdicion ? htmlspecialchars($usuarioActual["usuario"]) : ""; ?>">
                </div>

                <div class="form-group">
                    <label>
                        Contraseña
                        <?php echo $esEdicion ? "(dejar en blanco para no cambiarla)" : ""; ?>
                    </label>
                    <input type="password" name="password"
                           placeholder="Debe tener entre 8 y 12 caracteres"
                           <?php echo $esEdicion ? "" : "required"; ?>>
                </div>

                <div class="form-group">
                    <label>Rol</label>
                    <select name="rol" required>
                        <option value="">Seleccione un rol</option>
                        <option value="admin" <?php echo ($esEdicion && $usuarioActual["rol"] === "admin") ? "selected" : ""; ?>>
                            Administrador
                        </option>
                        <option value="estudiante" <?php echo ($esEdicion && $usuarioActual["rol"] === "estudiante") ? "selected" : ""; ?>>
                            Estudiante
                        </option>
                    </select>
                </div>

                <div class="form-actions">
                    <a class="btn btn-secondary" href="usuarios.php">Cancelar</a>
                    <button class="btn btn-primary" type="submit">
                        <?php echo $esEdicion ? "Guardar cambios" : "Crear usuario"; ?>
                    </button>
                </div>

            </form>

        </div>
    </main>

</div>

</body>
</html>