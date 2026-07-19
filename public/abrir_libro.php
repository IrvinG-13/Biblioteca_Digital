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
| Validar sesión
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Solo estudiantes y profesores
|--------------------------------------------------------------------------
*/
if (
    !in_array(
        $_SESSION['rol'] ?? '',
        ['estudiante', 'profesor'],
        true
    )
) {
    header('Location: dashboard.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Archivos necesarios
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../app/Core/NoCache.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/LibroModel.php';

NoCache::aplicar();

/*
|--------------------------------------------------------------------------
| Obtener ID del libro
|--------------------------------------------------------------------------
*/
$libroId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$libroId || $libroId <= 0) {
    header('Location: mis_reservas.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Buscar el libro
|--------------------------------------------------------------------------
*/
$libroModel = new LibroModel();

$libro = $libroModel->obtenerPorId(
    (int)$libroId
);

if ($libro === null) {
    header('Location: mis_reservas.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Validar información del libro
|--------------------------------------------------------------------------
*/
$origen = $libro['origen'] ?? 'propio';

$tipoAcceso = $libro['tipo_acceso']
    ?? 'gratuito';

$archivoPdf = trim(
    (string)($libro['archivo_pdf'] ?? '')
);

/*
 * Los libros externos no se abren desde
 * la biblioteca local.
 */
if ($origen !== 'propio') {
    header(
        'Location: libro_detalle.php?id='
        . (int)$libroId
        . '&error=acceso'
    );

    exit;
}

/*
 * El libro debe tener un PDF registrado.
 */
if ($archivoPdf === '') {
    header(
        'Location: libro_detalle.php?id='
        . (int)$libroId
        . '&error=acceso'
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| Validar acceso para libros pagados
|--------------------------------------------------------------------------
*/
if ($tipoAcceso === 'pago') {
    $conexion = new Database();
    $db = $conexion->conectar();

    $sql = "
        SELECT id

        FROM reservas

        WHERE
            usuario_id = :usuario_id
            AND libro_id = :libro_id
            AND estado IN (
                'reservado',
                'en_prestamo',
                'por_vencer'
            )
            AND fecha_vencimiento IS NOT NULL
            AND fecha_vencimiento >= CURDATE()

        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ':usuario_id' =>
            (int)$_SESSION['usuario_id'],

        ':libro_id' =>
            (int)$libroId
    ]);

    $accesoActivo = $stmt->fetch(
        PDO::FETCH_ASSOC
    );

    /*
     * Si no existe una reserva vigente,
     * no se permite abrir el PDF.
     */
    if (!$accesoActivo) {
        header(
            'Location: libro_detalle.php?id='
            . (int)$libroId
            . '&error=acceso'
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Preparar la ruta física del PDF
|--------------------------------------------------------------------------
*/
$nombreArchivo = basename(
    str_replace(
        '\\',
        '/',
        $archivoPdf
    )
);

$directorioPdf = realpath(
    __DIR__ . '/../uploads/pdfs'
);

$rutaPdf = realpath(
    __DIR__
    . '/../uploads/pdfs/'
    . $nombreArchivo
);

/*
|--------------------------------------------------------------------------
| Validar que el archivo exista
|--------------------------------------------------------------------------
*/
if (
    $directorioPdf === false
    || $rutaPdf === false
    || !is_file($rutaPdf)
    || !str_starts_with(
        $rutaPdf,
        $directorioPdf . DIRECTORY_SEPARATOR
    )
) {
    header(
        'Location: libro_detalle.php?id='
        . (int)$libroId
        . '&error=acceso'
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| Mostrar el PDF en el navegador
|--------------------------------------------------------------------------
*/
$nombreDescarga = preg_replace(
    '/[^A-Za-z0-9._-]/',
    '_',
    $nombreArchivo
);

header('Content-Type: application/pdf');

header(
    'Content-Disposition: inline; filename="'
    . $nombreDescarga
    . '"'
);

header(
    'Content-Length: '
    . filesize($rutaPdf)
);

header('X-Content-Type-Options: nosniff');

readfile($rutaPdf);
exit;