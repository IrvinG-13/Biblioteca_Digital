<?php

require_once __DIR__
    . '/../Models/EstadisticaModel.php';

class EstadisticaController
{
    private EstadisticaModel $modelo;

    public function __construct()
    {
        $this->verificarAdministrador();

        $this->modelo = new EstadisticaModel();
    }

    /**
     * Permite el acceso únicamente al administrador.
     */
    private function verificarAdministrador(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }

        if (($_SESSION['rol'] ?? '') !== 'admin') {
            header('Location: catalogo.php');
            exit;
        }
    }

    /**
     * Obtiene toda la información necesaria
     * para construir la pantalla de estadísticas.
     */
    public function obtenerEstadisticas(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Periodo predeterminado
        |--------------------------------------------------------------------------
        |
        | Si el administrador todavía no seleccionó fechas,
        | se utilizará desde el primer día del mes actual
        | hasta la fecha actual.
        |
        */

        $fechaActual = new DateTimeImmutable('today');

        $fechaInicioPredeterminada = $fechaActual
            ->modify('first day of this month')
            ->format('Y-m-d');

        $fechaFinPredeterminada = $fechaActual
            ->format('Y-m-d');

        /*
        |--------------------------------------------------------------------------
        | Obtener fechas enviadas por GET
        |--------------------------------------------------------------------------
        */

        $fechaInicio = trim(
            (string)(
                $_GET['fecha_inicio']
                ?? $fechaInicioPredeterminada
            )
        );

        $fechaFin = trim(
            (string)(
                $_GET['fecha_fin']
                ?? $fechaFinPredeterminada
            )
        );

        $error = '';

        /*
        |--------------------------------------------------------------------------
        | Validar formato de las fechas
        |--------------------------------------------------------------------------
        */

        if (
            !$this->fechaValida($fechaInicio)
            || !$this->fechaValida($fechaFin)
        ) {
            $error = 'Las fechas seleccionadas no son válidas.';

            $fechaInicio = $fechaInicioPredeterminada;
            $fechaFin = $fechaFinPredeterminada;
        }

        /*
        |--------------------------------------------------------------------------
        | Validar orden de las fechas
        |--------------------------------------------------------------------------
        */

        if (
            $error === ''
            && $fechaInicio > $fechaFin
        ) {
            $error = 'La fecha inicial no puede ser mayor que la fecha final.';

            $fechaInicio = $fechaInicioPredeterminada;
            $fechaFin = $fechaFinPredeterminada;
        }

        /*
        |--------------------------------------------------------------------------
        | Consultar información estadística
        |--------------------------------------------------------------------------
        */

        try {
            $resumen = $this->modelo->obtenerResumen(
                $fechaInicio,
                $fechaFin
            );

            $librosEstudiantes =
                $this->modelo->obtenerLibrosMasReservados(
                    $fechaInicio,
                    $fechaFin,
                    'estudiante',
                    10
                );

            $librosProfesores =
                $this->modelo->obtenerLibrosMasReservados(
                    $fechaInicio,
                    $fechaFin,
                    'profesor',
                    10
                );

            $reservasPorDia =
                $this->modelo->obtenerReservasPorDia(
                    $fechaInicio,
                    $fechaFin
                );
        } catch (Throwable $e) {
            $error = 'No fue posible cargar las estadísticas.';

            $resumen = [
                'total_reservas' => 0,
                'reservas_estudiantes' => 0,
                'reservas_profesores' => 0,
                'libros_utilizados' => 0
            ];

            $librosEstudiantes = [];
            $librosProfesores = [];
            $reservasPorDia = [];
        }

        /*
        |--------------------------------------------------------------------------
        | Preparar los datos para las gráficas
        |--------------------------------------------------------------------------
        */

        $graficaEstudiantes =
            $this->prepararDatosGrafica(
                $librosEstudiantes
            );

        $graficaProfesores =
            $this->prepararDatosGrafica(
                $librosProfesores
            );

        $graficaPeriodo =
            $this->prepararGraficaPeriodo(
                $reservasPorDia
            );

        return [
            'resumen' => $resumen,

            'libros_estudiantes' =>
                $librosEstudiantes,

            'libros_profesores' =>
                $librosProfesores,

            'reservas_por_dia' =>
                $reservasPorDia,

            'grafica_estudiantes' =>
                $graficaEstudiantes,

            'grafica_profesores' =>
                $graficaProfesores,

            'grafica_periodo' =>
                $graficaPeriodo,

            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ],

            'error' => $error
        ];
    }

    /**
     * Verifica que una fecha tenga formato YYYY-MM-DD
     * y que represente una fecha real.
     */
    private function fechaValida(
        string $fecha
    ): bool {
        if ($fecha === '') {
            return false;
        }

        $fechaObjeto =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $fecha
            );

        if ($fechaObjeto === false) {
            return false;
        }

        $errores = DateTimeImmutable::getLastErrors();

        if (
            is_array($errores)
            && (
                $errores['warning_count'] > 0
                || $errores['error_count'] > 0
            )
        ) {
            return false;
        }

        return $fechaObjeto->format('Y-m-d')
            === $fecha;
    }

    /**
     * Prepara los títulos y cantidades que usará
     * la gráfica de libros más reservados.
     */
    private function prepararDatosGrafica(
        array $libros
    ): array {
        $etiquetas = [];
        $cantidades = [];

        foreach ($libros as $libro) {
            $titulo = trim(
                (string)(
                    $libro['titulo']
                    ?? 'Libro sin título'
                )
            );

            /*
             * Acortar títulos muy largos para evitar
             * que la gráfica se vea desordenada.
             */
            if (mb_strlen($titulo) > 35) {
                $titulo = mb_substr(
                    $titulo,
                    0,
                    32
                ) . '...';
            }

            $etiquetas[] = $titulo;

            $cantidades[] = (int)(
                $libro['total_reservas']
                ?? 0
            );
        }

        return [
            'etiquetas' => $etiquetas,
            'cantidades' => $cantidades
        ];
    }

    /**
     * Prepara las reservas diarias de estudiantes
     * y profesores para una gráfica comparativa.
     */
    private function prepararGraficaPeriodo(
        array $reservasPorDia
    ): array {
        $fechas = [];
        $estudiantes = [];
        $profesores = [];

        foreach ($reservasPorDia as $registro) {
            $fecha = (string)(
                $registro['fecha_reserva']
                ?? ''
            );

            try {
                $fechaVisible =
                    (new DateTimeImmutable($fecha))
                    ->format('d/m/Y');
            } catch (Throwable $e) {
                $fechaVisible = $fecha;
            }

            $fechas[] = $fechaVisible;

            $estudiantes[] = (int)(
                $registro['estudiantes']
                ?? 0
            );

            $profesores[] = (int)(
                $registro['profesores']
                ?? 0
            );
        }

        return [
            'fechas' => $fechas,
            'estudiantes' => $estudiantes,
            'profesores' => $profesores
        ];
    }
}