<?php

require_once __DIR__ . '/../Core/Database.php';

class ReservaModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    /**
     * Obtiene las reservas o libros asociados
     * al estudiante que inició sesión.
     */
    public function obtenerPorUsuario(int $usuarioId): array
    {
        $sql = "
            SELECT
                r.id,
                r.usuario_id,
                r.tipo_usuario,
                r.libro_id,
                r.fecha_reserva,
                r.fecha_vencimiento,
                r.fecha_devolucion,
                r.estado,
                r.observacion,
                r.created_at,

                l.titulo,
                l.autor,
                l.imagen,
                l.thumbnail,
                l.tipo_acceso,
                l.precio_acceso,
                l.dias_acceso,

                c.nombre AS categoria_nombre

            FROM reservas r

            INNER JOIN libros l
                ON l.id = r.libro_id

            INNER JOIN categorias c
                ON c.id = l.categoria_id

            WHERE
                r.usuario_id = :usuario_id
                AND r.tipo_usuario = 'estudiante'

            ORDER BY
                r.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':usuario_id',
            $usuarioId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra un libro gratuito como lectura activa.
     */
    public function registrarLecturaGratuita(
        int $usuarioId,
        int $libroId
    ): void {
        $sqlExiste = "
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

            LIMIT 1
        ";

        $stmtExiste = $this->db->prepare($sqlExiste);

        $stmtExiste->execute([
            ':usuario_id' => $usuarioId,
            ':libro_id' => $libroId
        ]);

        $reservaExistente = $stmtExiste->fetch(
            PDO::FETCH_ASSOC
        );

        if ($reservaExistente) {
            return;
        }

        $sql = "
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
                CURDATE(),
                NULL,
                NULL,
                'en_prestamo',
                'Acceso digital gratuito',
                NULL
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':libro_id' => $libroId
        ]);
    }

    /**
     * Obtiene las reservas para el reporte administrativo.
     */
    public function obtenerReporte(
        ?string $fechaDesde = null,
        ?string $fechaHasta = null,
        ?string $estado = null
    ): array {
        $sql = "
            SELECT
                r.id,
                r.usuario_id,
                r.tipo_usuario,
                r.libro_id,

                u.usuario,

                TRIM(
                    CONCAT_WS(
                        ' ',
                        NULLIF(e.primer_nombre, ''),
                        NULLIF(e.segundo_nombre, ''),
                        NULLIF(e.primer_apellido, ''),
                        NULLIF(e.segundo_apellido, '')
                    )
                ) AS estudiante_nombre,

                e.cip,

                l.titulo,
                l.autor,
                l.tipo_acceso,
                l.precio_acceso,
                l.dias_acceso,

                c.nombre AS categoria_nombre,

                r.fecha_reserva,
                r.fecha_vencimiento,
                r.fecha_devolucion,
                r.estado,
                r.observacion,
                r.created_at

            FROM reservas r

            INNER JOIN usuarios u
                ON u.id = r.usuario_id

            LEFT JOIN estudiantes e
                ON e.usuario_id = u.id

            INNER JOIN libros l
                ON l.id = r.libro_id

            INNER JOIN categorias c
                ON c.id = l.categoria_id

            WHERE 1 = 1
        ";

        $parametros = [];

        if ($fechaDesde !== null && $fechaDesde !== '') {
            $sql .= "
                AND r.fecha_reserva >= :fecha_desde
            ";

            $parametros[':fecha_desde'] = $fechaDesde;
        }

        if ($fechaHasta !== null && $fechaHasta !== '') {
            $sql .= "
                AND r.fecha_reserva <= :fecha_hasta
            ";

            $parametros[':fecha_hasta'] = $fechaHasta;
        }

        if ($estado !== null && $estado !== '') {
            $sql .= "
                AND r.estado = :estado
            ";

            $parametros[':estado'] = $estado;
        }

        $sql .= "
            ORDER BY
                r.fecha_reserva DESC,
                r.created_at DESC,
                r.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}