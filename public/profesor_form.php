<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/ProfesorController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$token = Csrf::generarToken();
$error = $_GET["error"] ?? "";
$id = !empty($_GET["id"]) ? (int) $_GET["id"] : null;

$controller = new ProfesorController();
$datos = $controller->datosFormulario($id);

if ($id !== null && $datos["profesor"] === null) {
    header("Location: profesores.php");
    exit;
}

$profesorActual = $datos["profesor"];
$esEdicion = $profesorActual !== null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $esEdicion ? "Editar" : "Nuevo"; ?> Profesor - Biblioteca Digital</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/menu.php'; ?>
    <main class="main-content">
        <div class="content-card">
            <form class="form-card" action="profesor_procesar.php" method="POST">
                <div class="page-header">
                    <h2><?php echo $esEdicion ? "Editar Profesor" : "Nuevo Profesor"; ?></h2>
                </div>

                <?php if ($error === "cedula"): ?>
                    <div class="alert alert-error">Cédula inválida. Debe tener entre 5 y 20 caracteres.</div>
                <?php elseif ($error === "ceduladuplicada"): ?>
                    <div class="alert alert-error">Esa cédula ya está registrada para otro profesor.</div>
                <?php elseif ($error === "nombres"): ?>
                    <div class="alert alert-error">Primer nombre y primer apellido son obligatorios.</div>
                <?php elseif ($error === "materia"): ?>
                    <div class="alert alert-error">Selecciona una materia válida.</div>
                <?php endif; ?>

                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                <?php if ($esEdicion): ?>
                    <input type="hidden" name="id" value="<?php echo $profesorActual["id"]; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Cédula</label>
                    <input type="text" name="cedula" required
                           value="<?php echo $esEdicion ? htmlspecialchars($profesorActual["cedula"]) : ""; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Primer Nombre</label>
                        <input type="text" name="primer_nombre" required
                               value="<?php echo $esEdicion ? htmlspecialchars($profesorActual["primer_nombre"]) : ""; ?>">
                    </div>
                    <div class="form-group">
                        <label>Segundo Nombre</label>
                        <input type="text" name="segundo_nombre"
                               value="<?php echo $esEdicion ? htmlspecialchars($profesorActual["segundo_nombre"] ?? "") : ""; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Primer Apellido</label>
                        <input type="text" name="primer_apellido" required
                               value="<?php echo $esEdicion ? htmlspecialchars($profesorActual["primer_apellido"]) : ""; ?>">
                    </div>
                    <div class="form-group">
                        <label>Segundo Apellido</label>
                        <input type="text" name="segundo_apellido"
                               value="<?php echo $esEdicion ? htmlspecialchars($profesorActual["segundo_apellido"] ?? "") : ""; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Materia</label>
                    <select name="materia_id" required>
                        <option value="">Selecciona una materia</option>
                        <?php foreach ($datos["materias"] as $m): ?>
                            <option value="<?php echo $m["id"]; ?>"
                                <?php echo ($esEdicion && (int)$profesorActual["materia_id"] === (int)$m["id"]) ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($m["nombre"]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cuenta de acceso vinculada (opcional)</label>
                    <select name="usuario_id">
                        <option value="">Sin cuenta vinculada</option>
                        <?php foreach ($datos["usuariosDisponibles"] as $u): ?>
                            <option value="<?php echo $u["id"]; ?>"
                                <?php echo ($esEdicion && (int)($profesorActual["usuario_id"] ?? 0) === (int)$u["id"]) ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($u["usuario"]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <a class="btn btn-secondary" href="profesores.php">Cancelar</a>
                    <button class="btn btn-primary" type="submit">
                        <?php echo $esEdicion ? "Guardar cambios" : "Crear profesor"; ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>