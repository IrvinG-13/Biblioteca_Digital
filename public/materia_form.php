<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Models/MateriaModel.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$token = Csrf::generarToken();
$error = $_GET["error"] ?? "";
$id = $_GET["id"] ?? null;

$materiaActual = null;
if ($id !== null) {
    $modelo = new MateriaModel();
    $materiaActual = $modelo->obtenerPorId((int) $id);

    if ($materiaActual === null) {
        header("Location: materias.php");
        exit;
    }
}

$esEdicion = $materiaActual !== null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $esEdicion ? "Editar" : "Nueva"; ?> Materia - Biblioteca Digital</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/menu.php'; ?>
    <main class="main-content">
        <div class="content-card">
            <form class="form-card" action="materia_procesar.php" method="POST">
                <div class="page-header">
                    <h2><?php echo $esEdicion ? "Editar Materia" : "Nueva Materia"; ?></h2>
                </div>

                <?php if ($error === "nombre"): ?>
                    <div class="alert alert-error">El nombre debe tener entre 3 y 100 caracteres.</div>
                <?php elseif ($error === "duplicado"): ?>
                    <div class="alert alert-error">Esa materia ya existe.</div>
                <?php endif; ?>

                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                <?php if ($esEdicion): ?>
                    <input type="hidden" name="id" value="<?php echo $materiaActual["id"]; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Nombre de la materia</label>
                    <input type="text" name="nombre" required
                           value="<?php echo $esEdicion ? htmlspecialchars($materiaActual["nombre"]) : ""; ?>">
                </div>

                <div class="form-actions">
                    <a class="btn btn-secondary" href="materias.php">Cancelar</a>
                    <button class="btn btn-primary" type="submit">
                        <?php echo $esEdicion ? "Guardar cambios" : "Crear materia"; ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>