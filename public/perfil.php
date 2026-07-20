<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Core/Csrf.php';

$token = Csrf::generarToken();
$exito = $_GET["exito"] ?? "";
$error = $_GET["error"] ?? "";
$forzado = (int)($_SESSION["cambio_password"] ?? 0) === 1 ? "1" : "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi cuenta - Biblioteca Digital</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php if ($forzado !== "1"): ?>
        <?php include __DIR__ . '/menu.php'; ?>
    <?php endif; ?>
    <main class="main-content">
        <div class="content-card">
            <div class="page-header">
                <h2>Mi cuenta</h2>
            </div>

            <?php if ($forzado === "1"): ?>
                <div class="alert alert-error">
                    Tu contraseña fue asignada por el administrador. Debes crear una nueva antes de continuar.
                </div>
            <?php endif; ?>

            <?php if ($exito === "1"): ?>
                <div class="alert alert-success">
                    Tu contraseña fue actualizada correctamente.
                    <?php if ($forzado === "1"): ?>
                        Ya puedes continuar usando el sistema.
                    <?php endif; ?>
                </div>
            <?php elseif ($error === "actual"): ?>
                <div class="alert alert-error">La contraseña actual no es correcta.</div>
            <?php elseif ($error === "formato"): ?>
                <div class="alert alert-error">La nueva contraseña debe tener entre 8 y 12 caracteres.</div>
            <?php elseif ($error === "coincidencia"): ?>
                <div class="alert alert-error">La nueva contraseña y su confirmación no coinciden.</div>
            <?php elseif ($error === "igual"): ?>
                <div class="alert alert-error">La nueva contraseña no puede ser igual a la actual.</div>
            <?php elseif ($error === "guardar"): ?>
                <div class="alert alert-error">No fue posible actualizar la contraseña. Inténtalo nuevamente.</div>
            <?php endif; ?>

            <?php if ($exito !== "1" || $forzado === "1"): ?>
                <form class="form-card" action="perfil_procesar.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

                    <div class="form-group">
                        <label>Contraseña actual</label>
                        <input type="password" name="password_actual" required>
                    </div>

                    <div class="form-group">
                        <label>Nueva contraseña</label>
                        <input type="password" name="password_nueva" required>
                        <small>Debe tener entre 8 y 12 caracteres.</small>
                    </div>

                    <div class="form-group">
                        <label>Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmar" required>
                    </div>

                    <div class="form-actions">
                        <?php if ($forzado !== "1"): ?>
                            <a class="btn btn-secondary" href="<?php echo $_SESSION["rol"] === "admin" ? "dashboard.php" : "catalogo.php"; ?>">Cancelar</a>
                        <?php endif; ?>
                        <button class="btn btn-primary" type="submit">Guardar nueva contraseña</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>