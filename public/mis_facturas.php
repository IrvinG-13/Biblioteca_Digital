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
require_once __DIR__
    . '/../app/Controllers/FacturaController.php';

NoCache::aplicar();

/*
|--------------------------------------------------------------------------
| Obtener las facturas del usuario
|--------------------------------------------------------------------------
*/
$controller = new FacturaController();

$facturas = $controller->obtenerMisFacturas();

/*
|--------------------------------------------------------------------------
| Escapar contenido
|--------------------------------------------------------------------------
*/
function escaparMisFacturas(?string $valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| Formatear fechas
|--------------------------------------------------------------------------
*/
function formatearFechaMisFacturas(
    ?string $fecha,
    bool $conHora = false
): string {
    if ($fecha === null || trim($fecha) === '') {
        return 'No disponible';
    }

    try {
        $fechaObjeto = new DateTime($fecha);

        return $fechaObjeto->format(
            $conHora
                ? 'd/m/Y h:i A'
                : 'd/m/Y'
        );
    } catch (Throwable $e) {
        return escaparMisFacturas($fecha);
    }
}

/*
|--------------------------------------------------------------------------
| Nombres visibles
|--------------------------------------------------------------------------
*/
$metodosPago = [
    'yappy' => 'Yappy',
    'tarjeta' => 'Tarjeta',
    'transferencia' => 'Transferencia bancaria'
];

$estadosFactura = [
    'pagada' => 'Pagada',
    'pendiente' => 'Pendiente',
    'anulada' => 'Anulada'
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
        Mis facturas - Biblioteca Digital
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/student.css?v=8"
    >
        <link
        rel="stylesheet"
        href="assets/css/admin.css?v=7"
    >

</head>

<body class="student-body">

<div class="student-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="student-main">

        <section class="student-page-header">

            <div>

                <span class="student-eyebrow">
                    Historial de compras
                </span>

                <h1>Mis facturas</h1>

                <p>
                    Consulta los comprobantes generados por tus
                    compras de acceso digital.
                </p>

            </div>

            <a
                href="catalogo.php"
                class="student-primary-button"
            >
                Explorar catálogo
            </a>

        </section>

        <?php if (empty($facturas)): ?>

            <section class="student-empty-state invoice-empty-state">

                <div class="empty-icon">
                    ▤
                </div>

                <h2>Aún no tienes facturas</h2>

                <p>
                    Cuando compres el acceso digital a un libro,
                    la factura aparecerá en esta sección.
                </p>

                <a
                    href="catalogo.php"
                    class="student-primary-button"
                >
                    Explorar libros
                </a>

            </section>

        <?php else: ?>

            <section class="my-invoices-list">

                <?php foreach ($facturas as $factura): ?>

                    <?php

                    $metodo = $factura['metodo_pago']
                        ?? '';

                    $metodoVisible = $metodosPago[$metodo]
                        ?? ucfirst($metodo);

                    $estado = $factura['estado']
                        ?? 'pagada';

                    $estadoVisible =
                        $estadosFactura[$estado]
                        ?? ucfirst($estado);

                    ?>

                    <article class="my-invoice-card">

                        <div class="my-invoice-main">

                            <div class="my-invoice-icon">
                                ▤
                            </div>

                            <div class="my-invoice-information">

                                <div class="my-invoice-heading">

                                    <div>

                                        <span class="my-invoice-number">

                                            <?php echo
                                                escaparMisFacturas(
                                                    $factura[
                                                        'numero_factura'
                                                    ]
                                                    ?? 'Factura'
                                                );
                                            ?>

                                        </span>

                                        <h2>

                                            <?php echo
                                                escaparMisFacturas(
                                                    $factura['titulo']
                                                    ?? 'Libro'
                                                );
                                            ?>

                                        </h2>

                                    </div>

                                    <span
                                        class="my-invoice-status <?php
                                        echo escaparMisFacturas(
                                            $estado
                                        );
                                        ?>"
                                    >
                                        <?php echo
                                            escaparMisFacturas(
                                                $estadoVisible
                                            );
                                        ?>
                                    </span>

                                </div>

                                <p class="my-invoice-author">

                                    <?php echo escaparMisFacturas(
                                        $factura['autor']
                                        ?? 'Autor no especificado'
                                    ); ?>

                                </p>

                                <div class="my-invoice-data">

                                    <div>

                                        <span>Fecha de compra</span>

                                        <strong>

                                            <?php echo
                                                formatearFechaMisFacturas(
                                                    $factura[
                                                        'fecha_factura'
                                                    ]
                                                    ?? null,
                                                    true
                                                );
                                            ?>

                                        </strong>

                                    </div>

                                    <div>

                                        <span>Método de pago</span>

                                        <strong>

                                            <?php echo
                                                escaparMisFacturas(
                                                    $metodoVisible
                                                );
                                            ?>

                                        </strong>

                                    </div>

                                    <div>

                                        <span>Inicio del acceso</span>

                                        <strong>

                                            <?php echo
                                                formatearFechaMisFacturas(
                                                    $factura[
                                                        'fecha_inicio'
                                                    ]
                                                    ?? null
                                                );
                                            ?>

                                        </strong>

                                    </div>

                                    <div>

                                        <span>Vencimiento</span>

                                        <strong>

                                            <?php echo
                                                formatearFechaMisFacturas(
                                                    $factura[
                                                        'fecha_vencimiento'
                                                    ]
                                                    ?? null
                                                );
                                            ?>

                                        </strong>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="my-invoice-actions">

                            <div class="my-invoice-total">

                                <span>Total pagado</span>

                                <strong>

                                    $<?php echo number_format(
                                        (float)(
                                            $factura['total']
                                            ?? 0
                                        ),
                                        2
                                    ); ?>

                                </strong>

                            </div>

                            <a
                                href="factura_detalle.php?id=<?php
                                echo (int)$factura['id'];
                                ?>"
                                class="reservation-button"
                            >
                                Ver factura
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </section>

        <?php endif; ?>

    </main>

</div>

</body>

</html>