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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo $esEdicion
            ? "Editar materia | ReadPoint"
            : "Nueva materia | ReadPoint"; ?>
    </title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/materia-form.css?v=1">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-formulario-materia">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>
                    <?php echo $esEdicion
                        ? "Editar materia"
                        : "Nueva materia"; ?>
                </h1>

                <p>
                    <?php echo $esEdicion
                        ? "Modifica el nombre de la materia seleccionada."
                        : "Registra una nueva materia en ReadPoint."; ?>
                </p>
            </div>

            <a
                class="boton-volver-materias"
                href="materias.php"
            >
                Volver a materias
            </a>

        </section>

        <?php if ($error === "nombre"): ?>

            <div class="alert alert-error">
                El nombre debe tener entre 3 y 100 caracteres.
            </div>

        <?php elseif ($error === "duplicado"): ?>

            <div class="alert alert-error">
                Esa materia ya existe.
            </div>

        <?php endif; ?>

        <section class="panel-formulario-materia">

            <form
                class="formulario-materia"
                action="materia_procesar.php"
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
                            $materiaActual["id"],
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>"
                    >

                <?php endif; ?>

                <div class="grupo-campo-materia">

                    <label for="nombre">
                        Nombre de la materia
                    </label>

                    <input
                        id="nombre"
                        type="text"
                        name="nombre"
                        required
                        minlength="3"
                        maxlength="100"
                        placeholder="Ej. Programación orientada a objetos"
                        value="<?php echo $esEdicion
                            ? htmlspecialchars(
                                $materiaActual["nombre"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                            : ""; ?>"
                    >

                    <small>
                        Debe tener entre 3 y 100 caracteres.
                    </small>

                </div>

                <div class="acciones-formulario-materia">

                    <a
                        class="boton-cancelar-materia"
                        href="materias.php"
                    >
                        Cancelar
                    </a>

                    <button
                        class="boton-guardar-materia"
                        type="submit"
                    >
                        <?php echo $esEdicion
                            ? "Guardar cambios"
                            : "Crear materia"; ?>
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>