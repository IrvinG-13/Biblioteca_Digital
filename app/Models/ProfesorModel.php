<?php

require_once __DIR__ . '/../Core/Database.php';

class ProfesorModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    public function listar(string $busqueda, int $limite, int $offset): array
    {
        $sql = "SELECT
                    p.id, p.cedula, p.primer_nombre, p.segundo_nombre,
                    p.primer_apellido, p.segundo_apellido, p.materia_id,
                    p.usuario_id, m.nombre AS materia_nombre
                FROM profesores p
                INNER JOIN materias m ON m.id = p.materia_id
                WHERE p.cedula LIKE :busqueda_cedula
                   OR p.primer_nombre LIKE :busqueda_nombre
                   OR p.primer_apellido LIKE :busqueda_apellido
                ORDER BY p.id DESC
                LIMIT :limite OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $textoBusqueda = "%{$busqueda}%";
        $stmt->bindValue(":busqueda_cedula", $textoBusqueda, PDO::PARAM_STR);
        $stmt->bindValue(":busqueda_nombre", $textoBusqueda, PDO::PARAM_STR);
        $stmt->bindValue(":busqueda_apellido", $textoBusqueda, PDO::PARAM_STR);
        $stmt->bindValue(":limite", $limite, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contar(string $busqueda): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM profesores
                WHERE cedula LIKE :busqueda_cedula
                   OR primer_nombre LIKE :busqueda_nombre
                   OR primer_apellido LIKE :busqueda_apellido";

        $stmt = $this->db->prepare($sql);
        $textoBusqueda = "%{$busqueda}%";
        $stmt->bindValue(":busqueda_cedula", $textoBusqueda, PDO::PARAM_STR);
        $stmt->bindValue(":busqueda_nombre", $textoBusqueda, PDO::PARAM_STR);
        $stmt->bindValue(":busqueda_apellido", $textoBusqueda, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetch(PDO::FETCH_ASSOC)["total"];
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT
                    p.id, p.cedula, p.primer_nombre, p.segundo_nombre,
                    p.primer_apellido, p.segundo_apellido, p.materia_id,
                    p.usuario_id, m.nombre AS materia_nombre
                FROM profesores p
                INNER JOIN materias m ON m.id = p.materia_id
                WHERE p.id = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    public function obtenerPorUsuarioId(int $usuarioId): ?array
    {
        $sql = "SELECT
                    p.id, p.cedula, p.primer_nombre, p.segundo_nombre,
                    p.primer_apellido, p.segundo_apellido, p.materia_id,
                    p.usuario_id, m.nombre AS materia_nombre
                FROM profesores p
                INNER JOIN materias m ON m.id = p.materia_id
                WHERE p.usuario_id = :usuario_id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":usuario_id", $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    public function existeCedula(string $cedula, ?int $excluirId = null): bool
    {
        $sql = "SELECT id FROM profesores WHERE cedula = :cedula";
        if ($excluirId !== null) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":cedula", $cedula, PDO::PARAM_STR);
        if ($excluirId !== null) {
            $stmt->bindValue(":id", $excluirId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetch() !== false;
    }

    public function crear(
        string $cedula,
        string $primerNombre,
        ?string $segundoNombre,
        string $primerApellido,
        ?string $segundoApellido,
        int $materiaId,
        ?int $usuarioId
    ): void {
        $sql = "INSERT INTO profesores
                    (cedula, primer_nombre, segundo_nombre, primer_apellido,
                     segundo_apellido, materia_id, usuario_id)
                VALUES
                    (:cedula, :primer_nombre, :segundo_nombre, :primer_apellido,
                     :segundo_apellido, :materia_id, :usuario_id)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":cedula", $cedula, PDO::PARAM_STR);
        $stmt->bindValue(":primer_nombre", $primerNombre, PDO::PARAM_STR);
        $stmt->bindValue(":segundo_nombre", $segundoNombre, $segundoNombre === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":primer_apellido", $primerApellido, PDO::PARAM_STR);
        $stmt->bindValue(":segundo_apellido", $segundoApellido, $segundoApellido === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":materia_id", $materiaId, PDO::PARAM_INT);
        $stmt->bindValue(":usuario_id", $usuarioId, $usuarioId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();
    }

    public function actualizar(
        int $id,
        string $cedula,
        string $primerNombre,
        ?string $segundoNombre,
        string $primerApellido,
        ?string $segundoApellido,
        int $materiaId,
        ?int $usuarioId
    ): void {
        $sql = "UPDATE profesores
                SET cedula = :cedula,
                    primer_nombre = :primer_nombre,
                    segundo_nombre = :segundo_nombre,
                    primer_apellido = :primer_apellido,
                    segundo_apellido = :segundo_apellido,
                    materia_id = :materia_id,
                    usuario_id = :usuario_id
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":cedula", $cedula, PDO::PARAM_STR);
        $stmt->bindValue(":primer_nombre", $primerNombre, PDO::PARAM_STR);
        $stmt->bindValue(":segundo_nombre", $segundoNombre, $segundoNombre === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":primer_apellido", $primerApellido, PDO::PARAM_STR);
        $stmt->bindValue(":segundo_apellido", $segundoApellido, $segundoApellido === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":materia_id", $materiaId, PDO::PARAM_INT);
        $stmt->bindValue(":usuario_id", $usuarioId, $usuarioId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function eliminar(int $id): void
    {
        $sql = "DELETE FROM profesores WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function obtenerUsuariosDisponibles(?int $usuarioIdActual = null): array
    {
        $sql = "SELECT u.id, u.usuario
                FROM usuarios u
                LEFT JOIN profesores p ON p.usuario_id = u.id
                WHERE u.rol = 'profesor'
                  AND (p.id IS NULL OR u.id = :usuario_actual)
                ORDER BY u.usuario ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":usuario_actual", $usuarioIdActual, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}