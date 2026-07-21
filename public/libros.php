<?php

session_start();

if (
    !isset($_SESSION["usuario_id"]) ||
    ($_SESSION["rol"] ?? "") !== "admin"
) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/LibroController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$controller = new LibroController();
$datos = $controller->listar();

$token = Csrf::generarToken();

$exito = $_GET["exito"] ?? "";
$error = $_GET["error"] ?? "";

$mensajesError = [
    "reserva" =>
        "No se puede eliminar el libro porque tiene reservas asociadas.",

    "no_encontrado" =>
        "El libro solicitado no existe.",

    "eliminar" =>
        "No fue posible eliminar el libro. Inténtalo nuevamente."
];

$esc = static function ($valor): string {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
};
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Libros | ReadPoint</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/libros.css?v=2">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-libros">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>Gestión de libros</h1>

                <p>
                    Administra los libros propios, gratuitos, pagados y externos.
                </p>
            </div>

            <div class="acciones-encabezado-libros">

                <a
                    class="boton-exportar-libros"
                    href="libro_exportar.php"
                >
                    Exportar a Excel
                </a>

                <a
                    class="boton-nuevo-libro"
                    href="libro_form.php"
                >
                    Nuevo libro
                </a>

            </div>

        </section>

        <?php if ($exito === "1"): ?>

            <div class="alert alert-success">
                Operación realizada correctamente.
            </div>

        <?php elseif (isset($mensajesError[$error])): ?>

            <div class="alert alert-error">
                <?php echo $esc($mensajesError[$error]); ?>
            </div>

        <?php endif; ?>

        <section class="panel-libros">

            <form
                class="barra-busqueda-libros"
                action="libros.php"
                method="GET"
            >

                <input
                    class="campo-busqueda-libros"
                    type="text"
                    name="busqueda"
                    placeholder="Buscar por título, autor o categoría..."
                    value="<?php echo $esc(
                        $datos["busqueda"]
                    ); ?>"
                >

                <button
                    class="boton-buscar boton-principal-readpoint"
                    type="submit"
                >
                    Buscar
                </button>

                <a
                    class="boton-limpiar boton-principal-readpoint"
                    href="libros.php"
                >
                    Limpiar
                </a>

            </form>

            <div class="contenedor-tabla-libros">

                <table class="tabla-libros">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Portada</th>
                            <th>Libro</th>
                            <th>Categoría</th>
                            <th>Origen</th>
                            <th>Costo</th>
                            <th>Acceso digital</th>
                            <th>Disponibilidad</th>
                            <th>Recurso</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($datos["libros"])): ?>

                        <tr>
                            <td
                                class="estado-vacio-tabla"
                                colspan="11"
                            >
                                No se encontraron libros.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($datos["libros"] as $libro): ?>

                            <?php

                            $thumbnail = basename(
                                (string)($libro["thumbnail"] ?? "")
                            );

                            $archivoPdf = basename(
                                (string)($libro["archivo_pdf"] ?? "")
                            );

                            $origen = $libro["origen"] ?? "propio";
                            $esExterno = $origen === "externo";

                            $tipoAcceso =
                                $libro["tipo_acceso"] ?? "gratuito";

                            $esPago = $tipoAcceso === "pago";

                            $precioAcceso = (float)(
                                $libro["precio_acceso"] ?? 0
                            );

                            $diasAcceso =
                                $libro["dias_acceso"] ?? null;

                            $disponibles = (int)(
                                $libro["unidades_disponibles"] ?? 0
                            );

                            $totales = (int)(
                                $libro["unidades_totales"] ?? 0
                            );

                            $urlExterno =
                                $libro["url_externo"] ?? "";

                            $urlValida =
                                $urlExterno !== "" &&
                                filter_var(
                                    $urlExterno,
                                    FILTER_VALIDATE_URL
                                );

                            ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?php echo (int)$libro["id"]; ?>
                                    </strong>
                                </td>

                                <td>

                                    <?php if ($thumbnail !== ""): ?>

                                        <img
                                            class="portada-libro"
                                            src="../uploads/thumbnails/<?php echo rawurlencode(
                                                $thumbnail
                                            ); ?>"
                                            alt="Portada de <?php echo $esc(
                                                $libro["titulo"]
                                            ); ?>"
                                        >

                                    <?php else: ?>

                                        <span class="etiqueta-libro etiqueta-advertencia">
                                            Sin imagen
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <strong class="titulo-libro">
                                        <?php echo $esc(
                                            $libro["titulo"]
                                        ); ?>
                                    </strong>

                                    <span class="detalle-libro">
                                        Autor:
                                        <?php echo $esc(
                                            $libro["autor"]
                                        ); ?>
                                    </span>

                                </td>

                                <td>
                                    <span class="etiqueta-libro etiqueta-neutral">
                                        <?php echo $esc(
                                            $libro["categoria_nombre"]
                                        ); ?>
                                    </span>
                                </td>

                                <td>

                                    <?php if ($esExterno): ?>

                                        <span class="etiqueta-libro etiqueta-advertencia">
                                            Externo
                                        </span>

                                        <span class="detalle-libro">
                                            <?php echo $esc(
                                                $libro["institucion_origen"]
                                                ?? "Institución no especificada"
                                            ); ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="etiqueta-libro etiqueta-neutral">
                                            Propio
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <strong>
                                        B/.
                                        <?php echo number_format(
                                            (float)($libro["costo"] ?? 0),
                                            2,
                                            ".",
                                            ","
                                        ); ?>
                                    </strong>

                                    <span class="detalle-libro">
                                        Costo para la biblioteca
                                    </span>

                                </td>

                                <td>

                                    <?php if ($esExterno): ?>

                                        <span class="etiqueta-libro etiqueta-advertencia">
                                            Externo
                                        </span>

                                        <span class="detalle-libro">
                                            Condiciones definidas por la institución.
                                        </span>

                                    <?php elseif ($tipoAcceso === "gratuito"): ?>

                                        <span class="etiqueta-libro etiqueta-disponible">
                                            Gratuito
                                        </span>

                                        <span class="detalle-libro">
                                            Acceso permanente
                                        </span>

                                    <?php elseif ($esPago): ?>

                                        <span class="etiqueta-libro etiqueta-advertencia">
                                            Pagado
                                        </span>

                                        <strong class="precio-acceso">
                                            B/.
                                            <?php echo number_format(
                                                $precioAcceso,
                                                2,
                                                ".",
                                                ","
                                            ); ?>
                                        </strong>

                                        <span class="detalle-libro">
                                            Acceso por
                                            <?php echo (int)$diasAcceso; ?>
                                            días
                                        </span>

                                    <?php else: ?>

                                        <span class="etiqueta-libro etiqueta-advertencia">
                                            No definido
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?php if ($esExterno): ?>

                                        <span class="etiqueta-libro etiqueta-advertencia">
                                            Biblioteca externa
                                        </span>

                                    <?php elseif ($disponibles > 0): ?>

                                        <span class="etiqueta-libro etiqueta-disponible">
                                            Disponible
                                        </span>

                                        <span class="detalle-libro">
                                            <?php echo $disponibles; ?>
                                            de
                                            <?php echo $totales; ?>
                                            unidades
                                        </span>

                                    <?php else: ?>

                                        <span class="etiqueta-libro etiqueta-no-disponible">
                                            No disponible
                                        </span>

                                        <span class="detalle-libro">
                                            0 de
                                            <?php echo $totales; ?>
                                            unidades
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <div class="recursos-libro">

                                        <?php if ($archivoPdf !== ""): ?>
                                        <a
                                            href="ver_pdf.php?archivo=<?php echo rawurlencode(
                                                $archivoPdf
                                            ); ?>"
                                        >
                                            Ver PDF
                                        </a>

                                        <?php endif; ?>

                                        <?php if ($esExterno && $urlValida): ?>

                                            <a
                                                href="<?php echo $esc(
                                                    $urlExterno
                                                ); ?>"
                                                rel="noopener noreferrer"
                                            >
                                                Biblioteca de origen
                                            </a>

                                        <?php endif; ?>

                                        <?php if (
                                            $archivoPdf === "" &&
                                            (!$esExterno || !$urlValida)
                                        ): ?>

                                            <span class="etiqueta-libro etiqueta-advertencia">
                                                Sin archivo
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                </td>

                                <td>
                                    <span class="fecha-libro">
                                        <?php echo $esc(
                                            $libro["created_at"]
                                        ); ?>
                                    </span>
                                </td>

                                <td>

                                    <div class="acciones-libro">

                                        <a
                                            class="accion-editar"
                                            href="libro_form.php?id=<?php echo (int)$libro["id"]; ?>"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            action="libro_eliminar.php"
                                            method="POST"
                                            onsubmit="return confirm(
                                                '¿Seguro que deseas eliminar este libro?'
                                            );"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?php echo $esc(
                                                    $token
                                                ); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo (int)$libro["id"]; ?>"
                                            >

                                            <button
                                                class="accion-eliminar boton-principal-readpoint"
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

            <?php if ($datos["totalPaginas"] > 1): ?>

                <nav class="paginacion-libros">

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

                            <a href="libros.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode(
                                $datos["busqueda"]
                            ); ?>">
                                <?php echo $i; ?>
                            </a>

                        <?php endif; ?>

                    <?php endfor; ?>

                </nav>

            <?php endif; ?>

        </section>

    </main>

</div>

</body>
</html>