<?php

require_once __DIR__ . '/../Core/Database.php';

class EstadisticaModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();

        $this->db = $conexion->conectar();
    }

    /**
     * Obtiene un resumen general de las reservas
     * realizadas dentro del periodo seleccionado.
     */
    public function obtenerResumen(
        string $fechaInicio,
        string $fechaFin
    ): array {
        $sql = "
            SELECT
                COUNT(*) AS total_reservas,

                SUM(
                    CASE
                        WHEN tipo_usuario = 'estudiante'
                        THEN 1
                        ELSE 0
                    END
                ) AS reservas_estudiantes,

                SUM(
                    CASE
                        WHEN tipo_usuario = 'profesor'
                        THEN 1
                        ELSE 0
                    END
                ) AS reservas_profesores,

                COUNT(
                    DISTINCT libro_id
                ) AS libros_utilizados

            FROM reservas

            WHERE
                fecha_reserva BETWEEN
                    :fecha_inicio
                    AND :fecha_fin

                AND estado <> 'cancelado'
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin
        ]);

        $resultado = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        return $resultado ?: [
            'total_reservas' => 0,
            'reservas_estudiantes' => 0,
            'reservas_profesores' => 0,
            'libros_utilizados' => 0
        ];
    }

    /**
     * Obtiene los libros más reservados según
     * el tipo de usuario.
     */
    public function obtenerLibrosMasReservados(
        string $fechaInicio,
        string $fechaFin,
        string $tipoUsuario,
        int $limite = 10
    ): array {
        $tiposPermitidos = [
            'estudiante',
            'profesor'
        ];

        if (
            !in_array(
                $tipoUsuario,
                $tiposPermitidos,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'El tipo de usuario no es válido.'
            );
        }

        $limite = max(
            1,
            min($limite, 20)
        );

        /*
         * El límite se coloca como número entero
         * porque MySQL no siempre permite enlazarlo
         * mediante parámetros preparados.
         */
        $sql = "
            SELECT
                l.id AS libro_id,
                l.titulo,
                l.autor,
                COUNT(r.id) AS total_reservas

            FROM reservas r

            INNER JOIN libros l
                ON l.id = r.libro_id

            WHERE
                r.fecha_reserva BETWEEN
                    :fecha_inicio
                    AND :fecha_fin

                AND r.tipo_usuario = :tipo_usuario

                AND r.estado <> 'cancelado'

            GROUP BY
                l.id,
                l.titulo,
                l.autor

            ORDER BY
                total_reservas DESC,
                l.titulo ASC

            LIMIT {$limite}
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin,
            ':tipo_usuario' => $tipoUsuario
        ]);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    /**
     * Obtiene las reservas agrupadas por día
     * dentro del periodo seleccionado.
     */
    public function obtenerReservasPorDia(
        string $fechaInicio,
        string $fechaFin
    ): array {
        $sql = "
            SELECT
                fecha_reserva,

                SUM(
                    CASE
                        WHEN tipo_usuario = 'estudiante'
                        THEN 1
                        ELSE 0
                    END
                ) AS estudiantes,

                SUM(
                    CASE
                        WHEN tipo_usuario = 'profesor'
                        THEN 1
                        ELSE 0
                    END
                ) AS profesores

            FROM reservas

            WHERE
                fecha_reserva BETWEEN
                    :fecha_inicio
                    AND :fecha_fin

                AND estado <> 'cancelado'

            GROUP BY fecha_reserva

            ORDER BY fecha_reserva ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin
        ]);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }
}