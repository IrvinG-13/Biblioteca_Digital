<?php

require_once __DIR__ . '/../Core/Database.php';

class EstudianteModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    /**
     * Lista estudiantes con búsqueda y paginación.
     *
     * Permite buscar por:
     * - CIP
     * - Primer nombre
     * - Primer apellido
     */
    public function listar(
        string $busqueda,
        int $limite,
        int $offset
    ): array {
        $sql = "
            SELECT
                e.id,
                e.cip,
                e.primer_nombre,
                e.segundo_nombre,
                e.primer_apellido,
                e.segundo_apellido,
                e.fecha_nacimiento,
                e.carrera_id,
                e.usuario_id,
                c.nombre AS carrera_nombre
            FROM estudiantes e
            INNER JOIN carreras c
                ON c.id = e.carrera_id
            WHERE
                e.cip LIKE :busqueda_cip
                OR e.primer_nombre LIKE :busqueda_nombre
                OR e.primer_apellido LIKE :busqueda_apellido
            ORDER BY e.id DESC
            LIMIT :limite
            OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);

        $textoBusqueda = '%' . $busqueda . '%';

        $stmt->bindValue(
            ':busqueda_cip',
            $textoBusqueda,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':busqueda_nombre',
            $textoBusqueda,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':busqueda_apellido',
            $textoBusqueda,
            PDO::PARAM_STR
        );

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
     * Cuenta los estudiantes encontrados para la paginación.
     */
    public function contar(string $busqueda): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM estudiantes
            WHERE
                cip LIKE :busqueda_cip
                OR primer_nombre LIKE :busqueda_nombre
                OR primer_apellido LIKE :busqueda_apellido
        ";

        $stmt = $this->db->prepare($sql);

        $textoBusqueda = '%' . $busqueda . '%';

        $stmt->execute([
            ':busqueda_cip' => $textoBusqueda,
            ':busqueda_nombre' => $textoBusqueda,
            ':busqueda_apellido' => $textoBusqueda
        ]);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($resultado['total'] ?? 0);
    }

    /**
     * Obtiene un estudiante mediante su ID académico.
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "
            SELECT
                e.id,
                e.cip,
                e.primer_nombre,
                e.segundo_nombre,
                e.primer_apellido,
                e.segundo_apellido,
                e.fecha_nacimiento,
                e.carrera_id,
                e.usuario_id,
                c.nombre AS carrera_nombre
            FROM estudiantes e
            INNER JOIN carreras c
                ON c.id = e.carrera_id
            WHERE e.id = :id
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
     * Obtiene el estudiante relacionado con el usuario
     * que inició sesión.
     *
     * Este método será utilizado para:
     * - Solicitar libros.
     * - Consultar sus solicitudes.
     * - Consultar sus reservas.
     *
     * El estudiante no tendrá que escribir su ID manualmente.
     */
    public function obtenerPorUsuarioId(int $usuarioId): ?array
    {
        $sql = "
            SELECT
                e.id,
                e.cip,
                e.primer_nombre,
                e.segundo_nombre,
                e.primer_apellido,
                e.segundo_apellido,
                e.fecha_nacimiento,
                e.carrera_id,
                e.usuario_id,
                c.nombre AS carrera_nombre
            FROM estudiantes e
            INNER JOIN carreras c
                ON c.id = e.carrera_id
            WHERE e.usuario_id = :usuario_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':usuario_id',
            $usuarioId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    /**
     * Verifica si ya existe un estudiante con el mismo CIP.
     *
     * $excluirId se utiliza al editar para no comparar
     * el registro contra sí mismo.
     */
    public function existeCip(
        string $cip,
        ?int $excluirId = null
    ): bool {
        $sql = "
            SELECT id
            FROM estudiantes
            WHERE cip = :cip
        ";

        if ($excluirId !== null) {
            $sql .= " AND id != :id";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':cip',
            $cip,
            PDO::PARAM_STR
        );

        if ($excluirId !== null) {
            $stmt->bindValue(
                ':id',
                $excluirId,
                PDO::PARAM_INT
            );
        }

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    /**
     * Registra un estudiante.
     */
    public function crear(
        string $cip,
        string $primerNombre,
        ?string $segundoNombre,
        string $primerApellido,
        ?string $segundoApellido,
        string $fechaNacimiento,
        int $carreraId,
        ?int $usuarioId
    ): void {
        $sql = "
            INSERT INTO estudiantes (
                cip,
                primer_nombre,
                segundo_nombre,
                primer_apellido,
                segundo_apellido,
                fecha_nacimiento,
                carrera_id,
                usuario_id
            )
            VALUES (
                :cip,
                :primer_nombre,
                :segundo_nombre,
                :primer_apellido,
                :segundo_apellido,
                :fecha_nacimiento,
                :carrera_id,
                :usuario_id
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':cip',
            $cip,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':primer_nombre',
            $primerNombre,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':segundo_nombre',
            $segundoNombre,
            $segundoNombre === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':primer_apellido',
            $primerApellido,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':segundo_apellido',
            $segundoApellido,
            $segundoApellido === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':fecha_nacimiento',
            $fechaNacimiento,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':carrera_id',
            $carreraId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':usuario_id',
            $usuarioId,
            $usuarioId === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_INT
        );

        $stmt->execute();
    }

    /**
     * Actualiza un estudiante.
     */
    public function actualizar(
        int $id,
        string $cip,
        string $primerNombre,
        ?string $segundoNombre,
        string $primerApellido,
        ?string $segundoApellido,
        string $fechaNacimiento,
        int $carreraId,
        ?int $usuarioId
    ): void {
        $sql = "
            UPDATE estudiantes
            SET
                cip = :cip,
                primer_nombre = :primer_nombre,
                segundo_nombre = :segundo_nombre,
                primer_apellido = :primer_apellido,
                segundo_apellido = :segundo_apellido,
                fecha_nacimiento = :fecha_nacimiento,
                carrera_id = :carrera_id,
                usuario_id = :usuario_id
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':cip',
            $cip,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':primer_nombre',
            $primerNombre,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':segundo_nombre',
            $segundoNombre,
            $segundoNombre === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':primer_apellido',
            $primerApellido,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':segundo_apellido',
            $segundoApellido,
            $segundoApellido === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':fecha_nacimiento',
            $fechaNacimiento,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':carrera_id',
            $carreraId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':usuario_id',
            $usuarioId,
            $usuarioId === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();
    }

    /**
     * Cuenta las reservas relacionadas con un estudiante.
     *
     * La tabla reservas ya no contiene estudiante_id.
     * Ahora utiliza usuario_id y tipo_usuario.
     */
    public function contarReservasAsociadas(int $id): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM reservas r
            INNER JOIN estudiantes e
                ON e.usuario_id = r.usuario_id
            WHERE e.id = :estudiante_id
              AND r.tipo_usuario = 'estudiante'
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':estudiante_id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($resultado['total'] ?? 0);
    }

    /**
     * Cuenta las solicitudes de libros relacionadas
     * con el estudiante.
     *
     * Esto evita eliminar un estudiante que todavía
     * tenga solicitudes registradas.
     */
    public function contarSolicitudesAsociadas(int $id): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM solicitudes
            WHERE estudiante_id = :estudiante_id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':estudiante_id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($resultado['total'] ?? 0);
    }

    /**
     * Elimina un estudiante.
     *
     * La base de datos impedirá la eliminación si todavía
     * existen registros relacionados mediante ON DELETE RESTRICT.
     */
    public function eliminar(int $id): void
    {
        $sql = "
            DELETE FROM estudiantes
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();
    }

    /**
     * Obtiene usuarios con rol estudiante que todavía
     * no estén vinculados a otro registro académico.
     *
     * Al editar, también permite mostrar el usuario que
     * ya está vinculado al estudiante actual.
     */
    public function obtenerUsuariosDisponibles(
        ?int $usuarioIdActual = null
    ): array {
        if ($usuarioIdActual === null) {
            $sql = "
                SELECT
                    u.id,
                    u.usuario
                FROM usuarios u
                LEFT JOIN estudiantes e
                    ON e.usuario_id = u.id
                WHERE u.rol = 'estudiante'
                  AND e.id IS NULL
                ORDER BY u.usuario ASC
            ";

            $stmt = $this->db->prepare($sql);
        } else {
            $sql = "
                SELECT
                    u.id,
                    u.usuario
                FROM usuarios u
                LEFT JOIN estudiantes e
                    ON e.usuario_id = u.id
                WHERE u.rol = 'estudiante'
                  AND (
                      e.id IS NULL
                      OR u.id = :usuario_actual
                  )
                ORDER BY u.usuario ASC
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(
                ':usuario_actual',
                $usuarioIdActual,
                PDO::PARAM_INT
            );
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}