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
     * Lista estudiantes con buscador (por CIP, nombre o apellido) y paginación.
     * Se hace JOIN con carreras para mostrar el nombre en vez del id.
     */
    public function listar(string $busqueda, int $limite, int $offset): array
    {
        $sql = "SELECT e.id, e.cip, e.primer_nombre, e.segundo_nombre,
                       e.primer_apellido, e.segundo_apellido, e.fecha_nacimiento,
                       e.carrera_id, e.usuario_id, c.nombre AS carrera_nombre
                FROM estudiantes e
                INNER JOIN carreras c ON c.id = e.carrera_id
                WHERE e.cip LIKE :busqueda
                   OR e.primer_nombre LIKE :busqueda
                   OR e.primer_apellido LIKE :busqueda
                ORDER BY e.id DESC
                LIMIT :limite OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":busqueda", "%{$busqueda}%", PDO::PARAM_STR);
        $stmt->bindValue(":limite", $limite, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contar(string $busqueda): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM estudiantes
                WHERE cip LIKE :busqueda
                   OR primer_nombre LIKE :busqueda
                   OR primer_apellido LIKE :busqueda";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":busqueda", "%{$busqueda}%", PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetch(PDO::FETCH_ASSOC)["total"];
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT id, cip, primer_nombre, segundo_nombre, primer_apellido,
                       segundo_apellido, fecha_nacimiento, carrera_id, usuario_id
                FROM estudiantes
                WHERE id = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    /**
     * Verifica si el CIP ya existe (requisito: no duplicar cédula/identificación).
     * $excluirId se usa al editar, para no chocar contra sí mismo.
     */
    public function existeCip(string $cip, ?int $excluirId = null): bool
    {
        $sql = "SELECT id FROM estudiantes WHERE cip = :cip";
        if ($excluirId !== null) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":cip", $cip);
        if ($excluirId !== null) {
            $stmt->bindParam(":id", $excluirId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetch() !== false;
    }

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
        $sql = "INSERT INTO estudiantes
                    (cip, primer_nombre, segundo_nombre, primer_apellido,
                     segundo_apellido, fecha_nacimiento, carrera_id, usuario_id)
                VALUES
                    (:cip, :primer_nombre, :segundo_nombre, :primer_apellido,
                     :segundo_apellido, :fecha_nacimiento, :carrera_id, :usuario_id)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":cip", $cip);
        $stmt->bindParam(":primer_nombre", $primerNombre);
        $stmt->bindValue(":segundo_nombre", $segundoNombre, $segundoNombre === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(":primer_apellido", $primerApellido);
        $stmt->bindValue(":segundo_apellido", $segundoApellido, $segundoApellido === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(":fecha_nacimiento", $fechaNacimiento);
        $stmt->bindParam(":carrera_id", $carreraId, PDO::PARAM_INT);
        $stmt->bindValue(":usuario_id", $usuarioId, $usuarioId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();
    }

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
        $sql = "UPDATE estudiantes
                SET cip = :cip,
                    primer_nombre = :primer_nombre,
                    segundo_nombre = :segundo_nombre,
                    primer_apellido = :primer_apellido,
                    segundo_apellido = :segundo_apellido,
                    fecha_nacimiento = :fecha_nacimiento,
                    carrera_id = :carrera_id,
                    usuario_id = :usuario_id
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":cip", $cip);
        $stmt->bindParam(":primer_nombre", $primerNombre);
        $stmt->bindValue(":segundo_nombre", $segundoNombre, $segundoNombre === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(":primer_apellido", $primerApellido);
        $stmt->bindValue(":segundo_apellido", $segundoApellido, $segundoApellido === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(":fecha_nacimiento", $fechaNacimiento);
        $stmt->bindParam(":carrera_id", $carreraId, PDO::PARAM_INT);
        $stmt->bindValue(":usuario_id", $usuarioId, $usuarioId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Cuenta cuántas reservas tiene este estudiante.
     * Se usa antes de eliminar (reservas tiene ON DELETE RESTRICT hacia estudiantes).
     */
    public function contarReservasAsociadas(int $id): int
    {
        $sql = "SELECT COUNT(*) AS total FROM reservas WHERE estudiante_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetch(PDO::FETCH_ASSOC)["total"];
    }

    public function eliminar(int $id): void
    {
        $sql = "DELETE FROM estudiantes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Usuarios con rol 'estudiante' que todavía no están vinculados
     * a ningún registro académico (para el <select> del formulario).
     * Al editar, incluye también el usuario ya vinculado a ESE estudiante.
     */
    public function obtenerUsuariosDisponibles(?int $usuarioIdActual = null): array
    {
        $sql = "SELECT u.id, u.usuario
                FROM usuarios u
                LEFT JOIN estudiantes e ON e.usuario_id = u.id
                WHERE u.rol = 'estudiante'
                  AND (e.id IS NULL OR u.id = :usuario_actual)
                ORDER BY u.usuario ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":usuario_actual", $usuarioIdActual, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}