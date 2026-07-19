<?php

require_once __DIR__
    . '/../Models/FacturaAdminModel.php';

class FacturaAdminController
{
    private FacturaAdminModel $modelo;

    public function __construct()
    {
        $this->verificarAdministrador();

        $this->modelo =
            new FacturaAdminModel();
    }

    /**
     * Permite únicamente al administrador.
     */
    private function verificarAdministrador(): void
    {
        if (
            session_status()
            !== PHP_SESSION_ACTIVE
        ) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }

        /*
         * En este proyecto el rol se llama admin.
         */
        if (
            ($_SESSION['rol'] ?? '')
            !== 'admin'
        ) {
            header('Location: catalogo.php');
            exit;
        }
    }

    /**
     * Datos para el listado administrativo.
     */
    public function obtenerListado(): array
    {
        $estadosPermitidos = [
            'pagada',
            'pendiente',
            'anulada'
        ];

        $metodosPermitidos = [
            'yappy',
            'tarjeta',
            'transferencia'
        ];

        $estado = trim(
            (string)($_GET['estado'] ?? '')
        );

        $metodoPago = trim(
            (string)($_GET['metodo_pago'] ?? '')
        );

        $buscar = trim(
            (string)($_GET['buscar'] ?? '')
        );

        if (
            !in_array(
                $estado,
                $estadosPermitidos,
                true
            )
        ) {
            $estado = '';
        }

        if (
            !in_array(
                $metodoPago,
                $metodosPermitidos,
                true
            )
        ) {
            $metodoPago = '';
        }

        $buscar = mb_substr(
            $buscar,
            0,
            100
        );

        return [
            'facturas' => $this->modelo->listar(
                $estado,
                $metodoPago,
                $buscar
            ),

            'resumen' =>
                $this->modelo->obtenerResumen(),

            'filtros' => [
                'estado' => $estado,
                'metodo_pago' => $metodoPago,
                'buscar' => $buscar
            ]
        ];
    }

    /**
     * Obtiene una factura para el administrador.
     */
    public function obtenerFactura(
        int $facturaId
    ): ?array {
        if ($facturaId <= 0) {
            return null;
        }

        return $this->modelo->obtenerPorId(
            $facturaId
        );
    }
}