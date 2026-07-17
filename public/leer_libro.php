<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if (($_SESSION['rol'] ?? '') !== 'estudiante') {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalogo.php');
    exit;
}

require_once __DIR__ . '/../app/Core/Csrf.php';
require_once __DIR__ . '/../app/Models/LibroModel.php';
require_once __DIR__ . '/../app/Models/ReservaModel.php';

$csrf = $_POST['csrf_token'] ?? '';

if (!Csrf::validarToken($csrf)) {
    die('Token CSRF inválido.');
}

$libroId = filter_input(
    INPUT_POST,
    'libro_id',
    FILTER_VALIDATE_INT
);

if (!$libroId || $libroId <= 0) {
    header('Location: catalogo.php');
    exit;
}

$libroModel = new LibroModel();
$reservaModel = new ReservaModel();

$libro = $libroModel->obtenerPorId($libroId);

if (!$libro) {
    header('Location: catalogo.php');
    exit;
}

/*
 * Solamente los libros propios y gratuitos
 * pueden registrarse desde esta acción.
 */
$origen = $libro['origen'] ?? 'propio';
$tipoAcceso = $libro['tipo_acceso'] ?? 'gratuito';
$archivoPdf = trim(
    (string)($libro['archivo_pdf'] ?? '')
);

if (
    $origen !== 'propio'
    || $tipoAcceso !== 'gratuito'
    || $archivoPdf === ''
) {
    header(
        'Location: libro_detalle.php?id='
        . $libroId
        . '&error=acceso'
    );

    exit;
}

try {
    /*
     * Se registra el libro en Mis libros.
     */
    $reservaModel->registrarLecturaGratuita(
        (int)$_SESSION['usuario_id'],
        (int)$libroId
    );

    /*
     * basename evita que se utilicen rutas externas
     * almacenadas accidentalmente en la base de datos.
     */
    $nombreArchivo = basename(
        str_replace('\\', '/', $archivoPdf)
    );

    $rutaPdf = '../uploads/pdfs/'
        . rawurlencode($nombreArchivo);

    /*
     * Después de registrarlo, se abre el PDF.
     */
    header('Location: ' . $rutaPdf);
    exit;
} catch (Throwable $e) {
    header(
        'Location: libro_detalle.php?id='
        . $libroId
        . '&error=lectura'
    );

    exit;
}