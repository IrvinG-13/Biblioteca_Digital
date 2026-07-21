<?php

require_once __DIR__
    . '/../app/Core/NoCache.php';

require_once __DIR__
    . '/../app/Controllers/FacturaAdminController.php';

NoCache::aplicar();

$controller =
    new FacturaAdminController();

$facturaId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$facturaId || $facturaId <= 0) {
    header('Location: facturas.php');
    exit;
}

$factura = $controller->obtenerFactura(
    (int)$facturaId
);

if ($factura === null) {
    header('Location: facturas.php');
    exit;
}

function escaparDetalleAdmin(
    mixed $valor
): string {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

function fechaDetalleAdmin(
    ?string $fecha,
    bool $hora = false
): string {
    if (
        $fecha === null
        || trim($fecha) === ''
    ) {
        return 'No disponible';
    }

    try {
        return (new DateTime($fecha))
            ->format(
                $hora
                    ? 'd/m/Y h:i A'
                    : 'd/m/Y'
            );
    } catch (Throwable $e) {
        return escaparDetalleAdmin($fecha);
    }
}

$metodosPago = [
    'yappy' => 'Yappy',
    'tarjeta' => 'Tarjeta',
    'transferencia' =>
        'Transferencia bancaria'
];

$metodo = $factura['metodo_pago']
    ?? '';

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
        Detalle de factura
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >
    

    <style>

        .admin-invoice-actions {
            margin-bottom: 22px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .admin-invoice {
            max-width: 900px;
            margin: auto;
            padding: 32px;
            border: 1px solid #dfe4df;
            border-radius: 16px;
            background: #ffffff;
        }

        .admin-invoice-header {
            padding-bottom: 22px;
            border-bottom: 1px solid #e4e8e4;
            display: flex;
            justify-content: space-between;
            gap: 25px;
        }

        .admin-invoice-header h1 {
            margin: 0 0 7px;
        }

        .admin-invoice-header p {
            margin: 0;
            color: #6e7972;
        }

        .admin-invoice-number {
            text-align: right;
        }

        .admin-invoice-number strong,
        .admin-invoice-number span {
            display: block;
        }

        .admin-invoice-number strong {
            color: #183126;
            font-size: 18px;
        }

        .admin-invoice-number span {
            margin-top: 7px;
            color: #6e7972;
            font-size: 13px;
        }

        .admin-invoice-grid {
            padding: 24px 0;
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 15px;
        }

        .admin-invoice-grid div {
            padding: 16px;
            border-radius: 11px;
            background: #f6f8f5;
        }

        .admin-invoice-grid span,
        .admin-invoice-grid strong {
            display: block;
        }

        .admin-invoice-grid span {
            color: #748078;
            font-size: 12px;
        }

        .admin-invoice-grid strong {
            margin-top: 6px;
            color: #26392e;
        }

        .admin-invoice-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .admin-invoice-table th,
        .admin-invoice-table td {
            padding: 13px;
            border-bottom: 1px solid #e4e8e4;
            text-align: left;
        }

        .admin-invoice-table th {
            background: #183126;
            color: #ffffff;
        }

        .admin-invoice-totals {
            width: 350px;
            max-width: 100%;
            margin-top: 25px;
            margin-left: auto;
        }

        .admin-invoice-totals div {
            padding: 9px 0;
            display: flex;
            justify-content: space-between;
        }

        .admin-invoice-total {
            margin-top: 8px;
            padding-top: 15px !important;
            border-top: 2px solid #dfe4df;
            font-size: 19px;
        }

        @media print {
            .sidebar,
            .admin-invoice-actions {
                display: none !important;
            }

            .main-content {
                padding: 0;
            }

            .content-card,
            .admin-invoice {
                border: none;
            }
        }

        @media (max-width: 700px) {
            .admin-invoice-grid {
                grid-template-columns: 1fr;
            }

            .admin-invoice-header {
                flex-direction: column;
            }

            .admin-invoice-number {
                text-align: left;
            }
        }

    </style>

</head>

<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">

        <div class="content-card">

            <div class="admin-invoice-actions">

                <a
                    href="facturas.php"
                    class="btn btn-secondary"
                >
                    Volver a facturación
                </a>

                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="window.print()"
                >
                    Imprimir
                </button>

            </div>

            <article class="admin-invoice">

                <header class="admin-invoice-header">

                    <div>

                        <h1>Biblioteca Digital</h1>

                        <p>
                            Comprobante de acceso digital
                        </p>

                    </div>

                    <div class="admin-invoice-number">

                        <strong>
                            <?php echo
                                escaparDetalleAdmin(
                                    $factura[
                                        'numero_factura'
                                    ]
                                );
                            ?>
                        </strong>

                        <span>
                            <?php echo fechaDetalleAdmin(
                                $factura[
                                    'fecha_factura'
                                ],
                                true
                            ); ?>
                        </span>

                    </div>

                </header>

                <section class="admin-invoice-grid">

                    <div>

                        <span>Usuario</span>

                        <strong>
                            <?php echo
                                escaparDetalleAdmin(
                                    $factura['usuario']
                                );
                            ?>
                        </strong>

                    </div>

                    <div>

                        <span>Método de pago</span>

                        <strong>
                            <?php echo
                                escaparDetalleAdmin(
                                    $metodosPago[$metodo]
                                    ?? ucfirst($metodo)
                                );
                            ?>
                        </strong>

                    </div>

                    <div>

                        <span>Referencia</span>

                        <strong>
                            <?php echo
                                escaparDetalleAdmin(
                                    $factura[
                                        'referencia_pago'
                                    ]
                                    ?: 'No registrada'
                                );
                            ?>
                        </strong>

                    </div>

                    <div>

                        <span>Estado</span>

                        <strong>
                            <?php echo
                                escaparDetalleAdmin(
                                    ucfirst(
                                        $factura['estado']
                                    )
                                );
                            ?>
                        </strong>

                    </div>

                    <div>

                        <span>Inicio del acceso</span>

                        <strong>
                            <?php echo fechaDetalleAdmin(
                                $factura[
                                    'fecha_inicio'
                                ]
                            ); ?>
                        </strong>

                    </div>

                    <div>

                        <span>Vencimiento</span>

                        <strong>
                            <?php echo fechaDetalleAdmin(
                                $factura[
                                    'fecha_vencimiento'
                                ]
                            ); ?>
                        </strong>

                    </div>

                </section>

                <table class="admin-invoice-table">

                    <thead>

                    <tr>

                        <th>Libro</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>

                    </tr>

                    </thead>

                    <tbody>

                    <tr>

                        <td>

                            <strong>
                                <?php echo
                                    escaparDetalleAdmin(
                                        $factura['titulo']
                                    );
                                ?>
                            </strong>

                            <br>

                            <small>
                                <?php echo
                                    escaparDetalleAdmin(
                                        $factura['autor']
                                    );
                                ?>
                            </small>

                        </td>

                        <td>
                            <?php echo (int)(
                                $factura['cantidad']
                                ?? 1
                            ); ?>
                        </td>

                        <td>
                            $<?php echo number_format(
                                (float)$factura[
                                    'precio_unitario'
                                ],
                                2
                            ); ?>
                        </td>

                        <td>
                            $<?php echo number_format(
                                (float)$factura[
                                    'detalle_subtotal'
                                ],
                                2
                            ); ?>
                        </td>

                    </tr>

                    </tbody>

                </table>

                <section class="admin-invoice-totals">

                    <div>

                        <span>Subtotal</span>

                        <strong>
                            $<?php echo number_format(
                                (float)$factura[
                                    'subtotal'
                                ],
                                2
                            ); ?>
                        </strong>

                    </div>

                    <div>

                        <span>Impuesto</span>

                        <strong>
                            $<?php echo number_format(
                                (float)$factura[
                                    'impuesto'
                                ],
                                2
                            ); ?>
                        </strong>

                    </div>

                    <div class="admin-invoice-total">

                        <span>Total</span>

                        <strong>
                            $<?php echo number_format(
                                (float)$factura['total'],
                                2
                            ); ?>
                        </strong>

                    </div>

                </section>

            </article>

        </div>

    </main>

</div>

</body>

</html>
