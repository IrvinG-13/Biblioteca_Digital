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

$token = Csrf::generarToken();

$error = $_GET["error"] ?? "";
$id = isset($_GET["id"]) ? (int)$_GET["id"] : null;

$libroActual = null;

if ($id !== null && $id > 0) {
    $libroActual = $controller->obtenerPorId($id);

    if ($libroActual === null) {
        header("Location: libros.php?error=no_encontrado");
        exit;
    }
}

$categorias = $controller->obtenerCategorias();

$esEdicion = $libroActual !== null;

$origenActual = $esEdicion
    ? ($libroActual["origen"] ?? "propio")
    : "propio";

$tipoAccesoActual = $esEdicion
    ? ($libroActual["tipo_acceso"] ?? "gratuito")
    : "gratuito";

$precioAccesoActual = $esEdicion
    ? number_format(
        (float)($libroActual["precio_acceso"] ?? 0),
        2,
        ".",
        ""
    )
    : "0.00";

$diasAccesoActual = $esEdicion
    ? ($libroActual["dias_acceso"] ?? "")
    : "";

$thumbnailActual = $esEdicion
    ? basename((string)($libroActual["thumbnail"] ?? ""))
    : "";

$pdfActual = $esEdicion
    ? basename((string)($libroActual["archivo_pdf"] ?? ""))
    : "";

