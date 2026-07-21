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

$resumen = $datos['resumen'] ?? [];

$librosEstudiantes =
    $datos['libros_estudiantes'] ?? [];

$librosProfesores =
    $datos['libros_profesores'] ?? [];

$reservasPorDia =
    $datos['reservas_por_dia'] ?? [];

$filtros = $datos['filtros'] ?? [
    'fecha_inicio' => '',
    'fecha_fin' => ''
];

$error = (string)($datos['error'] ?? '');

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
| Totales comparativos
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

/*
|--------------------------------------------------------------------------
| Datos para Chart.js
|--------------------------------------------------------------------------
*/

$etiquetasEstudiantes = [];
$valoresEstudiantes = [];

foreach ($librosEstudiantes as $libro) {
    $etiquetasEstudiantes[] =
        (string)($libro['titulo'] ?? 'Libro');

    $valoresEstudiantes[] =
        (int)($libro['total_reservas'] ?? 0);
}

$etiquetasProfesores = [];
$valoresProfesores = [];

foreach ($librosProfesores as $libro) {
    $etiquetasProfesores[] =
        (string)($libro['titulo'] ?? 'Libro');

    $valoresProfesores[] =
        (int)($libro['total_reservas'] ?? 0);
}

$etiquetasDias = [];
$valoresDiasEstudiantes = [];
$valoresDiasProfesores = [];

