<?php

require_once __DIR__ . '/../Core/Database.php';

class CategoriaModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    public function listar(string $busqueda, int $limite, int $offset): array
    {
        $sql = "SELECT id, nombre
                FROM categorias
                WHERE nombre LIKE :busqueda
                ORDER BY nombre ASC
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
                FROM categorias
                WHERE nombre LIKE :busqueda";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":busqueda", "%{$busqueda}%", PDO::PARAM_STR);

        $stmt->execute();

        return (int)$stmt->fetch(PDO::FETCH_ASSOC)["total"];
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT id, nombre
                FROM categorias
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    public function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        $sql = "SELECT id
                FROM categorias
                WHERE nombre = :nombre";

        if ($excluirId !== null) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(":nombre", $nombre);

        if ($excluirId !== null) {
            $stmt->bindParam(":id", $excluirId, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetch() !== false;
    }

    public function crear(string $nombre): void
    {
        $sql = "INSERT INTO categorias(nombre)
                VALUES(:nombre)";

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(":nombre", $nombre);

        $stmt->execute();
    }

    public function actualizar(int $id, string $nombre): void
    {
        $sql = "UPDATE categorias
                SET nombre = :nombre
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function contarLibrosAsociados(int $id): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM libros
                WHERE categoria_id = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        return (int)$stmt->fetch(PDO::FETCH_ASSOC)["total"];
    }

    public function eliminar(int $id): void
    {
        $sql = "DELETE FROM categorias
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function obtenerTodas(): array
    {
        $sql = "SELECT id, nombre
                FROM categorias
                ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}