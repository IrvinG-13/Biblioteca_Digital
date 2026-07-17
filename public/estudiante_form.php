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

$token = Csrf::generarToken();
$error = $_GET["error"] ?? "";
$id = !empty($_GET["id"]) ? (int) $_GET["id"] : null;

$controller = new EstudianteController();
$datos = $controller->datosFormulario($id);

if ($id !== null && $datos["estudiante"] === null) {
    header("Location: estudiantes.php");
    exit;
}

$estudianteActual = $datos["estudiante"];
$esEdicion = $estudianteActual !== null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $esEdicion ? "Editar" : "Nuevo"; ?> Estudiante - Biblioteca Digital</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">
        <div class="content-card">

            <form class="form-card" action="estudiante_procesar.php" method="POST">

                <div class="page-header">
                    <h2><?php echo $esEdicion ? "Editar Estudiante" : "Nuevo Estudiante"; ?></h2>
                </div>

                <?php if ($error === "cip"): ?>
                    <div class="alert alert-error">CIP inválido. Debe tener entre 5 y 20 caracteres.</div>
                <?php elseif ($error === "cipduplicado"): ?>
                    <div class="alert alert-error">Ese CIP ya está registrado para otro estudiante.</div>
                <?php elseif ($error === "nombres"): ?>
                    <div class="alert alert-error">Primer nombre y primer apellido son obligatorios.</div>
                <?php elseif ($error === "fecha"): ?>
                    <div class="alert alert-error">Fecha de nacimiento inválida. Edad mínima: 15 años.</div>
                <?php elseif ($error === "carrera"): ?>
                    <div class="alert alert-error">Selecciona una carrera válida.</div>
                <?php endif; ?>

                <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

                <?php if ($esEdicion): ?>
                    <input type="hidden" name="id" value="<?php echo $estudianteActual["id"]; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>CIP</label>
                    <input type="text" name="cip" required
                           value="<?php echo $esEdicion ? htmlspecialchars($estudianteActual["cip"]) : ""; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Primer Nombre</label>
                        <input type="text" name="primer_nombre" required
                               value="<?php echo $esEdicion ? htmlspecialchars($estudianteActual["primer_nombre"]) : ""; ?>">
                    </div>

                    <div class="form-group">
                        <label>Segundo Nombre</label>
                        <input type="text" name="segundo_nombre"
                               value="<?php echo $esEdicion ? htmlspecialchars($estudianteActual["segundo_nombre"] ?? "") : ""; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Primer Apellido</label>
                        <input type="text" name="primer_apellido" required
                               value="<?php echo $esEdicion ? htmlspecialchars($estudianteActual["primer_apellido"]) : ""; ?>">
                    </div>

                    <div class="form-group">
                        <label>Segundo Apellido</label>
                        <input type="text" name="segundo_apellido"
                               value="<?php echo $esEdicion ? htmlspecialchars($estudianteActual["segundo_apellido"] ?? "") : ""; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" required
                           value="<?php echo $esEdicion ? htmlspecialchars($estudianteActual["fecha_nacimiento"]) : ""; ?>">
                </div>

                <div class="form-group">
                    <label>Carrera</label>
                    <select name="carrera_id" required>
                        <option value="">Selecciona una carrera</option>
                        <?php foreach ($datos["carreras"] as $c): ?>
                            <option value="<?php echo $c["id"]; ?>"
                                <?php echo ($esEdicion && (int)$estudianteActual["carrera_id"] === (int)$c["id"]) ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($c["nombre"]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cuenta de acceso vinculada</label>
                    <select name="usuario_id">
                        <option value="">Sin cuenta vinculada</option>
                        <?php foreach ($datos["usuariosDisponibles"] as $u): ?>
                            <option value="<?php echo $u["id"]; ?>"
                                <?php echo ($esEdicion && (int)($estudianteActual["usuario_id"] ?? 0) === (int)$u["id"]) ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($u["usuario"]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <a class="btn btn-secondary" href="estudiantes.php">Cancelar</a>
                    <button class="btn btn-primary" type="submit">
                        <?php echo $esEdicion ? "Guardar cambios" : "Crear estudiante"; ?>
                    </button>
                </div>

            </form>

        </div>
    </main>

</div>

</body>
</html>