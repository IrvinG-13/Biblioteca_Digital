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

$controller = new ProfesorController();
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

    <title>Profesores | ReadPoint</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/profesores.css?v=2">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-profesores">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>Gestión de profesores</h1>

                <p>
                    Consulta y administra los profesores registrados.
                </p>
            </div>

            <a
                class="boton-nuevo-profesor"
                href="profesor_form.php"
            >
                Nuevo profesor
            </a>

        </section>

        <?php if ($exito === "1"): ?>
            <div class="alert alert-success">
                Operación realizada con éxito.
            </div>
        <?php endif; ?>

        <section class="panel-profesores">

            <form
                class="barra-busqueda-profesores"
                action="profesores.php"
                method="GET"
            >
                <input
                    class="campo-busqueda-profesores"
                    type="text"
                    name="busqueda"
                    placeholder="Buscar por cédula, nombre o apellido..."
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
                    href="profesores.php"
                >
                    Limpiar
                </a>
            </form>

            <div class="contenedor-tabla-profesores">

                <table class="tabla-profesores">

                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombre completo</th>
                            <th>Materia</th>
                            <th>Cuenta vinculada</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($datos["profesores"])): ?>

                        <tr>
                            <td
                                class="estado-vacio-tabla"
                                colspan="5"
                            >
                                No se encontraron profesores.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($datos["profesores"] as $p): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars(
                                            $p["cedula"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $p["primer_nombre"] . " " .
                                        ($p["segundo_nombre"]
                                            ? $p["segundo_nombre"] . " "
                                            : "") .
                                        $p["primer_apellido"] . " " .
                                        ($p["segundo_apellido"] ?? ""),
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>
                                </td>

                                <td>
                                    <span class="etiqueta-materia">
                                        <?php echo htmlspecialchars(
                                            $p["materia_nombre"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ($p["usuario_id"]): ?>

                                        <span class="estado-cuenta cuenta-vinculada">
                                            Sí
                                        </span>

                                    <?php else: ?>

                                        <span class="estado-cuenta cuenta-no-vinculada">
                                            No
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td>

                                    <div class="acciones-profesor">

                                        <a
                                            class="accion-editar"
                                            href="profesor_form.php?id=<?php echo urlencode(
                                                $p["id"]
                                            ); ?>"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            action="profesor_eliminar.php"
                                            method="POST"
                                            onsubmit="return confirm(
                                                '¿Seguro que deseas eliminar este profesor?'
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
                                                    $p["id"],
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

            <nav class="paginacion-profesores">

                <?php for (
                    $i = 1;
                    $i <= $datos["totalPaginas"];
                    $i++
                ): ?>

                    <?php if ($i === $datos["paginaActual"]): ?>

                        <span class="pagina-actual">
                            <?php echo $i; ?>
                        </span>

                    <?php else: ?>

                        <a href="profesores.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode(
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