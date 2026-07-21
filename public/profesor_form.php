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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo $esEdicion
            ? "Editar profesor | ReadPoint"
            : "Nuevo profesor | ReadPoint"; ?>
    </title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/profesor-form.css?v=1">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-formulario-profesor">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>
                    <?php echo $esEdicion
                        ? "Editar profesor"
                        : "Nuevo profesor"; ?>
                </h1>

                <p>
                    <?php echo $esEdicion
                        ? "Actualiza la información académica y personal del profesor."
                        : "Registra un nuevo profesor dentro de ReadPoint."; ?>
                </p>
            </div>

            <a
                class="boton-volver-profesores"
                href="profesores.php"
            >
                Volver a profesores
            </a>

        </section>

        <?php if ($error === "cedula"): ?>

            <div class="alert alert-error">
                Cédula inválida. Debe tener entre 5 y 20 caracteres.
            </div>

        <?php elseif ($error === "ceduladuplicada"): ?>

            <div class="alert alert-error">
                Esa cédula ya está registrada para otro profesor.
            </div>

        <?php elseif ($error === "nombres"): ?>

            <div class="alert alert-error">
                Primer nombre y primer apellido son obligatorios.
            </div>

        <?php elseif ($error === "materia"): ?>

            <div class="alert alert-error">
                Selecciona una materia válida.
            </div>

        <?php endif; ?>

        <section class="panel-formulario-profesor">

            <form
                class="formulario-profesor"
                action="profesor_procesar.php"
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
                            $profesorActual["id"],
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>"
                    >

                <?php endif; ?>

                <div class="grupo-campo-profesor campo-completo">

                    <label for="cedula">
                        Cédula
                    </label>

                    <input
                        id="cedula"
                        type="text"
                        name="cedula"
                        required
                        minlength="5"
                        maxlength="20"
                        placeholder="Ej. 8-123-456"
                        value="<?php echo $esEdicion
                            ? htmlspecialchars(
                                $profesorActual["cedula"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                            : ""; ?>"
                    >

                    <small>
                        Debe tener entre 5 y 20 caracteres.
                    </small>

                </div>

                <div class="fila-formulario-profesor">

                    <div class="grupo-campo-profesor">

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
                                    $profesorActual["primer_nombre"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                : ""; ?>"
                        >

                    </div>

                    <div class="grupo-campo-profesor">

                        <label for="segundo_nombre">
                            Segundo nombre
                        </label>

                        <input
                            id="segundo_nombre"
                            type="text"
                            name="segundo_nombre"
                            value="<?php echo $esEdicion
                                ? htmlspecialchars(
                                    $profesorActual["segundo_nombre"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                : ""; ?>"
                        >

                    </div>

                </div>

                <div class="fila-formulario-profesor">

                    <div class="grupo-campo-profesor">

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
                                    $profesorActual["primer_apellido"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                : ""; ?>"
                        >

                    </div>

                    <div class="grupo-campo-profesor">

                        <label for="segundo_apellido">
                            Segundo apellido
                        </label>

                        <input
                            id="segundo_apellido"
                            type="text"
                            name="segundo_apellido"
                            value="<?php echo $esEdicion
                                ? htmlspecialchars(
                                    $profesorActual["segundo_apellido"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                : ""; ?>"
                        >

                    </div>

                </div>

                <div class="fila-formulario-profesor">

                    <div class="grupo-campo-profesor">

                        <label for="materia_id">
                            Materia
                        </label>

                        <select
                            id="materia_id"
                            name="materia_id"
                            required
                        >

                            <option value="">
                                Selecciona una materia
                            </option>

                            <?php foreach ($datos["materias"] as $m): ?>

                                <option
                                    value="<?php echo htmlspecialchars(
                                        $m["id"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>"
                                    <?php echo (
                                        $esEdicion &&
                                        (int)$profesorActual["materia_id"]
                                        === (int)$m["id"]
                                    )
                                        ? "selected"
                                        : ""; ?>
                                >
                                    <?php echo htmlspecialchars(
                                        $m["nombre"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="grupo-campo-profesor">

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
                                            $profesorActual["usuario_id"] ?? 0
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
                            Solo aparecen cuentas disponibles con rol de profesor.
                        </small>

                    </div>

                </div>

                <div class="acciones-formulario-profesor">

                    <a
                        class="boton-cancelar-profesor"
                        href="profesores.php"
                    >
                        Cancelar
                    </a>

                    <button
                        class="boton-guardar-profesor"
                        type="submit"
                    >
                        <?php echo $esEdicion
                            ? "Guardar cambios"
                            : "Crear profesor"; ?>
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>