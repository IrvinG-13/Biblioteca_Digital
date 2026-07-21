<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Models/CarreraModel.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$token = Csrf::generarToken();
$error = $_GET["error"] ?? "";
$id = $_GET["id"] ?? null;

$carreraActual = null;

if ($id !== null) {
    $modelo = new CarreraModel();
    $carreraActual = $modelo->obtenerPorId((int)$id);

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo $esEdicion
            ? "Editar carrera | ReadPoint"
            : "Nueva carrera | ReadPoint"; ?>
    </title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/carrera-form.css?v=1">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-formulario-carrera">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>
                    <?php echo $esEdicion
                        ? "Editar carrera"
                        : "Nueva carrera"; ?>
                </h1>

                <p>
                    <?php echo $esEdicion
                        ? "Modifica la información de la carrera seleccionada."
                        : "Registra una nueva carrera académica en ReadPoint."; ?>
                </p>
            </div>

            <a
                class="boton-volver-carreras"
                href="carreras.php"
            >
                Volver a carreras
            </a>

        </section>

        <?php if ($error === "nombre"): ?>

            <div class="alert alert-error">
                El nombre debe tener entre 3 y 100 caracteres.
            </div>

        <?php elseif ($error === "duplicado"): ?>

            <div class="alert alert-error">
                Esa carrera ya existe.
            </div>

        <?php endif; ?>

        <section class="panel-formulario-carrera">

            <form
                class="formulario-carrera"
                action="carrera_procesar.php"
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
                            $carreraActual["id"],
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>"
                    >

                <?php endif; ?>

                <div class="grupo-campo-carrera">

                    <label for="nombre">
                        Nombre de la carrera
                    </label>

                    <input
                        id="nombre"
                        type="text"
                        name="nombre"
                        required
                        minlength="3"
                        maxlength="100"
                        autocomplete="off"
                        placeholder="Ej. Licenciatura en Desarrollo de Software"
                        value="<?php echo $esEdicion
                            ? htmlspecialchars(
                                $carreraActual["nombre"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                            : ""; ?>"
                    >

                    <small>
                        Debe tener entre 3 y 100 caracteres.
                    </small>

                </div>

                <div class="acciones-formulario-carrera">

                    <a
                        class="boton-cancelar-carrera"
                        href="carreras.php"
                    >
                        Cancelar
                    </a>

                    <button
                        class="boton-guardar-carrera"
                        type="submit"
                    >
                        <?php echo $esEdicion
                            ? "Guardar cambios"
                            : "Crear carrera"; ?>
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>