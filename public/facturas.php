<?php

require_once __DIR__
    . '/../app/Core/NoCache.php';

require_once __DIR__
    . '/../app/Controllers/FacturaAdminController.php';

NoCache::aplicar();

$controller =
    new FacturaAdminController();

$datos = $controller->obtenerListado();

$facturas = $datos['facturas'];
$resumen = $datos['resumen'];
$filtros = $datos['filtros'];

function escaparFacturaAdmin(
    mixed $valor
): string {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

function fechaFacturaAdmin(
    ?string $fecha
): string {
    if (
        $fecha === null
        || trim($fecha) === ''
    ) {
        return 'No disponible';
    }

    try {
        return (new DateTime($fecha))
            ->format('d/m/Y h:i A');
    } catch (Throwable $e) {
        return escaparFacturaAdmin($fecha);
    }
}

$metodosPago = [
    'yappy' => 'Yappy',
    'tarjeta' => 'Tarjeta',
    'transferencia' =>
        'Transferencia bancaria'
];

$estados = [
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
        Facturación - Administración
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        .billing-header {
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .billing-header h1 {
            margin: 0 0 8px;
        }

        .billing-header p {
            margin: 0;
            color: #667169;
        }

        .billing-summary {
            margin-bottom: 24px;
            display: grid;
            grid-template-columns:
                repeat(4, minmax(150px, 1fr));
            gap: 15px;
        }

        .billing-summary article {
            padding: 20px;
            border: 1px solid #dfe4df;
            border-radius: 14px;
            background: #ffffff;
        }

        .billing-summary span {
            color: #748078;
            font-size: 12px;
            font-weight: bold;
        }

        .billing-summary strong {
            margin-top: 8px;
            display: block;
            color: #183126;
            font-size: 27px;
        }

        .billing-filters {
            margin-bottom: 24px;
            padding: 20px;
            border: 1px solid #dfe4df;
            border-radius: 14px;
            background: #ffffff;
            display: grid;
            grid-template-columns:
                minmax(200px, 1.5fr)
                minmax(150px, 1fr)
                minmax(170px, 1fr)
                auto
                auto;
            gap: 14px;
            align-items: end;
        }

        .billing-filters .form-group {
            margin: 0;
        }

        .billing-filters input,
        .billing-filters select {
            min-height: 43px;
        }

        .billing-table-container {
            overflow-x: auto;
            border: 1px solid #dfe4df;
            border-radius: 14px;
        }

        .billing-table {
            width: 100%;
            min-width: 1050px;
            border-collapse: collapse;
        }

        .billing-table th,
        .billing-table td {
            padding: 13px 14px;
            border-bottom: 1px solid #e5e9e5;
            text-align: left;
            font-size: 13px;
        }

        .billing-table th {
            background: #183126;
            color: #ffffff;
            font-size: 12px;
        }

        .billing-table tbody tr:hover {
            background: #f7f8f5;
        }

        .billing-status {
            padding: 6px 10px;
            border-radius: 999px;
            display: inline-block;
            font-size: 11px;
            font-weight: bold;
        }

        .billing-status.pagada {
            background: #dcfce7;
            color: #166534;
        }

        .billing-status.pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        .billing-status.anulada {
            background: #fee2e2;
            color: #991b1b;
        }

        .billing-empty {
            padding: 45px 20px;
            color: #6d7971;
            text-align: center;
        }

        @media (max-width: 1100px) {
            .billing-summary {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .billing-filters {
                grid-template-columns:
                    repeat(2, 1fr);
            }
        }

        @media (max-width: 650px) {
            .billing-summary,
            .billing-filters {
                grid-template-columns: 1fr;
            }
        }

    </style>

</head>

<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">

        <div class="content-card">

            <section class="billing-header">

                <div>

                    <h1>Facturación</h1>

                    <p>
                        Consulta las compras de acceso digital
                        realizadas por los usuarios.
                    </p>

                </div>

            </section>

            <section class="billing-summary">

                <article>

                    <span>Total de facturas</span>

                    <strong>
                        <?php echo (int)(
                            $resumen['total_facturas']
                            ?? 0
                        ); ?>
                    </strong>

                </article>

                <article>

                    <span>Facturas pagadas</span>

                    <strong>
                        <?php echo (int)(
                            $resumen['facturas_pagadas']
                            ?? 0
                        ); ?>
                    </strong>

                </article>

                <article>

                    <span>Facturas pendientes</span>

                    <strong>
                        <?php echo (int)(
                            $resumen['facturas_pendientes']
                            ?? 0
                        ); ?>
                    </strong>

                </article>

                <article>

                    <span>Ingresos registrados</span>

                    <strong>
                        $<?php echo number_format(
                            (float)(
                                $resumen['total_ingresos']
                                ?? 0
                            ),
                            2
                        ); ?>
                    </strong>

                </article>

            </section>

            <form
                action="facturas.php"
                method="GET"
                class="billing-filters"
            >

                <div class="form-group">

                    <label for="buscar">
                        Buscar
                    </label>

                    <input
                        type="text"
                        id="buscar"
                        name="buscar"
                        maxlength="100"
                        value="<?php echo
                            escaparFacturaAdmin(
                                $filtros['buscar']
                            );
                        ?>"
                        placeholder="Factura, usuario o libro"
                    >

                </div>

                <div class="form-group">

                    <label for="estado">
                        Estado
                    </label>

                    <select
                        id="estado"
                        name="estado"
                    >

                        <option value="">
                            Todos
                        </option>

                        <?php foreach (
                            $estados as
                            $valor => $texto
                        ): ?>

                            <option
                                value="<?php echo
                                    escaparFacturaAdmin(
                                        $valor
                                    );
                                ?>"
                                <?php echo
                                    $filtros['estado']
                                    === $valor
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                <?php echo
                                    escaparFacturaAdmin(
                                        $texto
                                    );
                                ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="form-group">

                    <label for="metodo_pago">
                        Método de pago
                    </label>

                    <select
                        id="metodo_pago"
                        name="metodo_pago"
                    >

                        <option value="">
                            Todos
                        </option>

                        <?php foreach (
                            $metodosPago as
                            $valor => $texto
                        ): ?>

                            <option
                                value="<?php echo
                                    escaparFacturaAdmin(
                                        $valor
                                    );
                                ?>"
                                <?php echo
                                    $filtros['metodo_pago']
                                    === $valor
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                <?php echo
                                    escaparFacturaAdmin(
                                        $texto
                                    );
                                ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Filtrar
                </button>

                <a
                    href="facturas.php"
                    class="btn btn-secondary"
                >
                    Limpiar
                </a>

            </form>

            <div class="billing-table-container">

                <?php if (empty($facturas)): ?>

                    <div class="billing-empty">

                        No se encontraron facturas.

                    </div>

                <?php else: ?>

                    <table class="billing-table">

                        <thead>

                        <tr>

                            <th>Factura</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Libro</th>
                            <th>Método</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acción</th>

                        </tr>

                        </thead>

                        <tbody>

                        <?php foreach (
                            $facturas as $factura
                        ): ?>

                            <?php

                            $metodo =
                                $factura['metodo_pago']
                                ?? '';

                            $estado =
                                $factura['estado']
                                ?? 'pagada';

                            ?>

                            <tr>

                                <td>

                                    <strong>
                                        <?php echo
                                            escaparFacturaAdmin(
                                                $factura[
                                                    'numero_factura'
                                                ]
                                                ?? ''
                                            );
                                        ?>
                                    </strong>

                                </td>

                                <td>
                                    <?php echo
                                        fechaFacturaAdmin(
                                            $factura[
                                                'fecha_factura'
                                            ]
                                            ?? null
                                        );
                                    ?>
                                </td>

                                <td>
                                    <?php echo
                                        escaparFacturaAdmin(
                                            $factura['usuario']
                                            ?? ''
                                        );
                                    ?>
                                </td>

                                <td>

                                    <strong>
                                        <?php echo
                                            escaparFacturaAdmin(
                                                $factura['titulo']
                                                ?? ''
                                            );
                                        ?>
                                    </strong>

                                    <br>

                                    <small>
                                        <?php echo
                                            escaparFacturaAdmin(
                                                $factura['autor']
                                                ?? ''
                                            );
                                        ?>
                                    </small>

                                </td>

                                <td>
                                    <?php echo
                                        escaparFacturaAdmin(
                                            $metodosPago[
                                                $metodo
                                            ]
                                            ?? ucfirst($metodo)
                                        );
                                    ?>
                                </td>

                                <td>

                                    <strong>
                                        $<?php echo number_format(
                                            (float)(
                                                $factura['total']
                                                ?? 0
                                            ),
                                            2
                                        ); ?>
                                    </strong>

                                </td>

                                <td>

                                    <span
                                        class="billing-status <?php
                                        echo escaparFacturaAdmin(
                                            $estado
                                        );
                                        ?>"
                                    >
                                        <?php echo
                                            escaparFacturaAdmin(
                                                $estados[$estado]
                                                ?? ucfirst($estado)
                                            );
                                        ?>
                                    </span>

                                </td>

                                <td>

                                    <a
                                        href="factura_admin_detalle.php?id=<?php
                                        echo (int)$factura['id'];
                                        ?>"
                                        class="btn btn-link"
                                    >
                                        Ver
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php endif; ?>

            </div>

        </div>

    </main>

</div>

</body>

</html>