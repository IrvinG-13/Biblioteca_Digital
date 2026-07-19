<?php

require_once __DIR__ . '/../Core/Database.php';

class FacturaModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    /**
     * Comprueba si el usuario ya tiene acceso activo al libro.
     */
    public function usuarioTieneAccesoActivo(
        int $usuarioId,
        int $libroId
    ): bool {
        $sql = "
            SELECT id

            FROM reservas

            WHERE
                usuario_id = :usuario_id
                AND tipo_usuario = 'estudiante'
                AND libro_id = :libro_id
                AND estado IN (
                    'reservado',
                    'en_prestamo',
                    'por_vencer'
                )
                AND (
                    fecha_vencimiento IS NULL
                    OR fecha_vencimiento >= CURDATE()
                )

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':libro_id' => $libroId
        ]);

        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Guarda la factura, su detalle y el acceso al libro.
     */
    public function procesarCompra(
        int $usuarioId,
        int $libroId,
        float $precio,
        int $diasAcceso,
        string $metodoPago,
        ?string $referenciaPago
    ): int {
        $metodosPermitidos = [
            'tarjeta',
            'yappy',
            'transferencia'
        ];

        if ($usuarioId <= 0 || $libroId <= 0) {
            throw new InvalidArgumentException(
                'El usuario o el libro no son válidos.'
            );
        }

        if ($precio <= 0) {
            throw new InvalidArgumentException(
                'El precio del libro no es válido.'
            );
        }

        if ($diasAcceso <= 0) {
            throw new InvalidArgumentException(
                'Los días de acceso no son válidos.'
            );
        }

        if (!in_array(
            $metodoPago,
            $metodosPermitidos,
            true
        )) {
            throw new InvalidArgumentException(
                'El método de pago no es válido.'
            );
        }

        if ($this->usuarioTieneAccesoActivo(
            $usuarioId,
            $libroId
        )) {
            throw new RuntimeException(
                'Ya tienes acceso activo a este libro.'
            );
        }

        $numeroFactura = $this->generarNumeroFactura();

        $fechaInicio = new DateTimeImmutable('today');

        $fechaVencimiento = $fechaInicio->modify(
            '+' . $diasAcceso . ' days'
        );

        $precioFormateado = number_format(
            $precio,
            2,
            '.',
            ''
        );

        $referenciaPago = trim(
            (string)$referenciaPago
        );

        if ($referenciaPago === '') {
            $referenciaPago = null;
        }

        try {
            $this->db->beginTransaction();

            /*
             * 1. Registrar la factura.
             */
            $sqlFactura = "
                INSERT INTO facturas (
                    numero_factura,
                    usuario_id,
                    subtotal,
                    impuesto,
                    total,
                    metodo_pago,
                    referencia_pago,
                    estado
                )
                VALUES (
                    :numero_factura,
                    :usuario_id,
                    :subtotal,
                    0.00,
                    :total,
                    :metodo_pago,
                    :referencia_pago,
                    'pagada'
                )
            ";

            $stmtFactura = $this->db->prepare(
                $sqlFactura
            );

            $stmtFactura->execute([
                ':numero_factura' => $numeroFactura,
                ':usuario_id' => $usuarioId,
                ':subtotal' => $precioFormateado,
                ':total' => $precioFormateado,
                ':metodo_pago' => $metodoPago,
                ':referencia_pago' => $referenciaPago
            ]);

            $facturaId = (int)$this->db->lastInsertId();

            /*
             * 2. Registrar el libro comprado.
             */
            $sqlDetalle = "
                INSERT INTO detalle_facturas (
                    factura_id,
                    libro_id,
                    cantidad,
                    precio_unitario,
                    subtotal,
                    dias_acceso,
                    fecha_inicio,
                    fecha_vencimiento
                )
                VALUES (
                    :factura_id,
                    :libro_id,
                    1,
                    :precio_unitario,
                    :subtotal,
                    :dias_acceso,
                    :fecha_inicio,
                    :fecha_vencimiento
                )
            ";

            $stmtDetalle = $this->db->prepare(
                $sqlDetalle
            );

            $stmtDetalle->execute([
                ':factura_id' => $facturaId,
                ':libro_id' => $libroId,
                ':precio_unitario' => $precioFormateado,
                ':subtotal' => $precioFormateado,
                ':dias_acceso' => $diasAcceso,
                ':fecha_inicio' =>
                    $fechaInicio->format('Y-m-d'),
                ':fecha_vencimiento' =>
                    $fechaVencimiento->format('Y-m-d')
            ]);

            /*
             * 3. Agregar el libro a Mis libros.
             */
            $sqlReserva = "
                INSERT INTO reservas (
                    usuario_id,
                    tipo_usuario,
                    libro_id,
                    fecha_reserva,
                    fecha_vencimiento,
                    fecha_devolucion,
                    estado,
                    observacion,
                    firma_digital
                )
                VALUES (
                    :usuario_id,
                    'estudiante',
                    :libro_id,
                    :fecha_reserva,
                    :fecha_vencimiento,
                    NULL,
                    'en_prestamo',
                    'Acceso digital pagado',
                    NULL
                )
            ";

            $stmtReserva = $this->db->prepare(
                $sqlReserva
            );

            $stmtReserva->execute([
                ':usuario_id' => $usuarioId,
                ':libro_id' => $libroId,
                ':fecha_reserva' =>
                    $fechaInicio->format('Y-m-d'),
                ':fecha_vencimiento' =>
                    $fechaVencimiento->format('Y-m-d')
            ]);

            $this->db->commit();

            return $facturaId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Obtiene una factura específica perteneciente al usuario.
     */
    public function obtenerPorId(
        int $facturaId,
        int $usuarioId
    ): ?array {
        $sql = "
            SELECT
                f.id,
                f.numero_factura,
                f.usuario_id,
                f.fecha_factura,
                f.subtotal,
                f.impuesto,
                f.total,
                f.metodo_pago,
                f.referencia_pago,
                f.estado,

                u.usuario,

                df.libro_id,
                df.cantidad,
                df.precio_unitario,
                df.dias_acceso,
                df.fecha_inicio,
                df.fecha_vencimiento,

                l.titulo,
                l.autor

            FROM facturas f

            INNER JOIN usuarios u
                ON u.id = f.usuario_id

            INNER JOIN detalle_facturas df
                ON df.factura_id = f.id

            INNER JOIN libros l
                ON l.id = df.libro_id

            WHERE
                f.id = :factura_id
                AND f.usuario_id = :usuario_id

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':factura_id' => $facturaId,
            ':usuario_id' => $usuarioId
        ]);

        $factura = $stmt->fetch(PDO::FETCH_ASSOC);

        return $factura ?: null;
    }

    /**
     * Obtiene todas las facturas de un usuario.
     */
    public function obtenerPorUsuario(
        int $usuarioId
    ): array {
        $sql = "
            SELECT
                f.id,
                f.numero_factura,
                f.fecha_factura,
                f.total,
                f.metodo_pago,
                f.estado,

                l.titulo,
                l.autor,

                df.fecha_inicio,
                df.fecha_vencimiento

            FROM facturas f

            INNER JOIN detalle_facturas df
                ON df.factura_id = f.id

            INNER JOIN libros l
                ON l.id = df.libro_id

            WHERE f.usuario_id = :usuario_id

            ORDER BY
                f.fecha_factura DESC,
                f.id DESC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Genera un número único para la factura.
     */
    private function generarNumeroFactura(): string
    {
        return 'FAC-'
            . date('Ymd-His')
            . '-'
            . random_int(1000, 9999);
    }
}