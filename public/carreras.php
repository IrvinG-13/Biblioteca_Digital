<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/CarreraController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$controller = new CarreraController();
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

    <title>Carreras | ReadPoint</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/carreras.css?v=2">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-carreras">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>Gestión de carreras</h1>

                <p>
                    Consulta y administra las carreras disponibles.
                </p>
            </div>

            <a
                class="boton-nueva-carrera"
                href="carrera_form.php"
            >
                Nueva carrera
            </a>

        </section>

        <?php if ($exito === "1"): ?>

            <div class="alert alert-success">
                Operación realizada con éxito.
            </div>

        <?php elseif ($error === "tieneestudiantes"): ?>

            <div class="alert alert-error">
                No se puede eliminar: hay estudiantes asignados a esta carrera.
            </div>

        <?php endif; ?>

        <section class="panel-carreras">

            <form
                class="barra-busqueda-carreras"
                action="carreras.php"
                method="GET"
            >
                <input
                    class="campo-busqueda-carreras"
                    type="text"
                    name="busqueda"
                    placeholder="Buscar carrera..."
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
                    href="carreras.php"
                >
                    Limpiar
                </a>
            </form>

            <div class="contenedor-tabla-carreras">

                <table class="tabla-carreras">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($datos["carreras"])): ?>

                        <tr>
                            <td
                                class="estado-vacio-tabla"
                                colspan="3"
                            >
                                No se encontraron carreras.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($datos["carreras"] as $c): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars(
                                            $c["id"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="nombre-carrera">
                                        <?php echo htmlspecialchars(
                                            $c["nombre"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>
                                    </span>
                                </td>

                                <td>

                                    <div class="acciones-carrera">

                                        <a
                                            class="accion-editar"
                                            href="carrera_form.php?id=<?php echo urlencode(
                                                $c["id"]
                                            ); ?>"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            action="carrera_eliminar.php"
                                            method="POST"
                                            onsubmit="return confirm(
                                                '¿Seguro que deseas eliminar esta carrera?'
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
                                                    $c["id"],
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

            <nav class="paginacion-carreras">

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

                        <a href="carreras.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode(
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