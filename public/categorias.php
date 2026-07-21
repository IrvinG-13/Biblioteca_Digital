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

$controller = new CategoriaController();
$datos = $controller->listar();

$token = Csrf::generarToken();

$exito = $_GET["exito"] ?? "";
$error = $_GET["error"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Categorías | ReadPoint</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/categorias.css?v=2">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-categorias">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>Gestión de categorías</h1>

                <p>
                    Organiza las categorías utilizadas para clasificar los libros.
                </p>
            </div>

            <a
                class="boton-nueva-categoria"
                href="categoria_form.php"
            >
                Nueva categoría
            </a>

        </section>

        <?php if ($exito === "1"): ?>

            <div class="alert alert-success">
                Operación realizada correctamente.
            </div>

        <?php elseif ($error === "tienelibros"): ?>

            <div class="alert alert-error">
                No se puede eliminar porque existen libros asociados.
            </div>

        <?php endif; ?>

        <section class="panel-categorias">

            <form
                class="barra-busqueda-categorias"
                action="categorias.php"
                method="GET"
            >

                <input
                    class="campo-busqueda-categorias"
                    type="text"
                    name="busqueda"
                    placeholder="Buscar categoría..."
                    value="<?php echo htmlspecialchars(
                        $datos["busqueda"],
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>"
                >

                <button
                    class="boton-buscar"
                    type="submit"
                >
                    Buscar
                </button>

                <a
                    class="boton-limpiar"
                    href="categorias.php"
                >
                    Limpiar
                </a>

            </form>

            <div class="contenedor-tabla-categorias">

                <table class="tabla-categorias">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($datos["categorias"])): ?>

                        <tr>
                            <td
                                class="estado-vacio-tabla"
                                colspan="3"
                            >
                                No existen categorías registradas.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($datos["categorias"] as $categoria): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars(
                                            $categoria["id"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="nombre-categoria">
                                        <?php echo htmlspecialchars(
                                            $categoria["nombre"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>
                                    </span>
                                </td>

                                <td>

                                    <div class="acciones-categoria">

                                        <a
                                            class="accion-editar"
                                            href="categoria_form.php?id=<?php echo urlencode(
                                                $categoria["id"]
                                            ); ?>"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            action="categoria_eliminar.php"
                                            method="POST"
                                            onsubmit="return confirm(
                                                '¿Eliminar esta categoría?'
                                            );"
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

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo htmlspecialchars(
                                                    $categoria["id"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ); ?>"
                                            >

                                            <button
                                                class="accion-eliminar"
                                                type="submit"
                                            >
                                                Eliminar
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <nav class="paginacion-categorias">

                <?php for (
                    $i = 1;
                    $i <= $datos["totalPaginas"];
                    $i++
                ): ?>

                    <?php if ($i == $datos["paginaActual"]): ?>

                        <span class="pagina-actual">
                            <?php echo $i; ?>
                        </span>

                    <?php else: ?>

                        <a href="categorias.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode(
                            $datos["busqueda"]
                        ); ?>">
                            <?php echo $i; ?>
                        </a>

                    <?php endif; ?>

                <?php endfor; ?>

            </nav>

        </section>

    </main>

</div>

</body>
</html>