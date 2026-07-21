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

    <title>Facturación | ReadPoint</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/facturas.css?v=2">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-facturacion">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>Facturación</h1>

                <p>
                    Consulta las compras de acceso digital realizadas por los usuarios.
                </p>
            </div>

        </section>

        <section class="resumen-facturacion">

            <article class="tarjeta-resumen-factura">

                <span>Total de facturas</span>

                <strong>
                    <?php echo (int)(
                        $resumen["total_facturas"] ?? 0
                    ); ?>
                </strong>

            </article>

            <article class="tarjeta-resumen-factura">

                <span>Facturas pagadas</span>

                <strong>
                    <?php echo (int)(
                        $resumen["facturas_pagadas"] ?? 0
                    ); ?>
                </strong>

            </article>

            <article class="tarjeta-resumen-factura">

                <span>Facturas pendientes</span>

                <strong>
                    <?php echo (int)(
                        $resumen["facturas_pendientes"] ?? 0
                    ); ?>
                </strong>

            </article>

            <article class="tarjeta-resumen-factura">

                <span>Ingresos registrados</span>

                <strong>
                    $<?php echo number_format(
                        (float)(
                            $resumen["total_ingresos"] ?? 0
                        ),
                        2
                    ); ?>
                </strong>

            </article>

        </section>

        <section class="panel-facturacion">

            <form
                action="facturas.php"
                method="GET"
                class="filtros-facturacion"
            >

                <div class="grupo-filtro-facturacion">

                    <label for="buscar">
                        Buscar
                    </label>

                    <input
                        type="text"
                        id="buscar"
                        name="buscar"
                        maxlength="100"
                        value="<?php echo escaparFacturaAdmin(
                            $filtros["buscar"]
                        ); ?>"
                        placeholder="Factura, usuario o libro"
                    >

                </div>

                <div class="grupo-filtro-facturacion">

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

                        <?php foreach ($estados as $valor => $texto): ?>

                            <option
                                value="<?php echo escaparFacturaAdmin(
                                    $valor
                                ); ?>"
                                <?php echo
                                    $filtros["estado"] === $valor
                                        ? "selected"
                                        : "";
                                ?>
                            >
                                <?php echo escaparFacturaAdmin(
                                    $texto
                                ); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="grupo-filtro-facturacion">

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

                        <?php foreach ($metodosPago as $valor => $texto): ?>

                            <option
                                value="<?php echo escaparFacturaAdmin(
                                    $valor
                                ); ?>"
                                <?php echo
                                    $filtros["metodo_pago"] === $valor
                                        ? "selected"
                                        : "";
                                ?>
                            >
                                <?php echo escaparFacturaAdmin(
                                    $texto
                                ); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <button
                    type="submit"
                    class="boton-filtrar-facturas"
                >
                    Filtrar
                </button>

                <a
                    href="facturas.php"
                    class="boton-limpiar-facturas"
                >
                    Limpiar
                </a>

            </form>

            <div class="contenedor-tabla-facturacion">

                <?php if (empty($facturas)): ?>

                    <div class="estado-vacio-facturacion">
                        No se encontraron facturas.
                    </div>

                <?php else: ?>

                    <table class="tabla-facturacion">

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

                        <?php foreach ($facturas as $factura): ?>

                            <?php

                            $metodo =
                                $factura["metodo_pago"] ?? "";

                            $estado =
                                $factura["estado"] ?? "pagada";

                            ?>

                            <tr>

                                <td>
                                    <strong class="numero-factura">
                                        <?php echo escaparFacturaAdmin(
                                            $factura["numero_factura"] ?? ""
                                        ); ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="fecha-factura">
                                        <?php echo fechaFacturaAdmin(
                                            $factura["fecha_factura"] ?? null
                                        ); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php echo escaparFacturaAdmin(
                                        $factura["usuario"] ?? ""
                                    ); ?>
                                </td>

                                <td>

                                    <strong class="titulo-libro-factura">
                                        <?php echo escaparFacturaAdmin(
                                            $factura["titulo"] ?? ""
                                        ); ?>
                                    </strong>

                                    <span class="autor-libro-factura">
                                        <?php echo escaparFacturaAdmin(
                                            $factura["autor"] ?? ""
                                        ); ?>
                                    </span>

                                </td>

                                <td>
                                    <?php echo escaparFacturaAdmin(
                                        $metodosPago[$metodo]
                                            ?? ucfirst($metodo)
                                    ); ?>
                                </td>

                                <td>
                                    <strong class="total-factura">
                                        $<?php echo number_format(
                                            (float)(
                                                $factura["total"] ?? 0
                                            ),
                                            2
                                        ); ?>
                                    </strong>
                                </td>

                                <td>

                                    <span
                                        class="estado-factura <?php echo escaparFacturaAdmin(
                                            $estado
                                        ); ?>"
                                    >
                                        <?php echo escaparFacturaAdmin(
                                            $estados[$estado]
                                                ?? ucfirst($estado)
                                        ); ?>
                                    </span>

                                </td>

                                <td>

                                    <a
                                        class="accion-ver-factura"
                                        href="factura_admin_detalle.php?id=<?php echo (int)$factura["id"]; ?>"
                                    >
                                        Ver detalle
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php endif; ?>

            </div>

        </section>

    </main>

</div>

</body>
</html>