<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Models/CarreraModel.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$token = Csrf::generarToken();
$error = $_GET["error"] ?? "";
$id = $_GET["id"] ?? null;

$carreraActual = null;
if ($id !== null) {
    $modelo = new CarreraModel();
    $carreraActual = $modelo->obtenerPorId((int) $id);

    if ($carreraActual === null) {
        header("Location: carreras.php");
        exit;
    }
}

$esEdicion = $carreraActual !== null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $esEdicion ? "Editar" : "Nueva"; ?> Carrera - Biblioteca Digital</title>
</head>
<body>

    <h2><?php echo $esEdicion ? "Editar Carrera" : "Nueva Carrera"; ?></h2>

    <?php if ($error === "nombre"): ?>
        <p style="color:red;">El nombre debe tener entre 3 y 100 caracteres.</p>
    <?php elseif ($error === "duplicado"): ?>
        <p style="color:red;">Esa carrera ya existe.</p>
    <?php endif; ?>

    <form action="carrera_procesar.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

        <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?php echo $carreraActual["id"]; ?>">
        <?php endif; ?>

        <label>Nombre de la carrera:</label><br>
        <input type="text" name="nombre" required
               value="<?php echo $esEdicion ? htmlspecialchars($carreraActual["nombre"]) : ""; ?>"><br><br>

        <button type="submit"><?php echo $esEdicion ? "Guardar cambios" : "Crear carrera"; ?></button>
    </form>

    <br>
    <a href="carreras.php">Cancelar / Volver</a>

</body>
</html>