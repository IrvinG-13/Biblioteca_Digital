<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/CategoriaController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$token = Csrf::generarToken();

$error = $_GET["error"] ?? "";
$id = $_GET["id"] ?? null;

$categoriaActual = null;

if ($id !== null) {
    $controller = new CategoriaController();
    $categoriaActual = $controller->obtenerPorId((int)$id);

    if ($categoriaActual === null) {
        header("Location: categorias.php");
        exit;
    }
}

$esEdicion = $categoriaActual !== null;
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
            ? "Editar categoría | ReadPoint"
            : "Nueva categoría | ReadPoint"; ?>
    </title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/categoria-form.css?v=1">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-formulario-categoria">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>
                    <?php echo $esEdicion
                        ? "Editar categoría"
                        : "Nueva categoría"; ?>
                </h1>

                <p>
                    <?php echo $esEdicion
                        ? "Modifica el nombre de la categoría seleccionada."
                        : "Registra una nueva categoría para clasificar los libros."; ?>
                </p>
            </div>

            <a
                class="boton-volver-categorias"
                href="categorias.php"
            >
                Volver a categorías
            </a>

        </section>

        <?php if ($error === "nombre"): ?>

            <div class="alert alert-error">
                El nombre debe contener entre 3 y 100 caracteres.
            </div>

        <?php elseif ($error === "duplicado"): ?>

            <div class="alert alert-error">
                Ya existe una categoría con ese nombre.
            </div>

        <?php endif; ?>

        <section class="panel-formulario-categoria">

            <form
                class="formulario-categoria"
                action="categoria_procesar.php"
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
                            $categoriaActual["id"],
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>"
                    >

                <?php endif; ?>

                <div class="grupo-campo-categoria">

                    <label for="nombre">
                        Nombre de la categoría
                    </label>

                    <input
                        id="nombre"
                        type="text"
                        name="nombre"
                        required
                        minlength="3"
                        maxlength="100"
                        autocomplete="off"
                        placeholder="Ej. Desarrollo de software"
                        value="<?php echo $esEdicion
                            ? htmlspecialchars(
                                $categoriaActual["nombre"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                            : ""; ?>"
                    >

                    <small>
                        Esta categoría será utilizada para organizar y filtrar los libros.
                    </small>

                </div>

                <div class="acciones-formulario-categoria">

                    <a
                        class="boton-cancelar-categoria"
                        href="categorias.php"
                    >
                        Cancelar
                    </a>

                    <button
                        class="boton-guardar-categoria"
                        type="submit"
                    >
                        <?php echo $esEdicion
                            ? "Guardar cambios"
                            : "Crear categoría"; ?>
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>