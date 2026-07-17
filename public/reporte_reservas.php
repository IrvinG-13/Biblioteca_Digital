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

    <title>
        Reporte de reservas - Biblioteca Digital
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        .reservation-report-header {
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .reservation-report-header h1 {
            margin: 0 0 8px;
        }

        .reservation-report-header p {
            margin: 0;
            color: #647068;
        }

        .reservation-report-filters {
            margin-bottom: 24px;
            padding: 20px;
            border: 1px solid #dfe4df;
            border-radius: 16px;
            background: #ffffff;
            display: grid;
            grid-template-columns:
                repeat(3, minmax(160px, 1fr))
                auto
                auto;
            gap: 14px;
            align-items: end;
        }

        .reservation-report-filters .form-group {
            margin: 0;
        }

        .reservation-report-filters input,
        .reservation-report-filters select {
            width: 100%;
            min-height: 44px;
        }

        .reservation-report-summary {
            margin-bottom: 24px;
            display: grid;
            grid-template-columns:
                repeat(4, minmax(140px, 1fr));
            gap: 15px;
        }

        .reservation-report-summary article {
            padding: 20px;
            border: 1px solid #dfe4df;
            border-radius: 15px;
            background: #ffffff;
        }

        .reservation-report-summary span {
            display: block;
            color: #748078;
            font-size: 12px;
            font-weight: 700;
        }

        .reservation-report-summary strong {
            display: block;
            margin-top: 7px;
            color: #183126;
            font-size: 28px;
        }

        .reservation-report-table-container {
            overflow-x: auto;
            border: 1px solid #dfe4df;
            border-radius: 16px;
            background: #ffffff;
        }

        .reservation-report-table {
            width: 100%;
            min-width: 1150px;
            border-collapse: collapse;
        }

        .reservation-report-table th,
        .reservation-report-table td {
            padding: 13px 14px;
            border-bottom: 1px solid #e8ece8;
            text-align: left;
            vertical-align: top;
            font-size: 13px;
        }

        .reservation-report-table th {
            background: #183126;
            color: #ffffff;
            font-size: 12px;
            white-space: nowrap;
        }

        .reservation-report-table tbody tr:hover {
            background: #f7f8f5;
        }

        .reservation-report-status {
            padding: 6px 10px;
            border-radius: 999px;
            display: inline-block;
            background: #eef1ed;
            color: #3d4c43;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .reservation-report-empty {
            padding: 45px 20px;
            color: #6d7971;
            text-align: center;
        }

        @media (max-width: 1100px) {
            .reservation-report-filters {
                grid-template-columns: repeat(2, 1fr);
            }

            .reservation-report-summary {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 650px) {
            .reservation-report-header {
                flex-direction: column;
            }

            .reservation-report-filters,
            .reservation-report-summary {
                grid-template-columns: 1fr;
            }

            .reservation-report-header .btn {
                width: 100%;
            }
        }

    </style>

</head>

<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">

        <div class="content-card">

            <section class="reservation-report-header">

                <div>

                    <h1>Reporte de reservas</h1>

                    <p>
                        Consulta las reservas registradas y
                        descarga el resultado en Excel.
                    </p>

                </div>

                <a
                    href="<?php echo escaparReporte(
                        $urlExportacion
                    ); ?>"
                    class="btn btn-primary"
                >
                    Exportar a Excel
                </a>

            </section>

            <form
                action="reporte_reservas.php"
                method="GET"
                class="reservation-report-filters"
            >

                <div class="form-group">

                    <label for="fecha_desde">
                        Fecha desde
                    </label>

                    <input
                        id="fecha_desde"
                        type="date"
                        name="fecha_desde"
                        value="<?php echo escaparReporte(
                            $filtros['fecha_desde']
                        ); ?>"
                    >

                </div>

                <div class="form-group">

                    <label for="fecha_hasta">
                        Fecha hasta
                    </label>

                    <input
                        id="fecha_hasta"
                        type="date"
                        name="fecha_hasta"
                        value="<?php echo escaparReporte(
                            $filtros['fecha_hasta']
                        ); ?>"
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

                        <?php foreach ($estados as $valor => $texto): ?>

                            <option
                                value="<?php echo escaparReporte(
                                    $valor
                                ); ?>"
                                <?php echo
                                    $filtros['estado'] === $valor
                                        ? 'selected'
                                        : '';
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
                    class="btn btn-primary"
                >
                    Filtrar
                </button>

                <a
                    href="reporte_reservas.php"
                    class="btn btn-secondary"
                >
                    Limpiar
                </a>

            </form>

            <section class="reservation-report-summary">

                <article>

                    <span>Total de reservas</span>

                    <strong>
                        <?php echo (int)$resumen['total']; ?>
                    </strong>

                </article>

                <article>

                    <span>Reservas activas</span>

                    <strong>
                        <?php echo (int)$resumen['activas']; ?>
                    </strong>

                </article>

                <article>

                    <span>Devueltas</span>

                    <strong>
                        <?php echo (int)$resumen['devueltas']; ?>
                    </strong>

                </article>

                <article>

                    <span>Canceladas</span>

                    <strong>
                        <?php echo (int)$resumen['canceladas']; ?>
                    </strong>

                </article>

            </section>

            <section class="reservation-report-table-container">

                <?php if (empty($reservas)): ?>

                    <div class="reservation-report-empty">

                        No se encontraron reservas con los
                        filtros seleccionados.

                    </div>

                <?php else: ?>

                    <table class="reservation-report-table">

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
                                        $reserva['estudiante_nombre']
                                        ?? ''
                                    )
                                );

                                if ($nombreEstudiante === '') {
                                    $nombreEstudiante =
                                        (string)(
                                            $reserva['usuario']
                                            ?? ''
                                        );
                                }

                                ?>

                                <tr>

                                    <td>
                                        <?php echo (int)$reserva['id']; ?>
                                    </td>

                                    <td>

                                        <strong>
                                            <?php echo escaparReporte(
                                                $nombreEstudiante
                                            ); ?>
                                        </strong>

                                        <br>

                                        <small>
                                            <?php echo escaparReporte(
                                                $reserva['usuario']
                                                ?? ''
                                            ); ?>
                                        </small>

                                    </td>

                                    <td>
                                        <?php echo escaparReporte(
                                            $reserva['cip'] ?? ''
                                        ); ?>
                                    </td>

                                    <td>

                                        <strong>
                                            <?php echo escaparReporte(
                                                $reserva['titulo']
                                                ?? ''
                                            ); ?>
                                        </strong>

                                        <br>

                                        <small>
                                            <?php echo escaparReporte(
                                                $reserva['autor']
                                                ?? ''
                                            ); ?>
                                        </small>

                                    </td>

                                    <td>
                                        <?php echo escaparReporte(
                                            $reserva[
                                                'categoria_nombre'
                                            ] ?? ''
                                        ); ?>
                                    </td>

                                    <td>
                                        <?php echo escaparReporte(
                                            formatearFechaReporte(
                                                $reserva[
                                                    'fecha_reserva'
                                                ] ?? ''
                                            )
                                        ); ?>
                                    </td>

                                    <td>
                                        <?php echo escaparReporte(
                                            formatearFechaReporte(
                                                $reserva[
                                                    'fecha_vencimiento'
                                                ] ?? '',
                                                'Sin fecha límite'
                                            )
                                        ); ?>
                                    </td>

                                    <td>
                                        <?php echo escaparReporte(
                                            formatearFechaReporte(
                                                $reserva[
                                                    'fecha_devolucion'
                                                ] ?? ''
                                            )
                                        ); ?>
                                    </td>

                                    <td>

                                        <span class="reservation-report-status">

                                            <?php echo escaparReporte(
                                                nombreEstadoReporte(
                                                    (string)(
                                                        $reserva[
                                                            'estado'
                                                        ] ?? ''
                                                    )
                                                )
                                            ); ?>

                                        </span>

                                    </td>

                                    <td>
                                        <?php echo escaparReporte(
                                            ($reserva[
                                                'tipo_acceso'
                                            ] ?? 'gratuito') === 'pago'
                                                ? 'Pago'
                                                : 'Gratuito'
                                        ); ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php endif; ?>

            </section>

        </div>

    </main>

</div>

</body>

</html>