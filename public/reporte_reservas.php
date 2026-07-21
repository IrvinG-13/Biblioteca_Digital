<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../app/Core/NoCache.php';
require_once __DIR__ . '/../app/Controllers/ReservaController.php';

NoCache::aplicar();

$controller = new ReservaController();
$datos = $controller->obtenerDatosReporte();

$reservas = $datos['reservas'];
$filtros = $datos['filtros'];
$resumen = $datos['resumen'];
$estados = $datos['estados'];

function escaparReporte(mixed $valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

function formatearFechaReporte(
    mixed $fecha,
    string $vacio = 'No aplica'
): string {
    $fecha = trim((string)$fecha);

    if ($fecha === '') {
        return $vacio;
    }

    try {
        return (new DateTime($fecha))->format('d/m/Y');
    } catch (Throwable $e) {
        return escaparReporte($fecha);
    }
}

function nombreEstadoReporte(string $estado): string
{
    $estadosDisponibles = [
        'reservado' => 'Reservado',
        'en_prestamo' => 'En préstamo',
        'por_vencer' => 'Por vencer',
        'devuelto' => 'Devuelto',
        'cancelado' => 'Cancelado'
    ];

    return $estadosDisponibles[$estado]
        ?? ucfirst(
            str_replace('_', ' ', $estado)
        );
}

$parametrosExportacion = array_filter(
    [
        'fecha_desde' => $filtros['fecha_desde'],
        'fecha_hasta' => $filtros['fecha_hasta'],
        'estado' => $filtros['estado']
    ],
    static fn ($valor) => $valor !== ''
);

$urlExportacion = 'reserva_exportar.php';

if (!empty($parametrosExportacion)) {
    $urlExportacion .=
        '?' .
        http_build_query($parametrosExportacion);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reporte de reservas | ReadPoint</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/reporte_reservas.css?v=2">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-reporte-reservas">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>Reporte de reservas</h1>

                <p>
                    Consulta las reservas registradas y descarga
                    los resultados en formato Excel.
                </p>
            </div>

            <a
                href="<?php echo escaparReporte(
                    $urlExportacion
                ); ?>"
                class="boton-exportar-reservas"
            >
                Exportar a Excel
            </a>

        </section>

        <form
            action="reporte_reservas.php"
            method="GET"
            class="filtros-reporte-reservas"
        >

            <div class="grupo-filtro-reservas">

                <label for="fecha_desde">
                    Fecha desde
                </label>

                <input
                    id="fecha_desde"
                    type="date"
                    name="fecha_desde"
                    value="<?php echo escaparReporte(
                        $filtros["fecha_desde"]
                    ); ?>"
                >

            </div>

            <div class="grupo-filtro-reservas">

                <label for="fecha_hasta">
                    Fecha hasta
                </label>

                <input
                    id="fecha_hasta"
                    type="date"
                    name="fecha_hasta"
                    value="<?php echo escaparReporte(
                        $filtros["fecha_hasta"]
                    ); ?>"
                >

            </div>

            <div class="grupo-filtro-reservas">

                <label for="estado">
                    Estado
                </label>

                <select
                    id="estado"
                    name="estado"
                >

                    <?php foreach ($estados as $valor => $texto): ?>

                        <option
                            value="<?php echo escaparReporte(
                                $valor
                            ); ?>"
                            <?php echo
                                $filtros["estado"] === $valor
                                    ? "selected"
                                    : "";
                            ?>
                        >
                            <?php echo escaparReporte(
                                $texto
                            ); ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <button
                type="submit"
                class="boton-filtrar-reservas"
            >
                Filtrar
            </button>

            <a
                href="reporte_reservas.php"
                class="boton-limpiar-reservas"
            >
                Limpiar
            </a>

        </form>

        <section class="resumen-reporte-reservas">

            <article class="tarjeta-resumen-reservas">

                <span>Total de reservas</span>

                <strong>
                    <?php echo (int)$resumen["total"]; ?>
                </strong>

            </article>

            <article class="tarjeta-resumen-reservas">

                <span>Reservas activas</span>

                <strong>
                    <?php echo (int)$resumen["activas"]; ?>
                </strong>

            </article>

            <article class="tarjeta-resumen-reservas">

                <span>Devueltas</span>

                <strong>
                    <?php echo (int)$resumen["devueltas"]; ?>
                </strong>

            </article>

            <article class="tarjeta-resumen-reservas">

                <span>Canceladas</span>

                <strong>
                    <?php echo (int)$resumen["canceladas"]; ?>
                </strong>

            </article>

        </section>

        <section class="panel-reporte-reservas">

            <?php if (empty($reservas)): ?>

                <div class="estado-vacio-reservas">
                    No se encontraron reservas con los filtros seleccionados.
                </div>

            <?php else: ?>

                <div class="contenedor-tabla-reservas">

                    <table class="tabla-reporte-reservas">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Estudiante</th>
                                <th>CIP</th>
                                <th>Libro</th>
                                <th>Categoría</th>
                                <th>Reserva</th>
                                <th>Vencimiento</th>
                                <th>Devolución</th>
                                <th>Estado</th>
                                <th>Acceso</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($reservas as $reserva): ?>

                            <?php

                            $nombreEstudiante = trim(
                                (string)(
                                    $reserva["estudiante_nombre"]
                                    ?? ""
                                )
                            );

                            if ($nombreEstudiante === "") {
                                $nombreEstudiante =
                                    (string)(
                                        $reserva["usuario"]
                                        ?? ""
                                    );
                            }

                            $estadoReserva = (string)(
                                $reserva["estado"] ?? ""
                            );

                            ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?php echo (int)$reserva["id"]; ?>
                                    </strong>
                                </td>

                                <td>

                                    <strong class="nombre-estudiante-reserva">
                                        <?php echo escaparReporte(
                                            $nombreEstudiante
                                        ); ?>
                                    </strong>

                                    <span class="detalle-reserva">
                                        <?php echo escaparReporte(
                                            $reserva["usuario"] ?? ""
                                        ); ?>
                                    </span>

                                </td>

                                <td>
                                    <?php echo escaparReporte(
                                        $reserva["cip"] ?? ""
                                    ); ?>
                                </td>

                                <td>

                                    <strong class="titulo-libro-reserva">
                                        <?php echo escaparReporte(
                                            $reserva["titulo"] ?? ""
                                        ); ?>
                                    </strong>

                                    <span class="detalle-reserva">
                                        <?php echo escaparReporte(
                                            $reserva["autor"] ?? ""
                                        ); ?>
                                    </span>

                                </td>

                                <td>
                                    <span class="etiqueta-categoria-reserva">
                                        <?php echo escaparReporte(
                                            $reserva["categoria_nombre"]
                                            ?? ""
                                        ); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php echo escaparReporte(
                                        formatearFechaReporte(
                                            $reserva["fecha_reserva"] ?? ""
                                        )
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo escaparReporte(
                                        formatearFechaReporte(
                                            $reserva["fecha_vencimiento"] ?? "",
                                            "Sin fecha límite"
                                        )
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo escaparReporte(
                                        formatearFechaReporte(
                                            $reserva["fecha_devolucion"] ?? ""
                                        )
                                    ); ?>
                                </td>

                                <td>

                                    <span
                                        class="estado-reserva <?php echo escaparReporte(
                                            $estadoReserva
                                        ); ?>"
                                    >
                                        <?php echo escaparReporte(
                                            nombreEstadoReporte(
                                                $estadoReserva
                                            )
                                        ); ?>
                                    </span>

                                </td>

                                <td>

                                    <span class="tipo-acceso-reserva">
                                        <?php echo escaparReporte(
                                            (
                                                $reserva["tipo_acceso"]
                                                ?? "gratuito"
                                            ) === "pago"
                                                ? "Pago"
                                                : "Gratuito"
                                        ); ?>
                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </section>

    </main>

</div>

</body>

</html>