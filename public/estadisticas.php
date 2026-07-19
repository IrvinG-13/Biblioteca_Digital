<?php

require_once __DIR__
    . '/../app/Core/NoCache.php';

require_once __DIR__
    . '/../app/Controllers/EstadisticaController.php';

NoCache::aplicar();

/*
|--------------------------------------------------------------------------
| Obtener información estadística
|--------------------------------------------------------------------------
*/

$controller = new EstadisticaController();

$datos = $controller->obtenerEstadisticas();

$resumen = $datos['resumen'];

$librosEstudiantes =
    $datos['libros_estudiantes'];

$librosProfesores =
    $datos['libros_profesores'];

$reservasPorDia =
    $datos['reservas_por_dia'];

$filtros = $datos['filtros'];

$error = $datos['error'];

/*
|--------------------------------------------------------------------------
| Funciones de presentación
|--------------------------------------------------------------------------
*/

function escaparEstadistica(
    mixed $valor
): string {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

function fechaVisibleEstadistica(
    ?string $fecha
): string {
    if (
        $fecha === null
        || trim($fecha) === ''
    ) {
        return 'No disponible';
    }

    try {
        return (new DateTimeImmutable($fecha))
            ->format('d/m/Y');
    } catch (Throwable $e) {
        return escaparEstadistica($fecha);
    }
}

/*
|--------------------------------------------------------------------------
| Obtener valores máximos para las gráficas
|--------------------------------------------------------------------------
|
| Se utilizan para calcular el ancho proporcional
| de cada barra sin depender de librerías externas.
|
*/

$maximoEstudiantes = 0;

foreach ($librosEstudiantes as $libro) {
    $cantidad = (int)(
        $libro['total_reservas'] ?? 0
    );

    if ($cantidad > $maximoEstudiantes) {
        $maximoEstudiantes = $cantidad;
    }
}

$maximoProfesores = 0;

foreach ($librosProfesores as $libro) {
    $cantidad = (int)(
        $libro['total_reservas'] ?? 0
    );

    if ($cantidad > $maximoProfesores) {
        $maximoProfesores = $cantidad;
    }
}

/*
|--------------------------------------------------------------------------
| Totales del periodo diario
|--------------------------------------------------------------------------
*/

$totalPeriodoEstudiantes = 0;
$totalPeriodoProfesores = 0;

foreach ($reservasPorDia as $registro) {
    $totalPeriodoEstudiantes += (int)(
        $registro['estudiantes'] ?? 0
    );

    $totalPeriodoProfesores += (int)(
        $registro['profesores'] ?? 0
    );
}

$totalComparativo =
    $totalPeriodoEstudiantes
    + $totalPeriodoProfesores;

$porcentajeEstudiantes =
    $totalComparativo > 0
        ? round(
            (
                $totalPeriodoEstudiantes
                / $totalComparativo
            ) * 100,
            1
        )
        : 0;

$porcentajeProfesores =
    $totalComparativo > 0
        ? round(
            (
                $totalPeriodoProfesores
                / $totalComparativo
            ) * 100,
            1
        )
        : 0;

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
        Estadísticas - Biblioteca Digital
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        /* =========================================================
           ENCABEZADO
        ========================================================= */

        .statistics-header {
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 25px;
        }

        .statistics-header h1 {
            margin: 0 0 8px;
            color: #17382b;
        }

        .statistics-header p {
            max-width: 720px;
            margin: 0;
            color: #68736c;
            line-height: 1.6;
        }

        .statistics-period-label {
            padding: 10px 15px;
            border-radius: 999px;
            background: #eef3ee;
            color: #345043;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* =========================================================
           MENSAJE DE ERROR
        ========================================================= */

        .statistics-alert {
            margin-bottom: 22px;
            padding: 16px 19px;
            border: 1px solid #e3b7b7;
            border-radius: 12px;
            background: #fff1f1;
            color: #8c2929;
            font-size: 14px;
        }

        /* =========================================================
           FILTRO DE FECHAS
        ========================================================= */

        .statistics-filter {
            margin-bottom: 25px;
            padding: 22px;
            border: 1px solid #dfe5df;
            border-radius: 15px;
            background: #ffffff;
            display: grid;
            grid-template-columns:
                minmax(180px, 1fr)
                minmax(180px, 1fr)
                auto
                auto;
            gap: 15px;
            align-items: end;
        }

        .statistics-filter .form-group {
            margin: 0;
        }

        .statistics-filter label {
            margin-bottom: 7px;
            display: block;
            color: #293d32;
            font-size: 13px;
            font-weight: 700;
        }

        .statistics-filter input {
            width: 100%;
            min-height: 44px;
            padding: 0 13px;
            border: 1px solid #cfd8d1;
            border-radius: 9px;
            background: #ffffff;
            color: #26392e;
        }

        /* =========================================================
           TARJETAS DEL RESUMEN
        ========================================================= */

        .statistics-summary {
            margin-bottom: 25px;
            display: grid;
            grid-template-columns:
                repeat(4, minmax(150px, 1fr));
            gap: 16px;
        }

        .statistics-summary-card {
            padding: 21px;
            border: 1px solid #dfe5df;
            border-radius: 15px;
            background: #ffffff;
            box-shadow:
                0 8px 22px
                rgba(24, 49, 38, 0.05);
        }

        .statistics-summary-card span {
            color: #748078;
            font-size: 12px;
            font-weight: 700;
        }

        .statistics-summary-card strong {
            margin-top: 8px;
            display: block;
            color: #183b2c;
            font-size: 29px;
        }

        .statistics-summary-card small {
            margin-top: 5px;
            display: block;
            color: #8a948e;
            font-size: 11px;
        }

        /* =========================================================
           COMPARACIÓN GENERAL
        ========================================================= */

        .statistics-comparison {
            margin-bottom: 25px;
            padding: 24px;
            border: 1px solid #dfe5df;
            border-radius: 15px;
            background: #ffffff;
        }

        .statistics-section-heading {
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .statistics-section-heading h2 {
            margin: 0 0 5px;
            color: #1d382b;
            font-size: 20px;
        }

        .statistics-section-heading p {
            margin: 0;
            color: #758078;
            font-size: 13px;
        }

        .comparison-bars {
            display: flex;
            flex-direction: column;
            gap: 17px;
        }

        .comparison-row {
            display: grid;
            grid-template-columns: 115px 1fr 90px;
            gap: 15px;
            align-items: center;
        }

        .comparison-label {
            color: #30443a;
            font-size: 13px;
            font-weight: 700;
        }

        .comparison-track {
            height: 18px;
            overflow: hidden;
            border-radius: 999px;
            background: #edf1ed;
        }

        .comparison-fill {
            min-width: 0;
            height: 100%;
            border-radius: inherit;
        }

        .comparison-fill.students {
            background: #315f49;
        }

        .comparison-fill.teachers {
            background: #a9782d;
        }

        .comparison-value {
            color: #34483d;
            text-align: right;
            font-size: 13px;
            font-weight: 700;
        }

        /* =========================================================
           GRÁFICAS
        ========================================================= */

        .statistics-charts {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .statistics-chart-card {
            min-width: 0;
            padding: 24px;
            border: 1px solid #dfe5df;
            border-radius: 16px;
            background: #ffffff;
            box-shadow:
                0 8px 22px
                rgba(24, 49, 38, 0.05);
        }

        .chart-list {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 17px;
        }

        .chart-item {
            display: grid;
            grid-template-columns:
                minmax(130px, 0.9fr)
                minmax(150px, 1.5fr)
                42px;
            gap: 13px;
            align-items: center;
        }

        .chart-book {
            min-width: 0;
        }

        .chart-book strong,
        .chart-book small {
            display: block;
        }

        .chart-book strong {
            overflow: hidden;
            color: #2c4035;
            font-size: 12px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .chart-book small {
            margin-top: 4px;
            overflow: hidden;
            color: #838d86;
            font-size: 10px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .chart-track {
            height: 16px;
            overflow: hidden;
            border-radius: 999px;
            background: #edf1ed;
        }

        .chart-fill {
            min-width: 4px;
            height: 100%;
            border-radius: inherit;
        }

        .chart-fill.students {
            background: #315f49;
        }

        .chart-fill.teachers {
            background: #a9782d;
        }

        .chart-number {
            color: #263b30;
            text-align: right;
            font-size: 13px;
            font-weight: 800;
        }

        .statistics-empty-chart {
            padding: 40px 20px;
            border: 1px dashed #ccd5ce;
            border-radius: 12px;
            color: #7b867f;
            text-align: center;
            font-size: 13px;
        }

        /* =========================================================
           TABLA DEL PERIODO
        ========================================================= */

        .statistics-daily {
            margin-top: 25px;
            padding: 24px;
            border: 1px solid #dfe5df;
            border-radius: 16px;
            background: #ffffff;
        }

        .statistics-table-wrapper {
            margin-top: 18px;
            overflow-x: auto;
            border: 1px solid #e0e6e1;
            border-radius: 12px;
        }

        .statistics-table {
            width: 100%;
            min-width: 620px;
            border-collapse: collapse;
        }

        .statistics-table th,
        .statistics-table td {
            padding: 13px 15px;
            border-bottom: 1px solid #e5eae6;
            text-align: left;
            font-size: 13px;
        }

        .statistics-table th {
            background: #18382b;
            color: #ffffff;
            font-size: 12px;
        }

        .statistics-table tbody tr:hover {
            background: #f7f9f7;
        }

        .statistics-table td strong {
            color: #263c31;
        }

        /* =========================================================
           IMPRESIÓN
        ========================================================= */

        @media print {
            .sidebar,
            .statistics-filter {
                display: none !important;
            }

            .main-content {
                padding: 0;
            }

            .content-card {
                border: none;
                box-shadow: none;
            }
        }

        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 1100px) {
            .statistics-summary {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .statistics-charts {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 800px) {
            .statistics-header {
                flex-direction: column;
            }

            .statistics-filter {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .chart-item {
                grid-template-columns: 1fr;
            }

            .chart-number {
                text-align: left;
            }
        }

        @media (max-width: 550px) {
            .statistics-summary,
            .statistics-filter {
                grid-template-columns: 1fr;
            }

            .comparison-row {
                grid-template-columns: 1fr;
            }

            .comparison-value {
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

            <!-- Encabezado -->

            <section class="statistics-header">

                <div>

                    <h1>Estadísticas de uso</h1>

                    <p>
                        Analiza los libros más reservados por
                        estudiantes y profesores dentro del
                        periodo seleccionado.
                    </p>

                </div>

                <span class="statistics-period-label">

                    <?php echo fechaVisibleEstadistica(
                        $filtros['fecha_inicio']
                    ); ?>

                    —

                    <?php echo fechaVisibleEstadistica(
                        $filtros['fecha_fin']
                    ); ?>

                </span>

            </section>

            <?php if ($error !== ''): ?>

                <div class="statistics-alert">

                    <?php echo escaparEstadistica(
                        $error
                    ); ?>

                </div>

            <?php endif; ?>

            <!-- Selector del periodo -->

            <form
                action="estadisticas.php"
                method="GET"
                class="statistics-filter"
            >

                <div class="form-group">

                    <label for="fecha_inicio">
                        Fecha inicial
                    </label>

                    <input
                        type="date"
                        id="fecha_inicio"
                        name="fecha_inicio"
                        value="<?php echo
                            escaparEstadistica(
                                $filtros['fecha_inicio']
                            );
                        ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="fecha_fin">
                        Fecha final
                    </label>

                    <input
                        type="date"
                        id="fecha_fin"
                        name="fecha_fin"
                        value="<?php echo
                            escaparEstadistica(
                                $filtros['fecha_fin']
                            );
                        ?>"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Consultar
                </button>

                <a
                    href="estadisticas.php"
                    class="btn btn-secondary"
                >
                    Periodo actual
                </a>

            </form>

            <!-- Resumen -->

            <section class="statistics-summary">

                <article class="statistics-summary-card">

                    <span>Total de reservas</span>

                    <strong>

                        <?php echo (int)(
                            $resumen['total_reservas']
                            ?? 0
                        ); ?>

                    </strong>

                    <small>
                        Dentro del periodo
                    </small>

                </article>

                <article class="statistics-summary-card">

                    <span>Reservas de estudiantes</span>

                    <strong>

                        <?php echo (int)(
                            $resumen[
                                'reservas_estudiantes'
                            ]
                            ?? 0
                        ); ?>

                    </strong>

                    <small>
                        Uso estudiantil
                    </small>

                </article>

                <article class="statistics-summary-card">

                    <span>Reservas de profesores</span>

                    <strong>

                        <?php echo (int)(
                            $resumen[
                                'reservas_profesores'
                            ]
                            ?? 0
                        ); ?>

                    </strong>

                    <small>
                        Uso docente
                    </small>

                </article>

                <article class="statistics-summary-card">

                    <span>Libros utilizados</span>

                    <strong>

                        <?php echo (int)(
                            $resumen[
                                'libros_utilizados'
                            ]
                            ?? 0
                        ); ?>

                    </strong>

                    <small>
                        Títulos diferentes
                    </small>

                </article>

            </section>

            <!-- Comparación general -->

            <section class="statistics-comparison">

                <div class="statistics-section-heading">

                    <div>

                        <h2>
                            Distribución de reservas
                        </h2>

                        <p>
                            Comparación general entre estudiantes
                            y profesores.
                        </p>

                    </div>

                </div>

                <div class="comparison-bars">

                    <div class="comparison-row">

                        <span class="comparison-label">
                            Estudiantes
                        </span>

                        <div class="comparison-track">

                            <div
                                class="comparison-fill students"
                                style="width: <?php
                                echo escaparEstadistica(
                                    $porcentajeEstudiantes
                                );
                                ?>%;"
                            ></div>

                        </div>

                        <span class="comparison-value">

                            <?php echo escaparEstadistica(
                                $porcentajeEstudiantes
                            ); ?>%

                        </span>

                    </div>

                    <div class="comparison-row">

                        <span class="comparison-label">
                            Profesores
                        </span>

                        <div class="comparison-track">

                            <div
                                class="comparison-fill teachers"
                                style="width: <?php
                                echo escaparEstadistica(
                                    $porcentajeProfesores
                                );
                                ?>%;"
                            ></div>

                        </div>

                        <span class="comparison-value">

                            <?php echo escaparEstadistica(
                                $porcentajeProfesores
                            ); ?>%

                        </span>

                    </div>

                </div>

            </section>

            <!-- Gráficas -->

            <section class="statistics-charts">

                <!-- Estudiantes -->

                <article class="statistics-chart-card">

                    <div class="statistics-section-heading">

                        <div>

                            <h2>
                                Más reservados por estudiantes
                            </h2>

                            <p>
                                Diez libros con mayor uso
                                estudiantil.
                            </p>

                        </div>

                    </div>

                    <?php if (
                        empty($librosEstudiantes)
                    ): ?>

                        <div class="statistics-empty-chart">

                            No existen reservas de estudiantes
                            dentro del periodo seleccionado.

                        </div>

                    <?php else: ?>

                        <div class="chart-list">

                            <?php foreach (
                                $librosEstudiantes as $libro
                            ): ?>

                                <?php

                                $cantidad = (int)(
                                    $libro[
                                        'total_reservas'
                                    ]
                                    ?? 0
                                );

                                $porcentaje =
                                    $maximoEstudiantes > 0
                                        ? (
                                            $cantidad
                                            / $maximoEstudiantes
                                        ) * 100
                                        : 0;

                                ?>

                                <div class="chart-item">

                                    <div class="chart-book">

                                        <strong
                                            title="<?php echo
                                                escaparEstadistica(
                                                    $libro['titulo']
                                                    ?? ''
                                                );
                                            ?>"
                                        >
                                            <?php echo
                                                escaparEstadistica(
                                                    $libro['titulo']
                                                    ?? 'Libro'
                                                );
                                            ?>
                                        </strong>

                                        <small>
                                            <?php echo
                                                escaparEstadistica(
                                                    $libro['autor']
                                                    ?? 'Autor no registrado'
                                                );
                                            ?>
                                        </small>

                                    </div>

                                    <div class="chart-track">

                                        <div
                                            class="chart-fill students"
                                            style="width: <?php
                                            echo escaparEstadistica(
                                                round(
                                                    $porcentaje,
                                                    2
                                                )
                                            );
                                            ?>%;"
                                        ></div>

                                    </div>

                                    <span class="chart-number">

                                        <?php echo $cantidad; ?>

                                    </span>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </article>

                <!-- Profesores -->

                <article class="statistics-chart-card">

                    <div class="statistics-section-heading">

                        <div>

                            <h2>
                                Más reservados por profesores
                            </h2>

                            <p>
                                Diez libros con mayor uso docente.
                            </p>

                        </div>

                    </div>

                    <?php if (
                        empty($librosProfesores)
                    ): ?>

                        <div class="statistics-empty-chart">

                            No existen reservas de profesores
                            dentro del periodo seleccionado.

                        </div>

                    <?php else: ?>

                        <div class="chart-list">

                            <?php foreach (
                                $librosProfesores as $libro
                            ): ?>

                                <?php

                                $cantidad = (int)(
                                    $libro[
                                        'total_reservas'
                                    ]
                                    ?? 0
                                );

                                $porcentaje =
                                    $maximoProfesores > 0
                                        ? (
                                            $cantidad
                                            / $maximoProfesores
                                        ) * 100
                                        : 0;

                                ?>

                                <div class="chart-item">

                                    <div class="chart-book">

                                        <strong
                                            title="<?php echo
                                                escaparEstadistica(
                                                    $libro['titulo']
                                                    ?? ''
                                                );
                                            ?>"
                                        >
                                            <?php echo
                                                escaparEstadistica(
                                                    $libro['titulo']
                                                    ?? 'Libro'
                                                );
                                            ?>
                                        </strong>

                                        <small>
                                            <?php echo
                                                escaparEstadistica(
                                                    $libro['autor']
                                                    ?? 'Autor no registrado'
                                                );
                                            ?>
                                        </small>

                                    </div>

                                    <div class="chart-track">

                                        <div
                                            class="chart-fill teachers"
                                            style="width: <?php
                                            echo escaparEstadistica(
                                                round(
                                                    $porcentaje,
                                                    2
                                                )
                                            );
                                            ?>%;"
                                        ></div>

                                    </div>

                                    <span class="chart-number">

                                        <?php echo $cantidad; ?>

                                    </span>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </article>

            </section>

            <!-- Tabla por día -->

            <section class="statistics-daily">

                <div class="statistics-section-heading">

                    <div>

                        <h2>
                            Actividad por día
                        </h2>

                        <p>
                            Cantidad de reservas registradas
                            diariamente durante el periodo.
                        </p>

                    </div>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="window.print()"
                    >
                        Imprimir
                    </button>

                </div>

                <?php if (
                    empty($reservasPorDia)
                ): ?>

                    <div class="statistics-empty-chart">

                        No existen movimientos durante el
                        periodo seleccionado.

                    </div>

                <?php else: ?>

                    <div class="statistics-table-wrapper">

                        <table class="statistics-table">

                            <thead>

                            <tr>

                                <th>Fecha</th>
                                <th>Estudiantes</th>
                                <th>Profesores</th>
                                <th>Total diario</th>

                            </tr>

                            </thead>

                            <tbody>

                            <?php foreach (
                                $reservasPorDia as $registro
                            ): ?>

                                <?php

                                $estudiantes = (int)(
                                    $registro[
                                        'estudiantes'
                                    ]
                                    ?? 0
                                );

                                $profesores = (int)(
                                    $registro[
                                        'profesores'
                                    ]
                                    ?? 0
                                );

                                ?>

                                <tr>

                                    <td>

                                        <strong>

                                            <?php echo
                                                fechaVisibleEstadistica(
                                                    $registro[
                                                        'fecha_reserva'
                                                    ]
                                                    ?? null
                                                );
                                            ?>

                                        </strong>

                                    </td>

                                    <td>
                                        <?php echo $estudiantes; ?>
                                    </td>

                                    <td>
                                        <?php echo $profesores; ?>
                                    </td>

                                    <td>

                                        <strong>
                                            <?php echo
                                                $estudiantes
                                                + $profesores;
                                            ?>
                                        </strong>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </section>

        </div>

    </main>

</div>

</body>

</html>