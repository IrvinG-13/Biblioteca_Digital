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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo $esEdicion
            ? "Editar estudiante | ReadPoint"
            : "Nuevo estudiante | ReadPoint"; ?>
    </title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/estudiante-form.css?v=1">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-formulario-estudiante">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>
                    <?php echo $esEdicion
                        ? "Editar estudiante"
                        : "Nuevo estudiante"; ?>
                </h1>

                <p>
                    <?php echo $esEdicion
                        ? "Actualiza la información académica y personal del estudiante."
                        : "Registra un nuevo estudiante dentro de ReadPoint."; ?>
                </p>
            </div>

            <a
                class="boton-volver-estudiantes"
                href="estudiantes.php"
            >
                Volver a estudiantes
            </a>

        </section>

        <?php if ($error === "cip"): ?>

            <div class="alert alert-error">
                CIP inválido. Debe tener entre 5 y 20 caracteres.
            </div>

        <?php elseif ($error === "cipduplicado"): ?>

            <div class="alert alert-error">
                Ese CIP ya está registrado para otro estudiante.
            </div>

        <?php elseif ($error === "nombres"): ?>

            <div class="alert alert-error">
                Primer nombre y primer apellido son obligatorios.
            </div>

        <?php elseif ($error === "fecha"): ?>

            <div class="alert alert-error">
                Fecha de nacimiento inválida. Edad mínima: 15 años.
            </div>

        <?php elseif ($error === "carrera"): ?>

            <div class="alert alert-error">
                Selecciona una carrera válida.
            </div>

        <?php endif; ?>

        <section class="panel-formulario-estudiante">

            <form
                class="formulario-estudiante"
                action="estudiante_procesar.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars(
                        $token,
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>"
                >

                <?php if ($esEdicion): ?>

                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo htmlspecialchars(
                            $estudianteActual["id"],
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>"
                    >

                <?php endif; ?>

                <div class="grupo-campo-estudiante campo-completo">

                    <label for="cip">
                        CIP
                    </label>

                    <input
                        id="cip"
                        type="text"
                        name="cip"
                        required
                        minlength="5"
                        maxlength="20"
                        placeholder="Ej. 8-123-456"
                        value="<?php echo $esEdicion
                            ? htmlspecialchars(
                                $estudianteActual["cip"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                            : ""; ?>"
                    >

                    <small>
                        Debe tener entre 5 y 20 caracteres.
                    </small>

                </div>

                <div class="fila-formulario-estudiante">

                    <div class="grupo-campo-estudiante">

                        <label for="primer_nombre">
                            Primer nombre
                        </label>

                        <input
                            id="primer_nombre"
                            type="text"
                            name="primer_nombre"
                            required
                            value="<?php echo $esEdicion
                                ? htmlspecialchars(
                                    $estudianteActual["primer_nombre"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                : ""; ?>"
                        >

                    </div>

                    <div class="grupo-campo-estudiante">

                        <label for="segundo_nombre">
                            Segundo nombre
                        </label>

                        <input
                            id="segundo_nombre"
                            type="text"
                            name="segundo_nombre"
                            value="<?php echo $esEdicion
                                ? htmlspecialchars(
                                    $estudianteActual["segundo_nombre"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                : ""; ?>"
                        >

                    </div>

                </div>

                <div class="fila-formulario-estudiante">

                    <div class="grupo-campo-estudiante">

                        <label for="primer_apellido">
                            Primer apellido
                        </label>

                        <input
                            id="primer_apellido"
                            type="text"
                            name="primer_apellido"
                            required
                            value="<?php echo $esEdicion
                                ? htmlspecialchars(
                                    $estudianteActual["primer_apellido"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                : ""; ?>"
                        >

                    </div>

                    <div class="grupo-campo-estudiante">

                        <label for="segundo_apellido">
                            Segundo apellido
                        </label>

                        <input
                            id="segundo_apellido"
                            type="text"
                            name="segundo_apellido"
                            value="<?php echo $esEdicion
                                ? htmlspecialchars(
                                    $estudianteActual["segundo_apellido"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                : ""; ?>"
                        >

                    </div>

                </div>

                <div class="fila-formulario-estudiante">

                    <div class="grupo-campo-estudiante">

                        <label for="fecha_nacimiento">
                            Fecha de nacimiento
                        </label>

                        <input
                            id="fecha_nacimiento"
                            type="date"
                            name="fecha_nacimiento"
                            required
                            value="<?php echo $esEdicion
                                ? htmlspecialchars(
                                    $estudianteActual["fecha_nacimiento"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                : ""; ?>"
                        >

                    </div>

                    <div class="grupo-campo-estudiante">

                        <label for="carrera_id">
                            Carrera
                        </label>

                        <select
                            id="carrera_id"
                            name="carrera_id"
                            required
                        >

                            <option value="">
                                Selecciona una carrera
                            </option>

                            <?php foreach ($datos["carreras"] as $c): ?>

                                <option
                                    value="<?php echo htmlspecialchars(
                                        $c["id"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>"
                                    <?php echo (
                                        $esEdicion &&
                                        (int)$estudianteActual["carrera_id"]
                                        === (int)$c["id"]
                                    )
                                        ? "selected"
                                        : ""; ?>
                                >
                                    <?php echo htmlspecialchars(
                                        $c["nombre"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

                <div class="grupo-campo-estudiante campo-completo">

                    <label for="usuario_id">
                        Cuenta de acceso vinculada
                    </label>

                    <select
                        id="usuario_id"
                        name="usuario_id"
                    >

                        <option value="">
                            Sin cuenta vinculada
                        </option>

                        <?php foreach ($datos["usuariosDisponibles"] as $u): ?>

                            <option
                                value="<?php echo htmlspecialchars(
                                    $u["id"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>"
                                <?php echo (
                                    $esEdicion &&
                                    (int)(
                                        $estudianteActual["usuario_id"] ?? 0
                                    ) === (int)$u["id"]
                                )
                                    ? "selected"
                                    : ""; ?>
                            >
                                <?php echo htmlspecialchars(
                                    $u["usuario"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <small>
                        Solo aparecen cuentas disponibles con rol de estudiante.
                    </small>

                </div>

                <div class="acciones-formulario-estudiante">

                    <a
                        class="boton-cancelar-estudiante"
                        href="estudiantes.php"
                    >
                        Cancelar
                    </a>

                    <button
                        class="boton-guardar-estudiante"
                        type="submit"
                    >
                        <?php echo $esEdicion
                            ? "Guardar cambios"
                            : "Crear estudiante"; ?>
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>