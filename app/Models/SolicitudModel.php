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
     * Fragmento SQL reutilizable: obtiene el nombre del solicitante
     * (estudiante o profesor) según tipo_solicitante.
     */
    private function seleccionNombreSolicitante(): string
    {
        return "
            CASE
                WHEN s.tipo_solicitante = 'profesor'
                    THEN TRIM(
                        CONCAT_WS(
                            ' ',
                            NULLIF(p.primer_nombre, ''),
                            NULLIF(p.segundo_nombre, ''),
                            NULLIF(p.primer_apellido, ''),
                            NULLIF(p.segundo_apellido, '')
                        )
                    )
                ELSE TRIM(
                    CONCAT_WS(
                        ' ',
                        NULLIF(e.primer_nombre, ''),
                        NULLIF(e.segundo_nombre, ''),
                        NULLIF(e.primer_apellido, ''),
                        NULLIF(e.segundo_apellido, '')
                    )
                )
            END AS nombre_solicitante,
            CASE
                WHEN s.tipo_solicitante = 'profesor'
                    THEN p.cedula
                ELSE e.cip
            END AS identificacion_solicitante,
            CASE
                WHEN s.tipo_solicitante = 'profesor'
                    THEN pm.nombre
                ELSE c.nombre
            END AS carrera_o_materia
        ";
    }

    /**
     * Obtiene las solicitudes para el panel administrativo.
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
        $nombreSolicitante = $this->seleccionNombreSolicitante();
        $sql = "
            SELECT
                s.id,
                s.estudiante_id,
                s.profesor_id,
                s.tipo_solicitante,
                s.titulo_solicitado,
                s.area,
                s.comentario,
                s.estado,
                s.usuario_gestor_id,
                s.observacion_admin,
                s.fecha_respuesta,
                s.fecha,
                {$nombreSolicitante},
                ug.usuario AS gestor_usuario
            FROM solicitudes s
            LEFT JOIN estudiantes e
                ON e.id = s.estudiante_id
            LEFT JOIN carreras c
                ON c.id = e.carrera_id
            LEFT JOIN profesores p
                ON p.id = s.profesor_id
            LEFT JOIN materias pm
                ON pm.id = p.materia_id
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
            $stmt->bindValue($nombre, $valor, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
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
            $stmt->bindValue($nombre, $valor, PDO::PARAM_STR);
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
        $nombreSolicitante = $this->seleccionNombreSolicitante();
        $sql = "
            SELECT
                s.id,
                s.estudiante_id,
                s.profesor_id,
                s.tipo_solicitante,
                s.titulo_solicitado,
                s.area,
                s.comentario,
                s.estado,
                s.usuario_gestor_id,
                s.observacion_admin,
                s.fecha_respuesta,
                s.fecha,
                {$nombreSolicitante},
                ug.usuario AS gestor_usuario
            FROM solicitudes s
            LEFT JOIN estudiantes e
                ON e.id = s.estudiante_id
            LEFT JOIN carreras c
                ON c.id = e.carrera_id
            LEFT JOIN profesores p
                ON p.id = s.profesor_id
            LEFT JOIN materias pm
                ON pm.id = p.materia_id
            LEFT JOIN usuarios ug
                ON ug.id = s.usuario_gestor_id
            WHERE s.id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }
    /**
     * Evita solicitudes pendientes duplicadas.
     * $tipoSolicitante indica si $solicitanteId es un estudiante_id o profesor_id.
     */
    public function existeSolicitudPendiente(
        string $tipoSolicitante,
        int $solicitanteId,
        string $titulo
    ): bool {
        $columna = $tipoSolicitante === 'profesor'
            ? 'profesor_id'
            : 'estudiante_id';
        $sql = "
            SELECT id
            FROM solicitudes
            WHERE {$columna} = :solicitante_id
              AND tipo_solicitante = :tipo_solicitante
              AND titulo_solicitado = :titulo
              AND estado = 'pendiente'
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':solicitante_id', $solicitanteId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_solicitante', $tipoSolicitante, PDO::PARAM_STR);
        $stmt->bindValue(':titulo', $titulo, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    /**
     * Crea una solicitud, ya sea de un estudiante o de un profesor.
     */
    public function crear(
        string $tipoSolicitante,
        int $solicitanteId,
        string $titulo,
        string $categoria,
        ?string $comentario = null
    ): void {
        $estudianteId = $tipoSolicitante === 'estudiante' ? $solicitanteId : null;
        $profesorId = $tipoSolicitante === 'profesor' ? $solicitanteId : null;
        $sql = "
            INSERT INTO solicitudes (
                estudiante_id,
                profesor_id,
                tipo_solicitante,
                titulo_solicitado,
                area,
                comentario,
                estado
            )
            VALUES (
                :estudiante_id,
                :profesor_id,
                :tipo_solicitante,
                :titulo,
                :categoria,
                :comentario,
                'pendiente'
            )
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':estudiante_id', $estudianteId, $estudianteId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':profesor_id', $profesorId, $profesorId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':tipo_solicitante', $tipoSolicitante, PDO::PARAM_STR);
        $stmt->bindValue(':titulo', $titulo, PDO::PARAM_STR);
        $stmt->bindValue(':categoria', $categoria, PDO::PARAM_STR);
        $stmt->bindValue(':comentario', $comentario, $comentario === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
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
        $estadosValidos = ['pendiente', 'aprobada', 'rechazada'];
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
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
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
        $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindValue(':usuario_gestor_id', $usuarioGestorId, PDO::PARAM_INT);
        $stmt->bindValue(':observacion_admin', $observacionAdmin, $observacionAdmin === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    /**
     * Obtiene las solicitudes de un solicitante (estudiante o profesor).
     */
    public function obtenerPorSolicitante(
        string $tipoSolicitante,
        int $solicitanteId
    ): array {
        $columna = $tipoSolicitante === 'profesor'
            ? 'profesor_id'
            : 'estudiante_id';
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
            WHERE {$columna} = :solicitante_id
              AND tipo_solicitante = :tipo_solicitante
            ORDER BY fecha DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':solicitante_id', $solicitanteId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_solicitante', $tipoSolicitante, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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