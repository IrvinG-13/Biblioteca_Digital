<?php

require_once __DIR__ . '/../Core/Database.php';

class FacturaAdminModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    /**
     * Lista las facturas aplicando filtros.
     */
    public function listar(
        string $estado = '',
        string $metodoPago = '',
        string $buscar = ''
    ): array {
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

            WHERE 1 = 1
        ";

        $parametros = [];

        if ($estado !== '') {
            $sql .= "
                AND f.estado = :estado
            ";

            $parametros[':estado'] = $estado;
        }

        if ($metodoPago !== '') {
            $sql .= "
                AND f.metodo_pago = :metodo_pago
            ";

            $parametros[':metodo_pago'] = $metodoPago;
        }

        if ($buscar !== '') {
            $sql .= "
                AND (
                    f.numero_factura LIKE :buscar
                    OR u.usuario LIKE :buscar
                    OR l.titulo LIKE :buscar
                    OR l.autor LIKE :buscar
                )
            ";

            $parametros[':buscar'] =
                '%' . $buscar . '%';
        }

        $sql .= "
            ORDER BY
                f.fecha_factura DESC,
                f.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    /**
     * Obtiene el resumen general.
     */
    public function obtenerResumen(): array
    {
        $sql = "
            SELECT
                COUNT(*) AS total_facturas,

                SUM(
                    CASE
                        WHEN estado = 'pagada'
                        THEN 1
                        ELSE 0
                    END
                ) AS facturas_pagadas,

                SUM(
                    CASE
                        WHEN estado = 'pendiente'
                        THEN 1
                        ELSE 0
                    END
                ) AS facturas_pendientes,

                COALESCE(
                    SUM(
                        CASE
                            WHEN estado = 'pagada'
                            THEN total
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_ingresos

            FROM facturas
        ";

        $resultado = $this->db
            ->query($sql)
            ->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: [
            'total_facturas' => 0,
            'facturas_pagadas' => 0,
            'facturas_pendientes' => 0,
            'total_ingresos' => 0
        ];
    }

    /**
     * Obtiene una factura específica.
     */
    public function obtenerPorId(
        int $facturaId
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
                u.rol,

                df.libro_id,
                df.cantidad,
                df.precio_unitario,
                df.subtotal AS detalle_subtotal,
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

            WHERE f.id = :factura_id

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':factura_id' => $facturaId
        ]);

        $factura = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        return $factura ?: null;
    }
}