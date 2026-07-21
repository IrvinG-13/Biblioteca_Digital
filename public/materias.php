<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/MateriaController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$controller = new MateriaController();
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

    <title>Materias | ReadPoint</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/materias.css?v=2">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-materias">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>Gestión de materias</h1>

                <p>
                    Consulta y administra las materias disponibles.
                </p>
            </div>

            <a
                class="boton-nueva-materia"
                href="materia_form.php"
            >
                Nueva materia
            </a>

        </section>

        <?php if ($exito === "1"): ?>

            <div class="alert alert-success">
                Operación realizada con éxito.
            </div>

        <?php elseif ($error === "tieneprofesores"): ?>

            <div class="alert alert-error">
                No se puede eliminar: hay profesores asignados a esta materia.
            </div>

        <?php endif; ?>

        <section class="panel-materias">

            <form
                class="barra-busqueda-materias"
                action="materias.php"
                method="GET"
            >
                <input
                    class="campo-busqueda-materias"
                    type="text"
                    name="busqueda"
                    placeholder="Buscar materia..."
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
                    href="materias.php"
                >
                    Limpiar
                </a>
            </form>

            <div class="contenedor-tabla-materias">

                <table class="tabla-materias">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($datos["materias"])): ?>

                        <tr>
                            <td
                                class="estado-vacio-tabla"
                                colspan="3"
                            >
                                No se encontraron materias.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($datos["materias"] as $m): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars(
                                            $m["id"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="nombre-materia">
                                        <?php echo htmlspecialchars(
                                            $m["nombre"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>
                                    </span>
                                </td>

                                <td>

                                    <div class="acciones-materia">

                                        <a
                                            class="accion-editar"
                                            href="materia_form.php?id=<?php echo urlencode(
                                                $m["id"]
                                            ); ?>"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            action="materia_eliminar.php"
                                            method="POST"
                                            onsubmit="return confirm(
                                                '¿Seguro que deseas eliminar esta materia?'
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
                                                    $m["id"],
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

            <nav class="paginacion-materias">

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

                        <a href="materias.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode(
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