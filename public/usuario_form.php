<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Controllers/UsuarioController.php';
require_once __DIR__ . '/../app/Models/UsuarioModel.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$token = Csrf::generarToken();
$error = $_GET["error"] ?? "";
$id = $_GET["id"] ?? null;

$usuarioActual = null;
if ($id !== null) {
    $modelo = new UsuarioModel();
    $usuarioActual = $modelo->obtenerPorId((int) $id);

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
    <title><?php echo $esEdicion ? "Editar" : "Nuevo"; ?> Usuario - Biblioteca Digital</title>
</head>
<body>

    <h2><?php echo $esEdicion ? "Editar Usuario" : "Nuevo Usuario"; ?></h2>

    <?php if ($error === "usuario"): ?>
        <p style="color:red;">El nombre de usuario debe tener entre 3 y 50 caracteres.</p>
    <?php elseif ($error === "password"): ?>
        <p style="color:red;">La contraseña debe tener entre 8 y 12 caracteres.</p>
    <?php elseif ($error === "rol"): ?>
        <p style="color:red;">Rol inválido.</p>
    <?php elseif ($error === "duplicado"): ?>
        <p style="color:red;">Ese nombre de usuario ya existe.</p>
    <?php endif; ?>

    <form action="usuario_procesar.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

        <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?php echo $usuarioActual["id"]; ?>">
        <?php endif; ?>

        <label>Usuario:</label><br>
        <input type="text" name="usuario" required
               value="<?php echo $esEdicion ? htmlspecialchars($usuarioActual["usuario"]) : ""; ?>"><br><br>

        <label>Contraseña<?php echo $esEdicion ? " (dejar en blanco para no cambiarla)" : ""; ?>:</label><br>
        <input type="password" name="password" <?php echo $esEdicion ? "" : "required"; ?>><br><br>

        <label>Rol:</label><br>
        <select name="rol" required>
            <option value="admin" <?php echo ($esEdicion && $usuarioActual["rol"] === "admin") ? "selected" : ""; ?>>Administrador</option>
            <option value="estudiante" <?php echo ($esEdicion && $usuarioActual["rol"] === "estudiante") ? "selected" : ""; ?>>Estudiante</option>
        </select><br><br>

        <button type="submit"><?php echo $esEdicion ? "Guardar cambios" : "Crear usuario"; ?></button>
    </form>

    <br>
    <a href="usuarios.php">Cancelar / Volver</a>

</body>
</html>