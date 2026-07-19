<?php

require_once __DIR__ . '/../Models/FacturaModel.php';
require_once __DIR__ . '/../Models/LibroModel.php';
require_once __DIR__ . '/../Core/Csrf.php';
require_once __DIR__ . '/../Core/Sanitizer.php';

class FacturaController
{
    private FacturaModel $facturaModelo;
    private LibroModel $libroModelo;

    public function __construct()
    {
        $this->facturaModelo = new FacturaModel();
        $this->libroModelo = new LibroModel();
    }

    /**
     * Verifica que haya una sesión válida.
     */
    private function verificarSesion(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }

        if (
            !in_array(
                $_SESSION['rol'] ?? '',
                ['estudiante', 'profesor'],
                true
            )
        ) {
            header('Location: dashboard.php');
            exit;
        }
    }

    /**
     * Regresa al formulario de compra con un error.
     */
    private function redirigirCompra(
        int $libroId,
        string $error
    ): void {
        header(
            'Location: comprar_libro.php?id='
            . $libroId
            . '&error='
            . urlencode($error)
        );

        exit;
    }

    /**
     * Obtiene y valida el libro que se mostrará
     * en la pantalla de compra.
     */
    public function obtenerLibroParaCompra(
        int $libroId
    ): array {
        $this->verificarSesion();

        if ($libroId <= 0) {
            header('Location: catalogo.php');
            exit;
        }

        $libro = $this->libroModelo->obtenerPorId(
            $libroId
        );

        if ($libro === null) {
            header('Location: catalogo.php');
            exit;
        }

        $origen = $libro['origen'] ?? 'propio';

        $tipoAcceso = $libro['tipo_acceso']
            ?? 'gratuito';

        $precio = (float)(
            $libro['precio_acceso'] ?? 0
        );

        $diasAcceso = (int)(
            $libro['dias_acceso'] ?? 0
        );

        $archivoPdf = trim(
            (string)($libro['archivo_pdf'] ?? '')
        );

        /*
         * Solo se pueden comprar libros propios,
         * de pago y con PDF disponible.
         */
        if (
            $origen !== 'propio'
            || $tipoAcceso !== 'pago'
            || $precio <= 0
            || $diasAcceso <= 0
            || $archivoPdf === ''
        ) {
            header(
                'Location: libro_detalle.php?id='
                . $libroId
                . '&error=acceso'
            );

            exit;
        }

        /*
         * Evita comprar nuevamente un libro
         * que todavía tiene acceso vigente.
         */
        if (
            $this->facturaModelo
                ->usuarioTieneAccesoActivo(
                    (int)$_SESSION['usuario_id'],
                    $libroId
                )
        ) {
            header(
                'Location: libro_detalle.php?id='
                . $libroId
                . '&error=acceso_activo'
            );

            exit;
        }

        return $libro;
    }

    /**
     * Procesa la compra enviada desde el formulario.
     */
    public function procesarCompra(): void
    {
        $this->verificarSesion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: catalogo.php');
            exit;
        }

        /*
         * Validar token CSRF.
         */
        $csrf = $_POST['csrf_token'] ?? '';

        if (!Csrf::validarToken($csrf)) {
            die('Token CSRF inválido.');
        }

        /*
         * Obtener el ID del libro.
         */
        $libroId = filter_input(
            INPUT_POST,
            'libro_id',
            FILTER_VALIDATE_INT
        );

        if (!$libroId || $libroId <= 0) {
            header('Location: catalogo.php');
            exit;
        }

        /*
         * Método de pago seleccionado.
         */
        $metodoPago = trim(
            (string)($_POST['metodo_pago'] ?? '')
        );

        $metodosPermitidos = [
            'tarjeta',
            'yappy',
            'transferencia'
        ];

        if (
            !in_array(
                $metodoPago,
                $metodosPermitidos,
                true
            )
        ) {
            $this->redirigirCompra(
                (int)$libroId,
                'metodo'
            );
        }

        /*
         * Referencia del pago.
         */
        $referenciaPago = Sanitizer::limpiarTexto(
            $_POST['referencia_pago'] ?? ''
        );

        if (strlen($referenciaPago) > 100) {
            $this->redirigirCompra(
                (int)$libroId,
                'referencia'
            );
        }

        /*
         * Buscar nuevamente el libro en la base.
         * El precio nunca se toma del formulario.
         */
        $libro = $this->libroModelo->obtenerPorId(
            (int)$libroId
        );

        if ($libro === null) {
            header('Location: catalogo.php');
            exit;
        }

        $origen = $libro['origen'] ?? 'propio';

        $tipoAcceso = $libro['tipo_acceso']
            ?? 'gratuito';

        $precio = (float)(
            $libro['precio_acceso'] ?? 0
        );

        $diasAcceso = (int)(
            $libro['dias_acceso'] ?? 0
        );

        $archivoPdf = trim(
            (string)($libro['archivo_pdf'] ?? '')
        );

        /*
         * Validar que el libro realmente
         * esté disponible para compra.
         */
        if (
            $origen !== 'propio'
            || $tipoAcceso !== 'pago'
            || $precio <= 0
            || $diasAcceso <= 0
            || $archivoPdf === ''
        ) {
            header(
                'Location: libro_detalle.php?id='
                . $libroId
                . '&error=acceso'
            );

            exit;
        }

        try {
            $facturaId =
                $this->facturaModelo->procesarCompra(
                    (int)$_SESSION['usuario_id'],
                    (int)$libroId,
                    $precio,
                    $diasAcceso,
                    $metodoPago,
                    $referenciaPago
                );

            /*
             * Después de guardar la compra,
             * mostrar la factura generada.
             */
            header(
                'Location: factura_detalle.php?id='
                . $facturaId
                . '&exito=1'
            );

            exit;
        } catch (InvalidArgumentException $e) {
            $this->redirigirCompra(
                (int)$libroId,
                'datos'
            );
        } catch (RuntimeException $e) {
            $this->redirigirCompra(
                (int)$libroId,
                'acceso_activo'
            );
        } catch (Throwable $e) {
            $this->redirigirCompra(
                (int)$libroId,
                'guardar'
            );
        }
    }

    /**
     * Obtiene una factura del usuario actual.
     */
    public function obtenerFactura(
        int $facturaId
    ): ?array {
        $this->verificarSesion();

        if ($facturaId <= 0) {
            return null;
        }

        return $this->facturaModelo->obtenerPorId(
            $facturaId,
            (int)$_SESSION['usuario_id']
        );
    }

    /**
     * Obtiene todas las facturas del usuario actual.
     */
    public function obtenerMisFacturas(): array
    {
        $this->verificarSesion();

        return $this->facturaModelo
            ->obtenerPorUsuario(
                (int)$_SESSION['usuario_id']
            );
    }
}