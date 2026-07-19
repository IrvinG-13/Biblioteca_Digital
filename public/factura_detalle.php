<?php

require_once __DIR__ . '/../app/Core/NoCache.php';
require_once __DIR__ . '/../app/Controllers/FacturaController.php';

NoCache::aplicar();

$controller = new FacturaController();

/*
|--------------------------------------------------------------------------
| Obtener el ID de la factura
|--------------------------------------------------------------------------
*/

$facturaId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$facturaId || $facturaId <= 0) {
    header('Location: catalogo.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Buscar la factura
|--------------------------------------------------------------------------
*/

$factura = $controller->obtenerFactura(
    (int)$facturaId
);

if ($factura === null) {
    header('Location: catalogo.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Funciones de presentación
|--------------------------------------------------------------------------
*/

function escaparFactura(?string $valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

function formatearFechaFactura(
    ?string $fecha,
    bool $incluirHora = false
): string {
    if ($fecha === null || trim($fecha) === '') {
        return 'No disponible';
    }

    try {
        $fechaObjeto = new DateTime($fecha);

        return $fechaObjeto->format(
            $incluirHora
                ? 'd/m/Y h:i A'
                : 'd/m/Y'
        );
    } catch (Throwable $e) {
        return escaparFactura($fecha);
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

$metodoPago = $metodosPago[
    $factura['metodo_pago'] ?? ''
] ?? ucfirst(
    (string)($factura['metodo_pago'] ?? '')
);

$estado = $factura['estado'] ?? 'pagada';

$estadoVisible = $estadosFactura[$estado]
    ?? ucfirst($estado);

$referencia = trim(
    (string)($factura['referencia_pago'] ?? '')
);

$compraExitosa = (
    ($_GET['exito'] ?? '') === '1'
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
        Factura <?php echo escaparFactura(
            $factura['numero_factura'] ?? ''
        ); ?>
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/student.css?v=5"
    >

</head>

<body class="student-body">

<div class="student-layout">

    <?php include __DIR__ . '/menu_estudiante.php'; ?>

    <main class="student-main">

        <?php if ($compraExitosa): ?>

            <div class="invoice-success-message">

                <strong>Compra completada correctamente.</strong>

                El libro ya fue agregado a la sección
                “Mis libros”.

            </div>

        <?php endif; ?>

        <section class="student-page-header invoice-page-header">

            <div>

                <span class="student-eyebrow">
                    Comprobante de compra
                </span>

                <h1>Factura generada</h1>

                <p>
                    Consulta los datos de la compra y el período
                    durante el cual tendrás acceso al libro.
                </p>

            </div>

            <div class="invoice-header-actions">

                <a
                    href="mis_reservas.php"
                    class="student-request-secondary-button"
                >
                    Ir a Mis libros
                </a>

                <button
                    type="button"
                    class="student-primary-button"
                    onclick="window.print()"
                >
                    Imprimir factura
                </button>

            </div>

        </section>

        <article class="invoice-document">

            <!-- Encabezado de la factura -->

            <header class="invoice-document-header">

                <div class="invoice-company">

                    <span class="invoice-company-logo">
                        B
                    </span>

                    <div>

                        <strong>Biblioteca Digital</strong>

                        <small>
                            Comprobante de acceso digital
                        </small>

                    </div>

                </div>

                <div class="invoice-number">

                    <span>Factura</span>

                    <strong>
                        <?php echo escaparFactura(
                            $factura['numero_factura']
                            ?? ''
                        ); ?>
                    </strong>

                    <small>
                        <?php echo formatearFechaFactura(
                            $factura['fecha_factura']
                            ?? null,
                            true
                        ); ?>
                    </small>

                </div>

            </header>

            <!-- Información del cliente -->

            <section class="invoice-information-grid">

                <div class="invoice-information-box">

                    <span class="invoice-label">
                        Cliente
                    </span>

                    <strong>
                        <?php echo escaparFactura(
                            $factura['usuario']
                            ?? 'Usuario'
                        ); ?>
                    </strong>

                    <small>
                        ID de usuario:
                        <?php echo (int)(
                            $factura['usuario_id'] ?? 0
                        ); ?>
                    </small>

                </div>

                <div class="invoice-information-box">

                    <span class="invoice-label">
                        Método de pago
                    </span>

                    <strong>
                        <?php echo escaparFactura(
                            $metodoPago
                        ); ?>
                    </strong>

                    <small>
                        Referencia:
                        <?php echo $referencia !== ''
                            ? escaparFactura($referencia)
                            : 'No registrada'; ?>
                    </small>

                </div>

                <div class="invoice-information-box">

                    <span class="invoice-label">
                        Estado
                    </span>

                    <strong
                        class="invoice-status <?php
                        echo escaparFactura($estado);
                        ?>"
                    >
                        <?php echo escaparFactura(
                            $estadoVisible
                        ); ?>
                    </strong>

                </div>

            </section>

            <!-- Detalle comprado -->

            <section class="invoice-detail-section">

                <h2>Detalle de la compra</h2>

                <div class="invoice-table-wrapper">

                    <table class="invoice-table">

                        <thead>

                        <tr>

                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>

                        </tr>

                        </thead>

                        <tbody>

                        <tr>

                            <td>

                                <strong>
                                    <?php echo escaparFactura(
                                        $factura['titulo']
                                        ?? 'Libro'
                                    ); ?>
                                </strong>

                                <small>
                                    Autor:
                                    <?php echo escaparFactura(
                                        $factura['autor']
                                        ?? 'No registrado'
                                    ); ?>
                                </small>

                            </td>

                            <td>
                                <?php echo (int)(
                                    $factura['cantidad'] ?? 1
                                ); ?>
                            </td>

                            <td>
                                $<?php echo number_format(
                                    (float)(
                                        $factura[
                                            'precio_unitario'
                                        ] ?? 0
                                    ),
                                    2
                                ); ?>
                            </td>

                            <td>
                                $<?php echo number_format(
                                    (float)(
                                        $factura['subtotal'] ?? 0
                                    ),
                                    2
                                ); ?>
                            </td>

                        </tr>

                        </tbody>

                    </table>

                </div>

            </section>

            <!-- Fechas de acceso -->

            <section class="invoice-access-section">

                <h2>Período de acceso</h2>

                <div class="invoice-access-grid">

                    <div>

                        <span>Fecha de inicio</span>

                        <strong>
                            <?php echo formatearFechaFactura(
                                $factura['fecha_inicio']
                                ?? null
                            ); ?>
                        </strong>

                    </div>

                    <div>

                        <span>Fecha de vencimiento</span>

                        <strong>
                            <?php echo formatearFechaFactura(
                                $factura['fecha_vencimiento']
                                ?? null
                            ); ?>
                        </strong>

                    </div>

                    <div>

                        <span>Duración</span>

                        <strong>
                            <?php echo (int)(
                                $factura['dias_acceso'] ?? 0
                            ); ?>
                            días
                        </strong>

                    </div>

                </div>

            </section>

            <!-- Totales -->

            <section class="invoice-totals">

                <div>

                    <span>Subtotal</span>

                    <strong>
                        $<?php echo number_format(
                            (float)(
                                $factura['subtotal'] ?? 0
                            ),
                            2
                        ); ?>
                    </strong>

                </div>

                <div>

                    <span>Impuesto</span>

                    <strong>
                        $<?php echo number_format(
                            (float)(
                                $factura['impuesto'] ?? 0
                            ),
                            2
                        ); ?>
                    </strong>

                </div>

                <div class="invoice-total-final">

                    <span>Total pagado</span>

                    <strong>
                        $<?php echo number_format(
                            (float)(
                                $factura['total'] ?? 0
                            ),
                            2
                        ); ?>
                    </strong>

                </div>

            </section>

            <footer class="invoice-footer">

                <p>
                    Este documento corresponde a una simulación
                    académica de facturación.
                </p>

                <p>
                    El acceso al libro estará disponible hasta la
                    fecha de vencimiento indicada.
                </p>

            </footer>

        </article>

    </main>

</div>

</body>

</html>