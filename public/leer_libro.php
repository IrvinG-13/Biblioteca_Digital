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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: catalogo.php");
    exit;
}

require_once __DIR__ . '/../app/Core/Csrf.php';
require_once __DIR__ . '/../app/Models/LibroModel.php';
require_once __DIR__ . '/../app/Models/ReservaModel.php';

/*
|--------------------------------------------------------------------------
| Validar token CSRF
|--------------------------------------------------------------------------
*/

$csrf = $_POST["csrf_token"] ?? "";

if (!Csrf::validarToken($csrf)) {
    die("Token CSRF inválido.");
}

/*
|--------------------------------------------------------------------------
| Validar ID del libro
|--------------------------------------------------------------------------
*/

$libroId = filter_input(
    INPUT_POST,
    "libro_id",
    FILTER_VALIDATE_INT
);

if (!$libroId || $libroId <= 0) {
    header("Location: catalogo.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Obtener libro
|--------------------------------------------------------------------------
*/

$libroModel = new LibroModel();
$reservaModel = new ReservaModel();

$libro = $libroModel->obtenerPorId(
    (int)$libroId
);

if (!$libro) {
    header("Location: catalogo.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Validar acceso al libro
|--------------------------------------------------------------------------
*/

$origen = $libro["origen"] ?? "propio";

$tipoAcceso =
    $libro["tipo_acceso"] ?? "gratuito";

$archivoPdf = trim(
    (string)($libro["archivo_pdf"] ?? "")
);

if (
    $origen !== "propio"
    || $tipoAcceso !== "gratuito"
    || $archivoPdf === ""
) {
    header(
        "Location: libro_detalle.php?id="
        . (int)$libroId
        . "&error=acceso"
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| Registrar lectura y abrir lector integrado
|--------------------------------------------------------------------------
*/

try {
    /*
     * Registrar el libro en Mis libros.
     */
    $reservaModel->registrarLecturaGratuita(
        (int)$_SESSION["usuario_id"],
        (int)$libroId
    );

    /*
     * Abrir el lector integrado dentro de ReadPoint.
     */
    header(
        "Location: ver_pdf.php?libro_id="
        . (int)$libroId
    );

    exit;
} catch (Throwable $e) {
    header(
        "Location: libro_detalle.php?id="
        . (int)$libroId
        . "&error=lectura"
    );

    exit;
}