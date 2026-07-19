<?php

require_once __DIR__ . '/../Core/Database.php';

class MateriaModel
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
                FROM materias
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
        $sql = "SELECT COUNT(*) AS total FROM materias WHERE nombre LIKE :busqueda";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":busqueda", "%{$busqueda}%", PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetch(PDO::FETCH_ASSOC)["total"];
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT id, nombre FROM materias WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    public function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        $sql = "SELECT id FROM materias WHERE nombre = :nombre";
        if ($excluirId !== null) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);
        if ($excluirId !== null) {
            $stmt->bindValue(":id", $excluirId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetch() !== false;
    }

    public function crear(string $nombre): void
    {
        $sql = "INSERT INTO materias (nombre) VALUES (:nombre)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function actualizar(int $id, string $nombre): void
    {
        $sql = "UPDATE materias SET nombre = :nombre WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Cuenta cuántos profesores usan esta materia.
     * Se usa antes de eliminar, para dar un mensaje claro
     * en vez de dejar que truene la restricción de la FK.
     */
    public function contarProfesoresAsociados(int $id): int
    {
        $sql = "SELECT COUNT(*) AS total FROM profesores WHERE materia_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetch(PDO::FETCH_ASSOC)["total"];
    }

    public function eliminar(int $id): void
    {
        $sql = "DELETE FROM materias WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}