foreach ($reservasPorDia as $registro) {
    $fechaRegistro =
        (string)($registro['fecha_reserva'] ?? '');

    try {
        $etiquetasDias[] =
            (new DateTimeImmutable($fechaRegistro))
                ->format('d/m');
    } catch (Throwable $e) {
        $etiquetasDias[] = $fechaRegistro;
    }

    $valoresDiasEstudiantes[] =
        (int)($registro['estudiantes'] ?? 0);

    $valoresDiasProfesores[] =
        (int)($registro['profesores'] ?? 0);
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

    <title>Estadísticas | ReadPoint</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/admin.css?v=4"
    >

    <link
        rel="stylesheet"
        href="assets/css/estadisticas.css?v=4"
    >

    <script
        src="https://cdn.jsdelivr.net/npm/chart.js"
    ></script>

</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . '/menu.php'; ?>

    <main class="main-content">

        <!-- Encabezado -->

        <section class="encabezado-estadisticas">

            <div>

                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>Estadísticas de uso</h1>

                <p>
                    Analiza los libros más reservados por
                    estudiantes y profesores durante el periodo
                    seleccionado.
                </p>

            </div>

            <span class="periodo-estadisticas">

                <?php echo fechaVisibleEstadistica(
                    $filtros['fecha_inicio'] ?? null
                ); ?>

                —

                <?php echo fechaVisibleEstadistica(
                    $filtros['fecha_fin'] ?? null
                ); ?>

            </span>

        </section>

        <!-- Mensaje de error -->

        <?php if ($error !== ''): ?>

            <div class="alerta-estadisticas">

                <?php echo escaparEstadistica($error); ?>

            </div>

        <?php endif; ?>

        <!-- Filtros -->

        <form
            action="estadisticas.php"
            method="GET"
            class="filtros-estadisticas"
        >

            <div class="grupo-filtro-estadisticas">

                <label for="fecha_inicio">
                    Fecha inicial
                </label>

                <input
                    type="date"
                    id="fecha_inicio"
                    name="fecha_inicio"
                    value="<?php echo escaparEstadistica(
                        $filtros['fecha_inicio'] ?? ''
                    ); ?>"
                    required
                >

            </div>

            <div class="grupo-filtro-estadisticas">

                <label for="fecha_fin">
                    Fecha final
                </label>

                <input
                    type="date"
                    id="fecha_fin"
                    name="fecha_fin"
                    value="<?php echo escaparEstadistica(
                        $filtros['fecha_fin'] ?? ''
                    ); ?>"
                    required
                >

            </div>

            <button
                type="submit"
                class="boton-consultar-estadisticas"
            >
                Consultar
            </button>

            <a
                href="estadisticas.php"
                class="boton-periodo-actual"
            >
                Periodo actual
            </a>

        </form>

        <!-- Resumen -->

        <section class="resumen-estadisticas">

            <article class="tarjeta-estadistica">

                <span>Total de reservas</span>

                <strong>
                    <?php echo (int)(
                        $resumen['total_reservas'] ?? 0
                    ); ?>
                </strong>

                <small>
                    Dentro del periodo
                </small>

            </article>

            <article class="tarjeta-estadistica">

                <span>Reservas de estudiantes</span>

                <strong>
                    <?php echo (int)(
                        $resumen['reservas_estudiantes'] ?? 0
                    ); ?>
                </strong>

                <small>
                    Uso estudiantil
                </small>

            </article>

            <article class="tarjeta-estadistica">

                <span>Reservas de profesores</span>

                <strong>
                    <?php echo (int)(
                        $resumen['reservas_profesores'] ?? 0
                    ); ?>
                </strong>

                <small>
                    Uso docente
                </small>

            </article>

            <article class="tarjeta-estadistica">

                <span>Libros utilizados</span>

                <strong>
                    <?php echo (int)(
                        $resumen['libros_utilizados'] ?? 0
                    ); ?>
                </strong>

                <small>
                    Títulos diferentes
                </small>

            </article>

        </section>

        <!-- Distribución -->

        <section
            class="panel-estadisticas comparacion-estadisticas"
        >

            <div class="encabezado-panel-estadisticas">

                <div>

                    <h2>Distribución de reservas</h2>

                    <p>
                        Comparación general entre estudiantes
                        y profesores.
                    </p>

                </div>

            </div>

            <?php if ($totalComparativo > 0): ?>

                <div class="contenedor-grafica-dona">

                    <div class="grafica-dona">

                        <canvas
                            id="graficaDistribucion"
                            aria-label="Distribución de reservas"
                        ></canvas>

                        <div class="centro-grafica-dona">

                            <strong>
                                <?php echo $totalComparativo; ?>
                            </strong>

                            <span>
                                Reservas
                            </span>

                        </div>

                    </div>

                    <div class="resumen-distribucion">

                        <article class="dato-distribucion">

                            <span
                                class="indicador-distribucion estudiantes"
                            ></span>

                            <div>

                                <small>
                                    Estudiantes
                                </small>

                                <strong>
                                    <?php echo
                                        $totalPeriodoEstudiantes;
                                    ?>
                                </strong>

                                <span>
                                    <?php echo escaparEstadistica(
                                        $porcentajeEstudiantes
                                    ); ?>%
                                </span>

                            </div>

                        </article>

                        <article class="dato-distribucion">

                            <span
                                class="indicador-distribucion profesores"
                            ></span>

                            <div>

                                <small>
                                    Profesores
                                </small>

                                <strong>
                                    <?php echo
                                        $totalPeriodoProfesores;
                                    ?>
                                </strong>

                                <span>
                                    <?php echo escaparEstadistica(
                                        $porcentajeProfesores
                                    ); ?>%
                                </span>

                            </div>

                        </article>

                    </div>

                </div>

            <?php else: ?>

                <div class="estado-vacio-estadisticas">

                    No existen reservas para mostrar la
                    distribución.

                </div>

            <?php endif; ?>

        </section>

        <!-- Rankings -->

        <section class="graficas-estadisticas">

            <article class="panel-estadisticas">

                <div class="encabezado-panel-estadisticas">

                    <div>

                        <h2>
                            Más reservados por estudiantes
                        </h2>

                        <p>
                            Libros con mayor uso estudiantil
                            durante el periodo.
                        </p>

                    </div>

                </div>

                <?php if (empty($librosEstudiantes)): ?>

                    <div class="estado-vacio-estadisticas">

                        No existen reservas de estudiantes
                        dentro del periodo seleccionado.

                    </div>

                <?php else: ?>

                    <div class="contenedor-grafica-ranking">

                        <canvas
                            id="graficaEstudiantes"
                            aria-label="Libros más reservados por estudiantes"
                        ></canvas>

                    </div>

                <?php endif; ?>

            </article>

            <article class="panel-estadisticas">

                <div class="encabezado-panel-estadisticas">

                    <div>

                        <h2>
                            Más reservados por profesores
                        </h2>

                        <p>
                            Libros con mayor uso docente durante
                            el periodo.
                        </p>

                    </div>

                </div>

                <?php if (empty($librosProfesores)): ?>

                    <div class="estado-vacio-estadisticas">

                        No existen reservas de profesores
                        dentro del periodo seleccionado.

                    </div>

                <?php else: ?>

                    <div class="contenedor-grafica-ranking">

                        <canvas
                            id="graficaProfesores"
                            aria-label="Libros más reservados por profesores"
                        ></canvas>

                    </div>

                <?php endif; ?>

            </article>

        </section>

        <!-- Actividad diaria -->

        <section
            class="panel-estadisticas actividad-diaria"
        >

            <div class="encabezado-panel-estadisticas">

                <div>

                    <h2>Actividad por día</h2>

                    <p>
                        Cantidad de reservas registradas
                        diariamente durante el periodo.
                    </p>

                </div>

                <button
                    type="button"
                    class="boton-imprimir-estadisticas"
                    onclick="window.print()"
                >
                    Imprimir
                </button>

            </div>

            <?php if (empty($reservasPorDia)): ?>

                <div class="estado-vacio-estadisticas">

                    No existen movimientos durante el periodo
                    seleccionado.

                </div>

            <?php else: ?>

                <div class="contenedor-grafica-actividad">

                    <canvas
                        id="graficaActividad"
                        aria-label="Actividad diaria de reservas"
                    ></canvas>

                </div>

                <div class="contenedor-tabla-estadisticas">

                    <table class="tabla-estadisticas">

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
                                $registro['estudiantes'] ?? 0
                            );

                            $profesores = (int)(
                                $registro['profesores'] ?? 0
                            );

                            ?>

                            <tr>

                                <td>

                                    <strong>

                                        <?php echo
                                            fechaVisibleEstadistica(
                                                $registro[
                                                    'fecha_reserva'
                                                ] ?? null
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

    </main>

</div>

<script>
    window.datosEstadisticas = {
        distribucion: {
            estudiantes: <?php echo json_encode(
                $totalPeriodoEstudiantes
            ); ?>,

            profesores: <?php echo json_encode(
                $totalPeriodoProfesores
            ); ?>
        },

        estudiantes: {
            etiquetas: <?php echo json_encode(
                $etiquetasEstudiantes,
                JSON_UNESCAPED_UNICODE
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
            ); ?>,

            valores: <?php echo json_encode(
                $valoresEstudiantes
            ); ?>
        },

        profesores: {
            etiquetas: <?php echo json_encode(
                $etiquetasProfesores,
                JSON_UNESCAPED_UNICODE
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
            ); ?>,

            valores: <?php echo json_encode(
                $valoresProfesores
            ); ?>
        },

        actividad: {
            etiquetas: <?php echo json_encode(
                $etiquetasDias,
                JSON_UNESCAPED_UNICODE
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
            ); ?>,

            estudiantes: <?php echo json_encode(
                $valoresDiasEstudiantes
            ); ?>,

            profesores: <?php echo json_encode(
                $valoresDiasProfesores
            ); ?>
        }
    };
</script>

<script
    src="assets/js/estadisticas.js?v=1"
    defer
></script>

</body>

</html>