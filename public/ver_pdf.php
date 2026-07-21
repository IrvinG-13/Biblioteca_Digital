<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

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

require_once __DIR__ . '/../app/Core/NoCache.php';
require_once __DIR__ . '/../app/Models/LibroModel.php';

NoCache::aplicar();

function escaparPdf(?string $valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

$libroId = filter_input(
    INPUT_GET,
    "libro_id",
    FILTER_VALIDATE_INT
);

if (!$libroId || $libroId <= 0) {
    header("Location: mis_reservas.php");
    exit;
}

$libroModel = new LibroModel();

$libro = $libroModel->obtenerPorId(
    (int)$libroId
);

if (!$libro) {
    header("Location: mis_reservas.php");
    exit;
}

$archivoPdf = trim(
    (string)($libro["archivo_pdf"] ?? "")
);

if ($archivoPdf === "") {
    header(
        "Location: libro_detalle.php?id="
        . (int)$libroId
        . "&error=acceso"
    );

    exit;
}

$nombreArchivo = basename(
    str_replace(
        "\\",
        "/",
        $archivoPdf
    )
);

$rutaPdf =
    "../uploads/pdfs/"
    . rawurlencode($nombreArchivo);

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
        <?php echo escaparPdf(
            $libro["titulo"] ?? "Lectura"
        ); ?>
        - ReadPoint
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
        href="assets/css/student.css?v=13"
    >

</head>

<body class="student-body">

<div class="student-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="student-main reader-page">

        <header class="reader-header">

            <div>

                <span class="student-eyebrow">
                    Lector digital
                </span>

                <h1>
                    <?php echo escaparPdf(
                        $libro["titulo"] ?? "Libro"
                    ); ?>
                </h1>

                <p>
                    <?php echo escaparPdf(
                        $libro["autor"]
                        ?? "Autor no especificado"
                    ); ?>
                </p>

            </div>

            <a
                href="mis_reservas.php"
                class="student-request-secondary-button"
            >
                Volver a Mis libros
            </a>

        </header>

        <section class="reader-card">

        <iframe
            src="<?php echo escaparPdf(
                $rutaPdf . '#zoom=page-width&view=FitH'
            ); ?>"
            class="reader-frame"
            title="Lectura de <?php echo escaparPdf(
                $libro["titulo"] ?? "libro"
            ); ?>"
        ></iframe>

        </section>

    </main>

</div>

</body>

</html>