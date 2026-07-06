<?php

require_once __DIR__ . '/../Core/Database.php';

class UsuarioModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    /**
     * Lista usuarios con buscador y paginación.
     * $busqueda filtra por el campo "usuario".
     */
    public function listar(string $busqueda, int $limite, int $offset): array
    {
        $sql = "SELECT id, usuario, rol, bloqueado, intentos_fallidos, created_at
                FROM usuarios
                WHERE usuario LIKE :busqueda
                ORDER BY id DESC
                LIMIT :limite OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":busqueda", "%{$busqueda}%", PDO::PARAM_STR);
        $stmt->bindValue(":limite", $limite, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta el total de usuarios que coinciden con la búsqueda.
     * Necesario para calcular el número de páginas.
     */
    public function contar(string $busqueda): int
    {
        $sql = "SELECT COUNT(*) AS total FROM usuarios WHERE usuario LIKE :busqueda";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":busqueda", "%{$busqueda}%", PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetch(PDO::FETCH_ASSOC)["total"];
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT id, usuario, rol, bloqueado, intentos_fallidos FROM usuarios WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    /**
     * Verifica si el nombre de usuario ya existe.
     * $excluirId se usa al editar, para no chocar contra sí mismo.
     */
    public function existeUsuario(string $usuario, ?int $excluirId = null): bool
    {
        $sql = "SELECT id FROM usuarios WHERE usuario = :usuario";
        if ($excluirId !== null) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":usuario", $usuario);
        if ($excluirId !== null) {
            $stmt->bindParam(":id", $excluirId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetch() !== false;
    }

    public function crear(string $usuario, string $passwordHash, string $rol): void
    {
        $sql = "INSERT INTO usuarios (usuario, password_hash, rol)
                VALUES (:usuario, :password_hash, :rol)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":usuario", $usuario);
        $stmt->bindParam(":password_hash", $passwordHash);
        $stmt->bindParam(":rol", $rol);
        $stmt->execute();
    }

    /**
     * Actualiza usuario y rol. La contraseña es opcional:
     * si viene null, no se toca (para no obligar a reescribirla al editar).
     */
    public function actualizar(int $id, string $usuario, string $rol, ?string $passwordHash): void
    {
        if ($passwordHash !== null) {
            $sql = "UPDATE usuarios
                    SET usuario = :usuario, rol = :rol, password_hash = :password_hash
                    WHERE id = :id";
        } else {
            $sql = "UPDATE usuarios
                    SET usuario = :usuario, rol = :rol
                    WHERE id = :id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":usuario", $usuario);
        $stmt->bindParam(":rol", $rol);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        if ($passwordHash !== null) {
            $stmt->bindParam(":password_hash", $passwordHash);
        }
        $stmt->execute();
    }

    /**
     * Baja lógica: bloquea o reactiva un usuario.
     * $bloqueado = 1 (baja/bloquear) o 0 (reactivar).
     * Al reactivar, reinicia también los intentos fallidos.
     */
    public function cambiarEstado(int $id, int $bloqueado): void
    {
        $sql = "UPDATE usuarios
                SET bloqueado = :bloqueado, intentos_fallidos = 0
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":bloqueado", $bloqueado, PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Elimina físicamente un usuario.
     * Seguro porque logs_acceso tiene ON DELETE SET NULL en la FK.
     */
    public function eliminar(int $id): void
    {
        $sql = "DELETE FROM usuarios WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}