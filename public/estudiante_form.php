x<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

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
</head>
<body>

    <h2><?php echo $esEdicion ? "Editar Estudiante" : "Nuevo Estudiante"; ?></h2>

    <?php if ($error === "cip"): ?>
        <p style="color:red;">CIP inválido. Debe tener entre 5 y 20 caracteres (letras, números y guiones).</p>
    <?php elseif ($error === "cipduplicado"): ?>
        <p style="color:red;">Ese CIP ya está registrado para otro estudiante.</p>
    <?php elseif ($error === "nombres"): ?>
        <p style="color:red;">Primer nombre y primer apellido son obligatorios.</p>
    <?php elseif ($error === "fecha"): ?>
        <p style="color:red;">Fecha de nacimiento inválida (formato AAAA-MM-DD, edad mínima 15 años).</p>
    <?php elseif ($error === "carrera"): ?>
        <p style="color:red;">Selecciona una carrera válida.</p>
    <?php endif; ?>

    <form action="estudiante_procesar.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

        <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?php echo $estudianteActual["id"]; ?>">
        <?php endif; ?>

        <label>CIP:</label><br>
        <input type="text" name="cip" required
               value="<?php echo $esEdicion ? htmlspecialchars($estudianteActual["cip"]) : ""; ?>"><br><br>

        <label>Primer Nombre:</label><br>
        <input type="text" name="primer_nombre" required
               value="<?php echo $esEdicion ? htmlspecialchars($estudianteActual["primer_nombre"]) : ""; ?>"><br><br>

        <label>Segundo Nombre (opcional):</label><br>
        <input type="text" name="segundo_nombre"
               value="<?php echo $esEdicion ? htmlspecialchars($estudianteActual["segundo_nombre"] ?? "") : ""; ?>"><br><br>

        <label>Primer Apellido:</label><br>
        <input type="text" name="primer_apellido" required
               value="<?php echo $esEdicion ? htmlspecialchars($estudianteActual["primer_apellido"]) : ""; ?>"><br><br>

        <label>Segundo Apellido (opcional):</label><br>
        <input type="text" name="segundo_apellido"
               value="<?php echo $esEdicion ? htmlspecialchars($estudianteActual["segundo_apellido"] ?? "") : ""; ?>"><br><br>

        <label>Fecha de Nacimiento:</label><br>
        <input type="date" name="fecha_nacimiento" required
               value="<?php echo $esEdicion ? htmlspecialchars($estudianteActual["fecha_nacimiento"]) : ""; ?>"><br><br>

        <label>Carrera:</label><br>
        <select name="carrera_id" required>
            <option value="">-- Selecciona una carrera --</option>
            <?php foreach ($datos["carreras"] as $c): ?>
                <option value="<?php echo $c["id"]; ?>"
                    <?php echo ($esEdicion && (int)$estudianteActual["carrera_id"] === (int)$c["id"]) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($c["nombre"]); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Cuenta de acceso vinculada (opcional):</label><br>
        <select name="usuario_id">
            <option value="">-- Sin cuenta vinculada --</option>
            <?php foreach ($datos["usuariosDisponibles"] as $u): ?>
                <option value="<?php echo $u["id"]; ?>"
                    <?php echo ($esEdicion && (int)($estudianteActual["usuario_id"] ?? 0) === (int)$u["id"]) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($u["usuario"]); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit"><?php echo $esEdicion ? "Guardar cambios" : "Crear estudiante"; ?></button>
    </form>

    <br>
    <a href="estudiantes.php">Cancelar / Volver</a>

</body>
</html>