<?php

require_once __DIR__ . '/../Core/Database.php';

class SolicitudModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    /**
     * Obtiene las solicitudes para el panel administrativo.
     *
     * La columna "area" se conserva en la base de datos,
     * pero ahora guarda el nombre de una categoría real.
     */
    public function obtenerTodas(
        string $categoria = '',
        string $estado = 'pendiente',
        int $limite = 10,
        int $offset = 0
    ): array {
        $condiciones = [];
        $parametros = [];

        if ($categoria !== '') {
            $condiciones[] = 's.area = :categoria';
            $parametros[':categoria'] = $categoria;
        }

        if ($estado === 'respondidas') {
            $condiciones[] = "
                s.estado IN ('aprobada', 'rechazada')
            ";
        } elseif ($estado !== '' && $estado !== 'todas') {
            $condiciones[] = 's.estado = :estado';
            $parametros[':estado'] = $estado;
        }

        $where = '';

        if (!empty($condiciones)) {
            $where = 'WHERE ' . implode(' AND ', $condiciones);
        }

        $sql = "
            SELECT
                s.id,
                s.estudiante_id,
                s.titulo_solicitado,
                s.area,
                s.comentario,
                s.estado,
                s.usuario_gestor_id,
                s.observacion_admin,
                s.fecha_respuesta,
                s.fecha,

                e.cip,
                e.primer_nombre,
                e.segundo_nombre,
                e.primer_apellido,
                e.segundo_apellido,

                c.nombre AS carrera_nombre,
                ug.usuario AS gestor_usuario

            FROM solicitudes s

            INNER JOIN estudiantes e
                ON e.id = s.estudiante_id

            LEFT JOIN carreras c
                ON c.id = e.carrera_id

            LEFT JOIN usuarios ug
                ON ug.id = s.usuario_gestor_id

            {$where}

            ORDER BY
                FIELD(
                    s.estado,
                    'pendiente',
                    'aprobada',
                    'rechazada'
                ),
                s.fecha DESC

            LIMIT :limite
            OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($parametros as $nombre => $valor) {
            $stmt->bindValue(
                $nombre,
                $valor,
                PDO::PARAM_STR
            );
        }

        $stmt->bindValue(
            ':limite',
            $limite,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta las solicitudes usando los mismos filtros.
     */
    public function contarTotal(
        string $categoria = '',
        string $estado = 'pendiente'
    ): int {
        $condiciones = [];
        $parametros = [];

        if ($categoria !== '') {
            $condiciones[] = 'area = :categoria';
            $parametros[':categoria'] = $categoria;
        }

        if ($estado === 'respondidas') {
            $condiciones[] = "
                estado IN ('aprobada', 'rechazada')
            ";
        } elseif ($estado !== '' && $estado !== 'todas') {
            $condiciones[] = 'estado = :estado';
            $parametros[':estado'] = $estado;
        }

        $where = '';

        if (!empty($condiciones)) {
            $where = 'WHERE ' . implode(' AND ', $condiciones);
        }

        $sql = "
            SELECT COUNT(*) AS total
            FROM solicitudes
            {$where}
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($parametros as $nombre => $valor) {
            $stmt->bindValue(
                $nombre,
                $valor,
                PDO::PARAM_STR
            );
        }

        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($resultado['total'] ?? 0);
    }

    /**
     * Obtiene una solicitud por su ID.
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "
            SELECT
                s.id,
                s.estudiante_id,
                s.titulo_solicitado,
                s.area,
                s.comentario,
                s.estado,
                s.usuario_gestor_id,
                s.observacion_admin,
                s.fecha_respuesta,
                s.fecha,

                e.cip,
                e.primer_nombre,
                e.segundo_nombre,
                e.primer_apellido,
                e.segundo_apellido,

                c.nombre AS carrera_nombre,
                ug.usuario AS gestor_usuario

            FROM solicitudes s

            INNER JOIN estudiantes e
                ON e.id = s.estudiante_id

            LEFT JOIN carreras c
                ON c.id = e.carrera_id

            LEFT JOIN usuarios ug
                ON ug.id = s.usuario_gestor_id

            WHERE s.id = :id

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    /**
     * Evita solicitudes pendientes duplicadas.
     */
    public function existeSolicitudPendiente(
        int $estudianteId,
        string $titulo
    ): bool {
        $sql = "
            SELECT id

            FROM solicitudes

            WHERE estudiante_id = :estudiante_id
              AND titulo_solicitado = :titulo
              AND estado = 'pendiente'

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':estudiante_id',
            $estudianteId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':titulo',
            $titulo,
            PDO::PARAM_STR
        );

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    /**
     * Crea una solicitud.
     *
     * El nombre de la categoría se guarda en la columna "area"
     * para conservar la estructura actual de la base de datos.
     */
    public function crear(
        int $estudianteId,
        string $titulo,
        string $categoria,
        ?string $comentario = null
    ): void {
        $sql = "
            INSERT INTO solicitudes (
                estudiante_id,
                titulo_solicitado,
                area,
                comentario,
                estado
            )
            VALUES (
                :estudiante_id,
                :titulo,
                :categoria,
                :comentario,
                'pendiente'
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':estudiante_id',
            $estudianteId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':titulo',
            $titulo,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':categoria',
            $categoria,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':comentario',
            $comentario,
            $comentario === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );

        $stmt->execute();
    }

    /**
     * Cambia el estado de una solicitud.
     */
    public function cambiarEstado(
        int $id,
        string $estado,
        int $usuarioGestorId,
        ?string $observacionAdmin = null
    ): bool {
        $estadosValidos = [
            'pendiente',
            'aprobada',
            'rechazada'
        ];

        if (!in_array($estado, $estadosValidos, true)) {
            return false;
        }

        if ($estado === 'pendiente') {
            $sql = "
                UPDATE solicitudes

                SET
                    estado = 'pendiente',
                    usuario_gestor_id = NULL,
                    observacion_admin = NULL,
                    fecha_respuesta = NULL

                WHERE id = :id
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(
                ':id',
                $id,
                PDO::PARAM_INT
            );

            return $stmt->execute();
        }

        $sql = "
            UPDATE solicitudes

            SET
                estado = :estado,
                usuario_gestor_id = :usuario_gestor_id,
                observacion_admin = :observacion_admin,
                fecha_respuesta = CURRENT_TIMESTAMP

            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':estado',
            $estado,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':usuario_gestor_id',
            $usuarioGestorId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':observacion_admin',
            $observacionAdmin,
            $observacionAdmin === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        return $stmt->execute();
    }

    /**
     * Obtiene las solicitudes de un estudiante.
     */
    public function obtenerPorEstudiante(
        int $estudianteId
    ): array {
        $sql = "
            SELECT
                id,
                titulo_solicitado,
                area,
                comentario,
                estado,
                observacion_admin,
                fecha_respuesta,
                fecha

            FROM solicitudes

            WHERE estudiante_id = :estudiante_id

            ORDER BY fecha DESC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':estudiante_id',
            $estudianteId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta las solicitudes de un estudiante.
     */
    public function contarPorEstudiante(
        int $estudianteId
    ): int {
        $sql = "
            SELECT COUNT(*) AS total

            FROM solicitudes

            WHERE estudiante_id = :estudiante_id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':estudiante_id',
            $estudianteId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($resultado['total'] ?? 0);
    }

    /**
     * Devuelve las categorías reales registradas por el administrador.
     */
    public function obtenerCategorias(): array
    {
        $sql = "
            SELECT nombre
            FROM categorias
            ORDER BY nombre ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return array_column(
            $stmt->fetchAll(PDO::FETCH_ASSOC),
            'nombre'
        );
    }

    /**
     * Alias para conservar compatibilidad con código anterior.
     */
    public function obtenerAreas(): array
    {
        return $this->obtenerCategorias();
    }
}