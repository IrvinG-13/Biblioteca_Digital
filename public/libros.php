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

    <title>
        Gestión de Libros - Biblioteca Digital
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">

        <div class="content-card">

            <!-- Encabezado -->

            <div class="page-header">

                <div>

                    <h2>
                        Gestión de Libros
                    </h2>

                    <p>
                        Administra los libros propios, gratuitos,
                        pagados y externos.
                    </p>

                </div>

                <div>

                    <a
                        class="btn btn-secondary"
                        href="libro_exportar.php"
                    >
                        Exportar a Excel
                    </a>

                    <a
                        class="btn btn-primary"
                        href="libro_form.php"
                    >
                        + Nuevo Libro
                    </a>

                </div>

            </div>

            <!-- Mensajes -->

            <?php if ($exito === "1"): ?>

                <div class="alert alert-success">
                    Operación realizada correctamente.
                </div>

            <?php elseif (isset($mensajesError[$error])): ?>

                <div class="alert alert-error">

                    <?php echo $esc(
                        $mensajesError[$error]
                    ); ?>

                </div>

            <?php endif; ?>

            <!-- Buscador -->

            <form
                class="actions-bar"
                action="libros.php"
                method="GET"
            >

                <input
                    class="search-input"
                    type="text"
                    name="busqueda"
                    placeholder="Buscar por título, autor o categoría..."
                    value="<?php echo $esc(
                        $datos["busqueda"]
                    ); ?>"
                >

                <button
                    class="btn btn-secondary"
                    type="submit"
                >
                    Buscar
                </button>

                <a
                    class="btn btn-secondary"
                    href="libros.php"
                >
                    Limpiar
                </a>

            </form>

            <!-- Tabla -->

            <div style="overflow-x: auto;">

                <table class="table">

                    <thead>

                    <tr>

                        <th>ID</th>

                        <th>Portada</th>

                        <th>Libro</th>

                        <th>Categoría</th>

                        <th>Origen</th>

                        <th>Costo de compra</th>

                        <th>Acceso digital</th>

                        <th>Disponibilidad</th>

                        <th>PDF / Enlace</th>

                        <th>Fecha</th>

                        <th>Acciones</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php if (empty($datos["libros"])): ?>

                        <tr>

                            <td colspan="11">
                                No se encontraron libros.
                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($datos["libros"] as $libro): ?>

                            <?php

                            $thumbnail = basename(
                                (string)(
                                    $libro["thumbnail"] ?? ""
                                )
                            );

                            $archivoPdf = basename(
                                (string)(
                                    $libro["archivo_pdf"] ?? ""
                                )
                            );

                            $origen =
                                $libro["origen"] ?? "propio";

                            $esExterno =
                                $origen === "externo";

                            $tipoAcceso =
                                $libro["tipo_acceso"]
                                ?? "gratuito";

                            $esPago =
                                $tipoAcceso === "pago";

                            $precioAcceso = (float)(
                                $libro["precio_acceso"] ?? 0
                            );

                            $diasAcceso =
                                $libro["dias_acceso"] ?? null;

                            $disponibles = (int)(
                                $libro["unidades_disponibles"]
                                ?? 0
                            );

                            $totales = (int)(
                                $libro["unidades_totales"]
                                ?? 0
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

                                <!-- ID -->

                                <td>
                                    <?php echo (int)$libro["id"]; ?>
                                </td>

                                <!-- Portada -->

                                <td>

                                    <?php if ($thumbnail !== ""): ?>

                                        <img
                                            class="book-cover"
                                            src="../uploads/thumbnails/<?php
                                            echo rawurlencode(
                                                $thumbnail
                                            );
                                            ?>"
                                            alt="Portada de <?php
                                            echo $esc(
                                                $libro["titulo"]
                                            );
                                            ?>"
                                        >

                                    <?php else: ?>

                                        <span class="badge badge-yellow">
                                            Sin imagen
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- Libro -->

                                <td>

                                    <strong>

                                        <?php echo $esc(
                                            $libro["titulo"]
                                        ); ?>

                                    </strong>

                                    <br>

                                    <small>

                                        Autor:

                                        <?php echo $esc(
                                            $libro["autor"]
                                        ); ?>

                                    </small>

                                </td>

                                <!-- Categoría -->

                                <td>

                                    <span class="badge badge-blue">

                                        <?php echo $esc(
                                            $libro["categoria_nombre"]
                                        ); ?>

                                    </span>

                                </td>

                                <!-- Origen -->

                                <td>

                                    <?php if ($esExterno): ?>

                                        <span class="badge badge-yellow">
                                            Externo
                                        </span>

                                        <br>

                                        <small>

                                            <?php echo $esc(
                                                $libro["institucion_origen"]
                                                ?? "Institución no especificada"
                                            ); ?>

                                        </small>

                                    <?php else: ?>

                                        <span class="badge badge-blue">
                                            Propio
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- Costo de adquisición -->

                                <td>

                                    <strong>

                                        B/.
                                        <?php echo number_format(
                                            (float)(
                                                $libro["costo"] ?? 0
                                            ),
                                            2,
                                            ".",
                                            ","
                                        ); ?>

                                    </strong>

                                    <br>

                                    <small>
                                        Costo para la biblioteca
                                    </small>

                                </td>

                                <!-- Tipo de acceso -->

                                <td>

                                    <?php if ($esExterno): ?>

                                        <span class="badge badge-yellow">
                                            Gestionado externamente
                                        </span>

                                        <br>

                                        <small>
                                            La otra institución define
                                            sus condiciones.
                                        </small>

                                    <?php elseif ($tipoAcceso === "gratuito"): ?>

                                        <span class="badge badge-blue">
                                            Gratuito
                                        </span>

                                        <br>

                                        <small>
                                            Acceso permanente
                                        </small>

                                    <?php elseif ($esPago): ?>

                                        <span class="badge badge-yellow">
                                            Pagado
                                        </span>

                                        <br>

                                        <strong>

                                            B/.
                                            <?php echo number_format(
                                                $precioAcceso,
                                                2,
                                                ".",
                                                ","
                                            ); ?>

                                        </strong>

                                        <br>

                                        <small>

                                            Acceso por

                                            <?php echo (int)$diasAcceso; ?>

                                            días

                                        </small>

                                    <?php else: ?>

                                        <span class="badge badge-yellow">
                                            No definido
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- Disponibilidad -->

                                <td>

                                    <?php if ($esExterno): ?>

                                        <span class="badge badge-yellow">
                                            Biblioteca externa
                                        </span>

                                    <?php elseif ($disponibles > 0): ?>

                                        <span class="badge badge-blue">
                                            Disponible
                                        </span>

                                        <br>

                                        <small>

                                            <?php echo $disponibles; ?>

                                            de

                                            <?php echo $totales; ?>

                                            unidades

                                        </small>

                                    <?php else: ?>

                                        <span class="badge badge-yellow">
                                            No disponible
                                        </span>

                                        <br>

                                        <small>

                                            0 de

                                            <?php echo $totales; ?>

                                            unidades

                                        </small>

                                    <?php endif; ?>

                                </td>

                                <!-- PDF o enlace -->

                                <td>

                                    <?php if ($archivoPdf !== ""): ?>

                                        <a
                                            class="btn btn-link"
                                            href="../uploads/pdfs/<?php
                                            echo rawurlencode(
                                                $archivoPdf
                                            );
                                            ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Ver PDF
                                        </a>

                                        <br>

                                    <?php endif; ?>

                                    <?php if (
                                        $esExterno &&
                                        $urlValida
                                    ): ?>

                                        <a
                                            class="btn btn-link"
                                            href="<?php echo $esc(
                                                $urlExterno
                                            ); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Biblioteca de origen
                                        </a>

                                    <?php endif; ?>

                                    <?php if (
                                        $archivoPdf === "" &&
                                        (
                                            !$esExterno ||
                                            !$urlValida
                                        )
                                    ): ?>

                                        <span class="badge badge-yellow">
                                            Sin archivo
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- Fecha -->

                                <td>

                                    <?php echo $esc(
                                        $libro["created_at"]
                                    ); ?>

                                </td>

                                <!-- Acciones -->

                                <td>

                                    <a
                                        class="btn btn-link"
                                        href="libro_form.php?id=<?php
                                        echo (int)$libro["id"];
                                        ?>"
                                    >
                                        Editar
                                    </a>

                                    <form
                                        action="libro_eliminar.php"
                                        method="POST"
                                        style="display: inline;"
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
                                            value="<?php
                                            echo (int)$libro["id"];
                                            ?>"
                                        >

                                        <button
                                            class="btn btn-danger"
                                            type="submit"
                                        >
                                            Eliminar
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <!-- Paginación -->

            <?php if ($datos["totalPaginas"] > 1): ?>

                <div class="pagination">

                    <?php for (
                        $i = 1;
                        $i <= $datos["totalPaginas"];
                        $i++
                    ): ?>

                        <?php if (
                            $i === $datos["paginaActual"]
                        ): ?>

                            <strong>
                                <?php echo $i; ?>
                            </strong>

                        <?php else: ?>

                            <a
                                href="libros.php?pagina=<?php
                                echo $i;
                                ?>&busqueda=<?php
                                echo urlencode(
                                    $datos["busqueda"]
                                );
                                ?>"
                            >
                                <?php echo $i; ?>
                            </a>

                        <?php endif; ?>

                    <?php endfor; ?>

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

</body>
</html>