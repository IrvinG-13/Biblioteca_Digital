<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../app/Core/NoCache.php';
require_once __DIR__ . '/../app/Controllers/SolicitudController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

NoCache::aplicar();

$controller = new SolicitudController();
$datos = $controller->datosFormulario();

$estudiante = $datos['estudiante'];
$categorias = $datos['categorias'];

$token = Csrf::generarToken();

$error = $_GET['error'] ?? '';

$mensajesError = [
    'titulo' =>
        'El título debe tener entre 3 y 200 caracteres.',

    'categoria' =>
        'Debes seleccionar una categoría válida.',

    'area' =>
        'Debes seleccionar una categoría válida.',

    'comentario' =>
        'El motivo no puede superar los 1000 caracteres.',

    'duplicada' =>
        'Ya tienes una solicitud pendiente para ese mismo libro.',

    'guardar' =>
        'No fue posible registrar la solicitud. Inténtalo nuevamente.'
];

$partesNombre = [
    $estudiante['primer_nombre'] ?? '',
    $estudiante['segundo_nombre'] ?? '',
    $estudiante['primer_apellido'] ?? '',
    $estudiante['segundo_apellido'] ?? ''
];

$partesNombre = array_filter(
    $partesNombre,
    static fn ($parte) =>
        trim((string)$parte) !== ''
);

$nombreCompleto = implode(' ', $partesNombre);

$esc = static function ($valor): string {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
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
        Solicitar libro - Biblioteca Digital
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css?v=solicitudes-8"
    >

    <link
        rel="stylesheet"
        href="assets/css/student.css?v=solicitudes-8"
    >

</head>

<body class="student-body">

<div class="student-layout">

    <?php include __DIR__ . '/menu_estudiante.php'; ?>

    <main class="student-main">

        <section class="student-request-page-header">

            <div>

                <span class="student-eyebrow">
                    Solicitudes
                </span>

                <h1>Solicitar un libro</h1>

                <p>
                    Pide un libro que todavía no esté disponible
                    en el catálogo de la biblioteca.
                </p>

            </div>

            <a
                href="mis_solicitudes.php"
                class="student-request-secondary-button"
            >
                Ver mis solicitudes
            </a>

        </section>

        <section class="student-request-profile">

            <div>

                <span>Estudiante</span>

                <strong>
                    <?php echo $esc($nombreCompleto); ?>
                </strong>

            </div>

            <div>

                <span>CIP</span>

                <strong>
                    <?php echo $esc(
                        $estudiante['cip'] ?? ''
                    ); ?>
                </strong>

            </div>

            <div>

                <span>Carrera</span>

                <strong>
                    <?php echo $esc(
                        $estudiante['carrera_nombre']
                        ?? 'No especificada'
                    ); ?>
                </strong>

            </div>

        </section>

        <?php if (isset($mensajesError[$error])): ?>

            <div class="alert alert-error student-request-alert">

                <?php echo $esc(
                    $mensajesError[$error]
                ); ?>

            </div>

        <?php endif; ?>

        <form
            action="solicitud_procesar.php"
            method="POST"
            class="student-request-form"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo $esc($token); ?>"
            >

            <div class="student-request-form-header">

                <div>

                    <h2>Datos del libro</h2>

                    <p>
                        Completa la información para enviar la
                        solicitud al administrador.
                    </p>

                </div>

                <span class="student-request-status">
                    Pendiente de revisión
                </span>

            </div>

            <div class="form-group">

                <label for="titulo_solicitado">
                    Título del libro
                </label>

                <input
                    id="titulo_solicitado"
                    type="text"
                    name="titulo_solicitado"
                    required
                    minlength="3"
                    maxlength="200"
                    placeholder="Ej. Programación orientada a objetos con PHP"
                >

                <small>
                    Escribe el nombre del libro que deseas
                    incorporar al catálogo.
                </small>

            </div>

            <div class="form-group">

                <label for="categoria">
                    Categoría
                </label>

                <select
                    id="categoria"
                    name="categoria"
                    required
                >

                    <option value="">
                        Selecciona una categoría
                    </option>

                    <?php foreach ($categorias as $categoria): ?>

                        <option
                            value="<?php echo $esc($categoria); ?>"
                        >
                            <?php echo $esc($categoria); ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <small>
                    Estas categorías son las mismas que administra
                    la biblioteca.
                </small>

            </div>

            <div class="form-group">

                <label for="comentario">
                    Motivo de la solicitud
                </label>

                <textarea
                    id="comentario"
                    name="comentario"
                    rows="6"
                    maxlength="1000"
                    placeholder="Ej. Necesito este libro para reforzar los temas vistos en la asignatura..."
                ></textarea>

                <div class="student-request-help-row">

                    <small>
                        Este campo es opcional.
                    </small>

                    <span id="contador-comentario">
                        0 de 1000 caracteres
                    </span>

                </div>

            </div>

            <div class="student-request-note">

                La solicitud llegará al administrador con estado
                <strong>Pendiente</strong>. Después podrás revisar
                la respuesta desde “Mis solicitudes”.

            </div>

            <div class="student-request-actions">

                <a
                    href="mis_solicitudes.php"
                    class="student-request-secondary-button"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="student-primary-button"
                >
                    Enviar solicitud
                </button>

            </div>

        </form>

    </main>

</div>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const comentario =
            document.getElementById('comentario');

        const contador =
            document.getElementById(
                'contador-comentario'
            );

        function actualizarContador() {
            contador.textContent =
                comentario.value.length
                + ' de 1000 caracteres';
        }

        comentario.addEventListener(
            'input',
            actualizarContador
        );

        actualizarContador();
    }
);

</script>

</body>

</html>