<?php

require_once __DIR__ . '/../Models/ReservaModel.php';
require_once __DIR__ . '/../Core/ExcelReserva.php';

class ReservaController
{
    private ReservaModel $modelo;

    private array $estadosPermitidos = [
        'reservado',
        'en_prestamo',
        'por_vencer',
        'devuelto',
        'cancelado'
    ];

    public function __construct()
    {
        $this->modelo = new ReservaModel();
    }

    /**
     * Obtiene los datos del reporte administrativo.
     */
    public function obtenerDatosReporte(): array
    {
        $this->validarAdministrador();

        $fechaDesde = $this->validarFecha(
            $_GET['fecha_desde'] ?? null
        );

        $fechaHasta = $this->validarFecha(
            $_GET['fecha_hasta'] ?? null
        );

        $estado = trim(
            (string)($_GET['estado'] ?? '')
        );

        if (!in_array(
            $estado,
            $this->estadosPermitidos,
            true
        )) {
            $estado = '';
        }

        /*
         * Si las fechas están invertidas,
         * se intercambian automáticamente.
         */
        if (
            $fechaDesde !== null
            && $fechaHasta !== null
            && $fechaDesde > $fechaHasta
        ) {
            $temporal = $fechaDesde;
            $fechaDesde = $fechaHasta;
            $fechaHasta = $temporal;
        }

        $reservas = $this->modelo->obtenerReporte(
            $fechaDesde,
            $fechaHasta,
            $estado !== '' ? $estado : null
        );

        $resumen = [
            'total' => count($reservas),
            'activas' => 0,
            'devueltas' => 0,
            'canceladas' => 0
        ];

        foreach ($reservas as $reserva) {
            $estadoReserva = $reserva['estado'] ?? '';

            if (in_array(
                $estadoReserva,
                [
                    'reservado',
                    'en_prestamo',
                    'por_vencer'
                ],
                true
            )) {
                $resumen['activas']++;
            }

            if ($estadoReserva === 'devuelto') {
                $resumen['devueltas']++;
            }

            if ($estadoReserva === 'cancelado') {
                $resumen['canceladas']++;
            }
        }

        return [
            'reservas' => $reservas,

            'filtros' => [
                'fecha_desde' => $fechaDesde ?? '',
                'fecha_hasta' => $fechaHasta ?? '',
                'estado' => $estado
            ],

            'resumen' => $resumen,

            'estados' => [
                '' => 'Todos los estados',
                'reservado' => 'Reservado',
                'en_prestamo' => 'En préstamo',
                'por_vencer' => 'Por vencer',
                'devuelto' => 'Devuelto',
                'cancelado' => 'Cancelado'
            ]
        ];
    }

    /**
     * Exporta el reporte a Excel.
     */
    public function exportarExcel(): void
    {
        $datos = $this->obtenerDatosReporte();

        ExcelReserva::exportar(
            $datos['reservas'],
            $datos['filtros'],
            $datos['resumen']
        );
    }

    /**
     * Solo permite el acceso al administrador.
     */
    private function validarAdministrador(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }

        $rol = (string)($_SESSION['rol'] ?? '');

        if ($rol === 'estudiante') {
            header('Location: catalogo.php');
            exit;
        }

        if ($rol !== 'admin') {
            header('Location: login.php');
            exit;
        }
        require_once __DIR__ . '/../Core/SesionGuard.php';
        SesionGuard::bloquearSiCambioPasswordPendiente();
    }

    /**
     * Valida una fecha YYYY-MM-DD.
     */
    private function validarFecha(mixed $fecha): ?string
    {
        $fecha = trim((string)$fecha);

        if ($fecha === '') {
            return null;
        }

        $objetoFecha = DateTime::createFromFormat(
            'Y-m-d',
            $fecha
        );

        if (
            $objetoFecha === false
            || $objetoFecha->format('Y-m-d') !== $fecha
        ) {
            return null;
        }

        return $fecha;
    }
}