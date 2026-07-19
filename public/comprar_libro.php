<?php

require_once __DIR__
    . '/../app/Core/NoCache.php';

require_once __DIR__
    . '/../app/Core/Csrf.php';

require_once __DIR__
    . '/../app/Controllers/FacturaController.php';

NoCache::aplicar();

$controller = new FacturaController();

/*
|--------------------------------------------------------------------------
| Obtener y validar el ID del libro
|--------------------------------------------------------------------------
*/
$libroId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$libroId || $libroId <= 0) {
    header('Location: catalogo.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Obtener información del libro
|--------------------------------------------------------------------------
*/
$libro = $controller->obtenerLibroParaCompra(
    (int)$libroId
);

/*
|--------------------------------------------------------------------------
| Generar token CSRF
|--------------------------------------------------------------------------
*/
$csrfToken = Csrf::generarToken();

/*
|--------------------------------------------------------------------------
| Escapar información
|--------------------------------------------------------------------------
*/
function escaparCompra(?string $valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| Datos del libro
|--------------------------------------------------------------------------
*/
$titulo = $libro['titulo'] ?? 'Libro sin título';

$autor = $libro['autor'] ?? 'Autor no registrado';

$categoria = $libro['categoria_nombre']
    ?? 'Sin categoría';

$precio = (float)(
    $libro['precio_acceso'] ?? 0
);

$diasAcceso = (int)(
    $libro['dias_acceso'] ?? 0
);

$thumbnail = trim(
    (string)($libro['thumbnail'] ?? '')
);

$imagen = trim(
    (string)($libro['imagen'] ?? '')
);

/*
|--------------------------------------------------------------------------
| Seleccionar portada
|--------------------------------------------------------------------------
*/
$portada = '';

if ($thumbnail !== '') {
    $portada = '../uploads/thumbnails/'
        . rawurlencode(
            basename(
                str_replace('\\', '/', $thumbnail)
            )
        );
} elseif ($imagen !== '') {
    $portada = '../uploads/imagenes/'
        . rawurlencode(
            basename(
                str_replace('\\', '/', $imagen)
            )
        );
}

/*
|--------------------------------------------------------------------------
| Calcular fecha de vencimiento aproximada
|--------------------------------------------------------------------------
*/
$fechaInicio = new DateTimeImmutable('today');

$fechaVencimiento = $fechaInicio->modify(
    '+' . $diasAcceso . ' days'
);

/*
|--------------------------------------------------------------------------
| Mensajes de error
|--------------------------------------------------------------------------
*/
$error = trim(
    (string)($_GET['error'] ?? '')
);

$mensajesError = [
    'metodo' =>
        'Selecciona un método de pago válido.',

    'referencia' =>
        'La referencia de pago es demasiado larga.',

    'datos' =>
        'Los datos de la compra no son válidos.',

    'acceso_activo' =>
        'Ya tienes acceso activo a este libro.',

    'guardar' =>
        'No se pudo completar la compra. Inténtalo nuevamente.'
];

$mensajeError = $mensajesError[$error] ?? '';

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
        Comprar libro - Biblioteca Digital
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/student.css?v=4"
    >

</head>

<body class="student-body">

<div class="student-layout">

    <?php include __DIR__ . '/menu_estudiante.php'; ?>

    <main class="student-main">

        <!-- Encabezado -->

        <section class="student-page-header">

            <div>

                <span class="student-eyebrow">
                    Compra digital
                </span>

                <h1>Confirmar compra</h1>

                <p>
                    Revisa la información del libro y selecciona
                    el método con el que deseas simular el pago.
                </p>

            </div>

            <a
                href="libro_detalle.php?id=<?php
                echo (int)$libroId;
                ?>"
                class="student-request-secondary-button"
            >
                Volver al libro
            </a>

        </section>

        <?php if ($mensajeError !== ''): ?>

            <div class="student-request-alert">

                <?php echo escaparCompra(
                    $mensajeError
                ); ?>

            </div>

        <?php endif; ?>

        <section class="purchase-layout">

            <!-- Información del libro -->

            <article class="purchase-summary">

                <div class="purchase-book">

                    <div class="purchase-cover">

                        <?php if ($portada !== ''): ?>

                            <img
                                src="<?php echo escaparCompra(
                                    $portada
                                ); ?>"
                                alt="Portada de <?php
                                echo escaparCompra($titulo);
                                ?>"
                            >

                        <?php else: ?>

                            <div class="purchase-cover-empty">
                                LIBRO
                            </div>

                        <?php endif; ?>

                    </div>

                    <div class="purchase-book-information">

                        <span class="purchase-category">

                            <?php echo escaparCompra(
                                $categoria
                            ); ?>

                        </span>

                        <h2>

                            <?php echo escaparCompra(
                                $titulo
                            ); ?>

                        </h2>

                        <p>

                            <?php echo escaparCompra(
                                $autor
                            ); ?>

                        </p>

                    </div>

                </div>

                <div class="purchase-data">

                    <div>

                        <span>Precio</span>

                        <strong>
                            $<?php echo number_format(
                                $precio,
                                2
                            ); ?>
                        </strong>

                    </div>

                    <div>

                        <span>Duración del acceso</span>

                        <strong>
                            <?php echo $diasAcceso; ?> días
                        </strong>

                    </div>

                    <div>

                        <span>Fecha de inicio</span>

                        <strong>
                            <?php echo $fechaInicio->format(
                                'd/m/Y'
                            ); ?>
                        </strong>

                    </div>

                    <div>

                        <span>Fecha de vencimiento</span>

                        <strong>
                            <?php echo $fechaVencimiento->format(
                                'd/m/Y'
                            ); ?>
                        </strong>

                    </div>

                </div>

                <div class="purchase-total">

                    <span>Total a pagar</span>

                    <strong>
                        $<?php echo number_format(
                            $precio,
                            2
                        ); ?>
                    </strong>

                </div>

            </article>

            <!-- Formulario de pago -->

            <form
                action="factura_procesar.php"
                method="POST"
                class="student-request-form purchase-form"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo escaparCompra(
                        $csrfToken
                    ); ?>"
                >

                <input
                    type="hidden"
                    name="libro_id"
                    value="<?php echo (int)$libroId; ?>"
                >

                <div class="student-request-form-header">

                    <div>

                        <h2>Método de pago</h2>

                        <p>
                            Esta es una simulación académica.
                            No debes ingresar información bancaria
                            real.
                        </p>

                    </div>

                    <span class="student-request-status">
                        Pago simulado
                    </span>

                </div>

                <div class="form-group">

                    <label for="metodo_pago">
                        Selecciona un método
                    </label>

                    <select
                        id="metodo_pago"
                        name="metodo_pago"
                        required
                    >

                        <option value="">
                            Seleccionar
                        </option>

                        <option value="yappy">
                            Yappy
                        </option>

                        <option value="tarjeta">
                            Tarjeta
                        </option>

                        <option value="transferencia">
                            Transferencia bancaria
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label for="referencia_pago">
                        Referencia de pago
                    </label>

                    <input
                        type="text"
                        id="referencia_pago"
                        name="referencia_pago"
                        maxlength="100"
                        placeholder="Ejemplo: YAP-458721"
                    >

                    <small>
                        Puedes colocar una referencia simulada.
                        Este campo es opcional.
                    </small>

                </div>

                <div class="student-request-note">

                    <strong>Importante:</strong>

                    Al confirmar, se generará una factura y el
                    libro se agregará automáticamente a la sección
                    Mis libros durante

                    <strong>
                        <?php echo $diasAcceso; ?> días.
                    </strong>

                </div>

                <div class="student-request-actions">

                    <a
                        href="libro_detalle.php?id=<?php
                        echo (int)$libroId;
                        ?>"
                        class="student-request-secondary-button"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="student-primary-button"
                    >
                        Confirmar compra
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

</body>

</html>