$mensajesError = [
    "titulo" =>
        "El título debe tener entre 3 y 200 caracteres.",

    "autor" =>
        "El autor debe tener entre 2 y 150 caracteres.",

    "categoria" =>
        "Debes seleccionar una categoría.",

    "costo" =>
        "El costo de adquisición debe ser igual o mayor que cero.",

    "origen" =>
        "El origen seleccionado no es válido.",

    "unidades" =>
        "Las unidades no pueden ser negativas.",

    "tipo_acceso" =>
        "El tipo de acceso seleccionado no es válido.",

    "precio_acceso" =>
        "El precio de acceso debe ser mayor que cero para los libros pagados.",

    "dias_acceso" =>
        "Debes indicar una cantidad válida de días para el acceso pagado.",

    "institucion" =>
        "Debes indicar la institución de origen del libro externo.",

    "url" =>
        "Debes ingresar una URL válida que comience con http:// o https://.",

    "imagen" =>
        "La imagen debe ser JPG, JPEG o PNG y no superar los 2 MB.",

    "pdf" =>
        "El documento debe ser un archivo PDF válido y no superar los 100 MB.",

    "unidades_prestadas" =>
        "No puedes reducir el total por debajo de las unidades que están prestadas.",

    "guardar" =>
        "No fue posible guardar el libro. Verifica los datos e inténtalo nuevamente."
];
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
        <?php echo $esEdicion ? "Editar" : "Nuevo"; ?> Libro
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

            <form
                class="form-card"
                action="libro_procesar.php"
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="page-header">

                    <div>

                        <h2>
                            <?php echo $esEdicion
                                ? "Editar Libro"
                                : "Nuevo Libro";
                            ?>
                        </h2>

                        <p>
                            Registra la información bibliográfica,
                            disponibilidad y modalidad de acceso.
                        </p>

                    </div>

                </div>

                <?php if (isset($mensajesError[$error])): ?>

                    <div class="alert alert-error">

                        <?php echo htmlspecialchars(
                            $mensajesError[$error],
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>

                    </div>

                <?php endif; ?>

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
                        value="<?php echo (int)$libroActual["id"]; ?>"
                    >

                <?php endif; ?>

                <!-- Título y autor -->

                <div class="form-row">

                    <div class="form-group">

                        <label for="titulo">
                            Título
                        </label>

                        <input
                            id="titulo"
                            type="text"
                            name="titulo"
                            required
                            minlength="3"
                            maxlength="200"
                            placeholder="Ej. Contabilidad Financiera"
                            value="<?php echo $esEdicion
                                ? htmlspecialchars(
                                    $libroActual["titulo"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                : "";
                            ?>"
                        >

                    </div>

                    <div class="form-group">

                        <label for="autor">
                            Autor
                        </label>

                        <input
                            id="autor"
                            type="text"
                            name="autor"
                            required
                            minlength="2"
                            maxlength="150"
                            placeholder="Ej. Álvaro Javier Romero"
                            value="<?php echo $esEdicion
                                ? htmlspecialchars(
                                    $libroActual["autor"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                : "";
                            ?>"
                        >

                    </div>

                </div>

                <!-- Descripción -->

                <div class="form-group">

                    <label for="descripcion">
                        Descripción
                    </label>

                    <textarea
                        id="descripcion"
                        name="descripcion"
                        rows="5"
                        maxlength="2000"
                        placeholder="Breve descripción del contenido del libro..."
                    ><?php echo $esEdicion
                        ? htmlspecialchars(
                            $libroActual["descripcion"] ?? "",
                            ENT_QUOTES,
                            "UTF-8"
                        )
                        : "";
                    ?></textarea>

                </div>

                <!-- Categoría y costo -->

                <div class="form-row">

                    <div class="form-group">

                        <label for="categoria_id">
                            Categoría
                        </label>

                        <select
                            id="categoria_id"
                            name="categoria_id"
                            required
                        >

                            <option value="">
                                Seleccione una categoría
                            </option>

                            <?php foreach ($categorias as $categoria): ?>

                                <option
                                    value="<?php echo (int)$categoria["id"]; ?>"

                                    <?php
                                    if (
                                        $esEdicion &&
                                        (int)$libroActual["categoria_id"] ===
                                        (int)$categoria["id"]
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >

                                    <?php echo htmlspecialchars(
                                        $categoria["nombre"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="form-group">

                        <label for="costo">
                            Costo de adquisición
                        </label>

                        <input
                            id="costo"
                            type="number"
                            name="costo"
                            min="0"
                            step="0.01"
                            required
                            placeholder="0.00"
                            value="<?php echo $esEdicion
                                ? htmlspecialchars(
                                    number_format(
                                        (float)$libroActual["costo"],
                                        2,
                                        ".",
                                        ""
                                    ),
                                    ENT_QUOTES,
                                    "UTF-8"
                                )
                                : "0.00";
                            ?>"
                        >

                        <small>
                            Es el costo que pagó la biblioteca para
                            adquirir el libro, no el precio cobrado al usuario.
                        </small>

                    </div>

                </div>

                <!-- Origen -->

                <div class="form-group">

                    <label for="origen">
                        Origen del libro
                    </label>

                    <select
                        id="origen"
                        name="origen"
                        required
                    >

                        <option
                            value="propio"
                            <?php echo $origenActual === "propio"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Propio — pertenece a esta biblioteca
                        </option>

                        <option
                            value="externo"
                            <?php echo $origenActual === "externo"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Externo — pertenece a otra biblioteca
                        </option>

                    </select>

                </div>

                <!-- Datos del libro propio -->

                <div id="seccion-libro-propio">

                    <div class="form-group">

                        <label for="unidades_totales">
                            Unidades totales
                        </label>

                        <input
                            id="unidades_totales"
                            type="number"
                            name="unidades_totales"
                            min="0"
                            required
                            value="<?php echo $esEdicion
                                ? (int)$libroActual["unidades_totales"]
                                : 0;
                            ?>"
                        >

                        <small>
                            Las unidades disponibles se calcularán automáticamente.
                        </small>

                    </div>

                    <!-- Configuración del acceso -->

                    <div id="seccion-acceso">

                        <div class="form-group">

                            <label for="tipo_acceso">
                                Tipo de acceso al libro digital
                            </label>

                            <select
                                id="tipo_acceso"
                                name="tipo_acceso"
                                required
                            >

                                <option
                                    value="gratuito"
                                    <?php echo $tipoAccesoActual === "gratuito"
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Gratuito — acceso permanente
                                </option>

                                <option
                                    value="pago"
                                    <?php echo $tipoAccesoActual === "pago"
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Pagado — acceso por tiempo definido
                                </option>

                            </select>

                            <small>
                                Los libros gratuitos podrán leerse permanentemente.
                                Los pagados tendrán un precio fijo y una fecha de vencimiento.
                            </small>

                        </div>

                        <div id="campos-acceso-pago">

                            <div class="form-row">

                                <div class="form-group">

                                    <label for="precio_acceso">
                                        Precio de acceso
                                    </label>

                                    <input
                                        id="precio_acceso"
                                        type="number"
                                        name="precio_acceso"
                                        min="0.01"
                                        step="0.01"
                                        placeholder="10.00"
                                        value="<?php echo htmlspecialchars(
                                            $precioAccesoActual,
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>"
                                    >

                                    <small>
                                        Es el precio fijo que pagará el usuario.
                                        No se cobra por cada día.
                                    </small>

                                </div>

                                <div class="form-group">

                                    <label for="dias_acceso">
                                        Duración del acceso
                                    </label>

                                    <input
                                        id="dias_acceso"
                                        type="number"
                                        name="dias_acceso"
                                        min="1"
                                        step="1"
                                        placeholder="365"
                                        value="<?php echo htmlspecialchars(
                                            (string)$diasAccesoActual,
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>"
                                    >

                                    <small>
                                        Se recomienda utilizar 365 días
                                        para libros académicos.
                                    </small>

                                </div>

                            </div>

                            <div class="alert alert-success">

                                El usuario pagará una sola vez y podrá
                                leer el libro durante el período indicado.

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Datos del libro externo -->

                <div id="seccion-libro-externo">

                    <div class="form-row">

                        <div class="form-group">

                            <label for="institucion_origen">
                                Institución de origen
                            </label>

                            <input
                                id="institucion_origen"
                                type="text"
                                name="institucion_origen"
                                minlength="2"
                                maxlength="150"
                                placeholder="Ej. Biblioteca Nacional de Panamá"
                                value="<?php echo $esEdicion
                                    ? htmlspecialchars(
                                        $libroActual["institucion_origen"] ?? "",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    )
                                    : "";
                                ?>"
                            >

                        </div>

                        <div class="form-group">

                            <label for="url_externo">
                                Enlace de la biblioteca
                            </label>

                            <input
                                id="url_externo"
                                type="url"
                                name="url_externo"
                                maxlength="255"
                                placeholder="https://biblioteca-ejemplo.com/"
                                value="<?php echo $esEdicion
                                    ? htmlspecialchars(
                                        $libroActual["url_externo"] ?? "",
                                        ENT_QUOTES,
                                        "UTF-8"
                                    )
                                    : "";
                                ?>"
                            >

                        </div>

                    </div>

                    <div class="alert alert-success">

                        Los libros externos no se reservan ni se cobran
                        desde este sistema. El usuario será enviado
                        a la biblioteca de origen.

                    </div>

                </div>

                <!-- Portada -->

                <div class="form-group">

                    <label for="imagen">
                        Imagen de portada
                    </label>

                    <input
                        id="imagen"
                        type="file"
                        name="imagen"
                        accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    >

                    <small>
                        JPG, JPEG o PNG. Tamaño máximo: 2 MB.
                        El thumbnail se generará automáticamente.
                    </small>

                </div>

                <?php if ($thumbnailActual !== ""): ?>

                    <div class="current-image">

                        <p>
                            Portada actual:
                        </p>

                        <img
                            src="../uploads/thumbnails/<?php
                            echo rawurlencode($thumbnailActual);
                            ?>"
                            alt="Portada actual del libro"
                        >

                        <small>
                            Selecciona otra imagen únicamente
                            si deseas reemplazarla.
                        </small>

                    </div>

                <?php endif; ?>

                <!-- PDF -->

                <div class="form-group">

                    <label for="archivo_pdf">
                        Archivo PDF del libro
                    </label>

                    <input
                        id="archivo_pdf"
                        type="file"
                        name="archivo_pdf"
                        accept=".pdf,application/pdf"
                    >

                    <small>
                        Solamente archivos PDF.
                        Tamaño máximo: 100 MB.
                    </small>

                </div>

                <?php if ($pdfActual !== ""): ?>

                    <div class="current-file">

                        <p>
                            PDF actual:
                        </p>

                        <a
                            class="btn btn-secondary"
                            href="../uploads/pdfs/<?php
                            echo rawurlencode($pdfActual);
                            ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Ver PDF actual
                        </a>

                        <small>
                            Selecciona otro PDF únicamente
                            si deseas reemplazarlo.
                        </small>

                    </div>

                <?php endif; ?>

                <!-- Botones -->

                <div class="form-actions">

                    <a
                        class="btn btn-secondary"
                        href="libros.php"
                    >
                        Cancelar
                    </a>

                    <button
                        class="btn btn-primary"
                        type="submit"
                    >
                        <?php echo $esEdicion
                            ? "Guardar cambios"
                            : "Crear libro";
                        ?>
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const origen =
        document.getElementById("origen");

    const seccionLibroPropio =
        document.getElementById("seccion-libro-propio");

    const seccionLibroExterno =
        document.getElementById("seccion-libro-externo");

    const unidades =
        document.getElementById("unidades_totales");

    const institucion =
        document.getElementById("institucion_origen");

    const urlExterno =
        document.getElementById("url_externo");

    const tipoAcceso =
        document.getElementById("tipo_acceso");

    const camposAccesoPago =
        document.getElementById("campos-acceso-pago");

    const precioAcceso =
        document.getElementById("precio_acceso");

    const diasAcceso =
        document.getElementById("dias_acceso");

    let unidadesGuardadas =
        unidades.value || "0";

    let precioPagoGuardado =
        parseFloat(precioAcceso.value) > 0
            ? precioAcceso.value
            : "10.00";

    let diasPagoGuardados =
        parseInt(diasAcceso.value) > 0
            ? diasAcceso.value
            : "365";

    /**
     * Muestra u oculta los campos de pago.
     */
    function actualizarTipoAcceso() {

        const esPago =
            tipoAcceso.value === "pago";

        if (esPago) {

            camposAccesoPago.style.display = "block";

            precioAcceso.disabled = false;
            precioAcceso.required = true;

            diasAcceso.disabled = false;
            diasAcceso.required = true;

            if (
                precioAcceso.value === "" ||
                parseFloat(precioAcceso.value) <= 0
            ) {
                precioAcceso.value =
                    precioPagoGuardado;
            }

            if (
                diasAcceso.value === "" ||
                parseInt(diasAcceso.value) <= 0
            ) {
                diasAcceso.value =
                    diasPagoGuardados;
            }

        } else {

            if (parseFloat(precioAcceso.value) > 0) {
                precioPagoGuardado =
                    precioAcceso.value;
            }

            if (parseInt(diasAcceso.value) > 0) {
                diasPagoGuardados =
                    diasAcceso.value;
            }

            camposAccesoPago.style.display = "none";

            precioAcceso.value = "0.00";
            precioAcceso.disabled = true;
            precioAcceso.required = false;

            diasAcceso.value = "";
            diasAcceso.disabled = true;
            diasAcceso.required = false;
        }
    }

    /**
     * Cambia entre libro propio y libro externo.
     */
    function actualizarOrigen() {

        const esExterno =
            origen.value === "externo";

        if (esExterno) {

            if (!unidades.disabled) {
                unidadesGuardadas =
                    unidades.value || "0";
            }

            seccionLibroPropio.style.display = "none";
            seccionLibroExterno.style.display = "block";

            unidades.value = "0";
            unidades.disabled = true;
            unidades.required = false;

            tipoAcceso.disabled = true;
            tipoAcceso.required = false;

            precioAcceso.disabled = true;
            precioAcceso.required = false;

            diasAcceso.disabled = true;
            diasAcceso.required = false;

            institucion.disabled = false;
            institucion.required = true;

            urlExterno.disabled = false;
            urlExterno.required = true;

        } else {

            seccionLibroPropio.style.display = "block";
            seccionLibroExterno.style.display = "none";

            unidades.disabled = false;
            unidades.required = true;

            if (
                unidades.value === "0" &&
                unidadesGuardadas !== ""
            ) {
                unidades.value =
                    unidadesGuardadas;
            }

            tipoAcceso.disabled = false;
            tipoAcceso.required = true;

            institucion.disabled = true;
            institucion.required = false;

            urlExterno.disabled = true;
            urlExterno.required = false;

            actualizarTipoAcceso();
        }
    }

    tipoAcceso.addEventListener(
        "change",
        actualizarTipoAcceso
    );

    origen.addEventListener(
        "change",
        actualizarOrigen
    );

    actualizarOrigen();

});

</script>

</body>
</html>