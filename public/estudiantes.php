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

$controller = new EstudianteController();
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

    <title>Estudiantes | ReadPoint</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/estudiantes.css?v=2">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-estudiantes">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>Gestión de estudiantes</h1>

                <p>
                    Consulta y administra los estudiantes registrados.
                </p>
            </div>

            <a
                class="boton-nuevo-estudiante"
                href="estudiante_form.php"
            >
                Nuevo estudiante
            </a>

        </section>

        <?php if ($exito === "1"): ?>

            <div class="alert alert-success">
                Operación realizada con éxito.
            </div>

        <?php elseif ($error === "tienereservas"): ?>

            <div class="alert alert-error">
                No se puede eliminar: este estudiante tiene reservas registradas.
            </div>

        <?php endif; ?>

        <section class="panel-estudiantes">

            <form
                class="barra-busqueda-estudiantes"
                action="estudiantes.php"
                method="GET"
            >
                <input
                    class="campo-busqueda-estudiantes"
                    type="text"
                    name="busqueda"
                    placeholder="Buscar por CIP, nombre o apellido..."
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
                    href="estudiantes.php"
                >
                    Limpiar
                </a>
            </form>

            <div class="contenedor-tabla-estudiantes">

                <table class="tabla-estudiantes">

                    <thead>
                        <tr>
                            <th>CIP</th>
                            <th>Nombre completo</th>
                            <th>Fecha de nacimiento</th>
                            <th>Carrera</th>
                            <th>Cuenta vinculada</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($datos["estudiantes"])): ?>

                        <tr>
                            <td
                                class="estado-vacio-tabla"
                                colspan="6"
                            >
                                No se encontraron estudiantes.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($datos["estudiantes"] as $e): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars($e["cip"]); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $e["primer_nombre"] . " " .
                                        ($e["segundo_nombre"]
                                            ? $e["segundo_nombre"] . " "
                                            : "") .
                                        $e["primer_apellido"] . " " .
                                        ($e["segundo_apellido"] ?? "")
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $e["fecha_nacimiento"]
                                    ); ?>
                                </td>

                                <td>
                                    <span class="etiqueta-carrera">
                                        <?php echo htmlspecialchars(
                                            $e["carrera_nombre"]
                                        ); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ($e["usuario_id"]): ?>

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

                                    <div class="acciones-estudiante">

                                        <a
                                            class="accion-editar"
                                            href="estudiante_form.php?id=<?php echo urlencode(
                                                $e["id"]
                                            ); ?>"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            action="estudiante_eliminar.php"
                                            method="POST"
                                            onsubmit="return confirm(
                                                '¿Seguro que deseas eliminar este estudiante?'
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
                                                    $e["id"],
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

            <nav class="paginacion-estudiantes">

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

                        <a href="estudiantes.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode(
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