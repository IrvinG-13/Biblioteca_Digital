<?php

/*
|--------------------------------------------------------------------------
| Iniciar sesión
|--------------------------------------------------------------------------
*/
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Validar que exista una sesión
|-------------------------------------------a-------------------------------
*/
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Solo estudiantes y profesores
|--------------------------------------------------------------------------
*/
if (
    !in_array(
        $_SESSION["rol"] ?? "",
        ["estudiante", "profesor"],
        true
    )
) {
    header("Location: dashboard.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Archivos necesarios
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../app/Core/NoCache.php';
require_once __DIR__ . '/../app/Core/Csrf.php';
require_once __DIR__ . '/../app/Models/LibroModel.php';
require_once __DIR__ . '/../app/Models/FacturaModel.php';

NoCache::aplicar();

/*
|--------------------------------------------------------------------------
| Token de seguridad
|--------------------------------------------------------------------------
*/
$csrfToken = Csrf::generarToken();

/*
|--------------------------------------------------------------------------
| Escapar contenido antes de mostrarlo
|--------------------------------------------------------------------------
*/
function escaparDetalle(?string $valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

/*
|--------------------------------------------------------------------------
| Construir la ruta de un archivo
|--------------------------------------------------------------------------
*/
function construirRutaArchivo(
    string $archivo,
    string $carpeta
): string {
    $archivo = trim($archivo);

    if ($archivo === "") {
        return "";
    }

    /*
     * Si ya es una URL completa, se devuelve igual.
     */
    if (preg_match('/^https?:\/\//i', $archivo)) {
        return $archivo;
    }

    $archivo = str_replace("\\", "/", $archivo);

    /*
     * Si la ruta comienza con ../, no se modifica.
     */
    if (str_starts_with($archivo, "../")) {
        return $archivo;
    }

    /*
     * Si ya contiene uploads/, se agrega ../
     */
    if (str_starts_with($archivo, "uploads/")) {
        return "../" . $archivo;
    }

    /*
     * Si solamente contiene el nombre del archivo,
     * se construye la ruta completa.
     */
    return "../uploads/"
        . $carpeta
        . "/"
        . rawurlencode(basename($archivo));
}

/*
|--------------------------------------------------------------------------
| Obtener el ID del libro
|--------------------------------------------------------------------------
*/
$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    header("Location: catalogo.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Buscar el libro
|--------------------------------------------------------------------------
*/
$modelo = new LibroModel();

$libro = $modelo->obtenerPorId(
    (int)$id
);

if ($libro === null) {
    header("Location: catalogo.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Preparar información del libro
|--------------------------------------------------------------------------
*/
$origen = $libro["origen"] ?? "propio";

$tipoAcceso = $libro["tipo_acceso"] ?? "gratuito";

$disponibles = (int)(
    $libro["unidades_disponibles"] ?? 0
);

$precio = (float)(
    $libro["precio_acceso"] ?? 0
);

$diasAcceso = (int)(
    $libro["dias_acceso"] ?? 0
);

/*
 * La base de datos utiliza url_externo.
 * También se deja url_externa como respaldo.
 */
$urlExterna = trim(
    (string)(
        $libro["url_externo"]
        ?? $libro["url_externa"]
        ?? ""
    )
);

/*
 * Comprobar si existe un PDF registrado.
 */
$archivoPdf = construirRutaArchivo(
    (string)($libro["archivo_pdf"] ?? ""),
    "pdfs"
);

/*
|--------------------------------------------------------------------------
| Comprobar si ya existe acceso vigente
|--------------------------------------------------------------------------
*/
$tieneAccesoActivo = false;

/*
 * Solo es necesario revisar acceso para libros
 * propios que sean de pago.
 */
if (
    $origen === "propio"
    && $tipoAcceso === "pago"
) {
    $facturaModelo = new FacturaModel();

    $tieneAccesoActivo =
        $facturaModelo->usuarioTieneAccesoActivo(
            (int)$_SESSION["usuario_id"],
            (int)$id
        );
}

/*
|--------------------------------------------------------------------------
| Mensajes recibidos por URL
|--------------------------------------------------------------------------
*/
$error = trim(
    (string)($_GET["error"] ?? "")
);

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
        <?php echo escaparDetalle(
            $libro["titulo"] ?? "Libro"
        ); ?>
        - Biblioteca Digital
    </title>

<link
    rel="stylesheet"
    href="assets/css/style.css"
>

<link
    rel="stylesheet"
    href="assets/css/admin.css?v=6"
>

<link
    rel="stylesheet"
    href="assets/css/student.css?v=12"
>

</head>

<body class="student-body">

<div class="student-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="student-main">

        <a
            href="catalogo.php"
            class="back-catalog-link"
        >
            ← Volver al catálogo
        </a>

        <!-- =====================================================
             MENSAJES
        ====================================================== -->

        <?php if ($error === "acceso"): ?>

            <div class="student-access-error">

                No fue posible abrir este libro porque su acceso
                no está disponible.

            </div>

        <?php elseif ($error === "lectura"): ?>

            <div class="student-access-error">

                Ocurrió un problema al registrar la lectura.
                Intenta nuevamente.

            </div>

        <?php elseif ($error === "acceso_activo"): ?>

            <div class="student-access-error">

                Ya tienes acceso activo a este libro.
                Puedes encontrarlo en la sección Mis libros.

            </div>

        <?php endif; ?>

        <section class="book-detail-layout">

            <!-- =====================================================
                 PORTADA DEL LIBRO
            ====================================================== -->

            <div class="book-detail-cover">

                <?php if (!empty($libro["imagen"])): ?>

                    <img
                        src="../uploads/libros/<?php
                        echo rawurlencode(
                            basename(
                                str_replace(
                                    "\\",
                                    "/",
                                    $libro["imagen"]
                                )
                            )
                        );
                        ?>"
                        alt="Portada de <?php
                        echo escaparDetalle(
                            $libro["titulo"] ?? "Libro"
                        );
                        ?>"
                    >

                <?php elseif (!empty($libro["thumbnail"])): ?>

                    <img
                        src="../uploads/thumbnails/<?php
                        echo rawurlencode(
                            basename(
                                str_replace(
                                    "\\",
                                    "/",
                                    $libro["thumbnail"]
                                )
                            )
                        );
                        ?>"
                        alt="Portada de <?php
                        echo escaparDetalle(
                            $libro["titulo"] ?? "Libro"
                        );
                        ?>"
                    >

                <?php else: ?>

                    <div class="detail-placeholder">

                        <span>LIBRO</span>

                    </div>

                <?php endif; ?>

            </div>

            <!-- =====================================================
                 INFORMACIÓN DEL LIBRO
            ====================================================== -->

            <div class="book-detail-information">

                <span class="book-detail-category">

                    <?php echo escaparDetalle(
                        $libro["categoria_nombre"]
                        ?? "Sin categoría"
                    ); ?>

                </span>

                <h1>

                    <?php echo escaparDetalle(
                        $libro["titulo"] ?? "Libro sin título"
                    ); ?>

                </h1>

                <p class="book-detail-author">

                    Por

                    <strong>

                        <?php echo escaparDetalle(
                            $libro["autor"]
                            ?? "Autor no especificado"
                        ); ?>

                    </strong>

                </p>

                <!-- Etiquetas -->

                <div class="book-detail-badges">

                    <?php if ($origen === "externo"): ?>

                        <span class="detail-badge external">
                            Biblioteca externa
                        </span>

                    <?php else: ?>

                        <span class="detail-badge own">
                            Biblioteca propia
                        </span>

                    <?php endif; ?>

                    <?php if ($tipoAcceso === "pago"): ?>

                        <span class="detail-badge paid">
                            Acceso de pago
                        </span>

                    <?php else: ?>

                        <span class="detail-badge free">
                            Acceso gratuito
                        </span>

                    <?php endif; ?>

                </div>

                <!-- Sinopsis -->

                <div class="book-synopsis">

                    <h2>Sinopsis</h2>

                    <p>

                        <?php echo nl2br(
                            escaparDetalle(
                                $libro["descripcion"]
                                ?? "Este libro no tiene una descripción registrada."
                            )
                        ); ?>

                    </p>

                </div>

                <!-- Información adicional -->

                <div class="book-metadata">

                    <div>

                        <span>Categoría</span>

                        <strong>

                            <?php echo escaparDetalle(
                                $libro["categoria_nombre"]
                                ?? "Sin categoría"
                            ); ?>

                        </strong>

                    </div>

                    <div>

                        <span>Unidades totales</span>

                        <strong>

                            <?php echo (int)(
                                $libro["unidades_totales"]
                                ?? 0
                            ); ?>

                        </strong>

                    </div>

                    <div>

                        <span>Unidades disponibles</span>

                        <strong>

                            <?php echo $origen === "externo"
                                ? "Externo"
                                : $disponibles; ?>

                        </strong>

                    </div>

                    <div>

                        <span>Tipo de acceso</span>

                        <strong>

                            <?php echo $tipoAcceso === "pago"
                                ? "Pago"
                                : "Gratuito"; ?>

                        </strong>

                    </div>

                </div>

            </div>

            <!-- =====================================================
                 PANEL DE ACCIÓN
            ====================================================== -->

            <aside class="book-action-panel">

                <!-- Libro externo -->

                <?php if ($origen === "externo"): ?>

                    <span class="action-panel-label">
                        Libro interbibliotecario
                    </span>

                    <h2>

                        <?php echo escaparDetalle(
                            $libro["institucion_origen"]
                            ?? "Biblioteca de origen"
                        ); ?>

                    </h2>

                    <p>

                        Este libro pertenece a una institución
                        externa. El acceso se realizará desde la
                        página de la biblioteca de origen.

                    </p>

                    <?php if ($urlExterna !== ""): ?>

                        <a
                            href="<?php echo escaparDetalle(
                                $urlExterna
                            ); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="student-primary-button full"
                        >
                            Visitar biblioteca de origen
                        </a>

                    <?php else: ?>

                        <button
                            type="button"
                            class="student-disabled-button"
                            disabled
                        >
                            Enlace no disponible
                        </button>

                    <?php endif; ?>

                <!-- Libro propio de pago -->

                <?php elseif ($tipoAcceso === "pago"): ?>

                    <span class="action-panel-label">
                        Acceso digital
                    </span>

                    <div class="book-price">

                        $<?php echo number_format(
                            $precio,
                            2
                        ); ?>

                    </div>

                    <p>

                        Pago único con acceso durante

                        <strong>

                            <?php echo $diasAcceso > 0
                                ? $diasAcceso
                                : 365; ?>

                            días

                        </strong>.

                    </p>

                    <!-- Ya tiene acceso -->

                    <?php if ($tieneAccesoActivo): ?>

                        <a
                            href="mis_reservas.php"
                            class="student-primary-button full"
                        >
                            Ir a Mis libros
                        </a>

                        <small>
                            Ya tienes acceso activo a este libro.
                        </small>

                    <!-- Todavía no tiene acceso -->

                    <?php else: ?>

                        <a
                            href="comprar_libro.php?id=<?php
                            echo (int)$libro["id"];
                            ?>"
                            class="student-primary-button full"
                        >
                            Obtener acceso
                        </a>

                        <small>
                            El pago será procesado mediante el
                            módulo de facturación.
                        </small>

                    <?php endif; ?>

                <!-- Libro propio gratuito -->

                <?php else: ?>

                    <span class="action-panel-label">
                        Acceso gratuito
                    </span>

                    <h2>Lectura permanente</h2>

                    <p>

                        Puedes consultar este libro sin realizar
                        ningún pago.

                    </p>

                    <?php if ($archivoPdf !== ""): ?>

                        <!--
                        Este formulario registra el libro en
                        Mis libros antes de abrir el PDF.
                        -->

                        <form
                            action="leer_libro.php"
                            method="POST"
                            class="book-read-form"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo escaparDetalle(
                                    $csrfToken
                                ); ?>"
                            >

                            <input
                                type="hidden"
                                name="libro_id"
                                value="<?php echo (int)$libro["id"]; ?>"
                            >

                            <button
                                type="submit"
                                class="student-primary-button full"
                            >
                                Leer libro
                            </button>

                        </form>

                    <?php elseif ($disponibles > 0): ?>

                        <button
                            type="button"
                            class="student-disabled-button"
                            disabled
                        >
                            Reservar libro
                        </button>

                        <small>

                            Este libro no tiene un archivo PDF.
                            La reserva física se conectará después.

                        </small>

                    <?php else: ?>

                        <button
                            type="button"
                            class="student-disabled-button"
                            disabled
                        >
                            Sin unidades disponibles
                        </button>

                    <?php endif; ?>

                <?php endif; ?>

            </aside>

        </section>

    </main>

</div>

</body>

</html>