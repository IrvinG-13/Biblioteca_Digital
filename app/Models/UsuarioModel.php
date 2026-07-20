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
        $sql = "SELECT id, usuario, rol, bloqueado, intentos_fallidos, cambio_password
                FROM usuarios WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

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

    /**
     * Crea un usuario. Siempre queda marcado con cambio_password = 1,
     * porque la contraseña inicial la definió el administrador,
     * no el propio dueño de la cuenta.
     */
    public function crear(string $usuario, string $passwordHash, string $rol): void
    {
        $sql = "INSERT INTO usuarios (usuario, password_hash, rol, cambio_password)
                VALUES (:usuario, :password_hash, :rol, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":usuario", $usuario);
        $stmt->bindParam(":password_hash", $passwordHash);
        $stmt->bindParam(":rol", $rol);
        $stmt->execute();
    }

    /**
     * Actualiza usuario y rol. La contraseña es opcional:
     * si viene null, no se toca. Si el admin sí escribe una
     * contraseña nueva, se marca cambio_password = 1 de nuevo,
     * porque otra vez fue el admin quien la definió.
     */
    public function actualizar(int $id, string $usuario, string $rol, ?string $passwordHash): void
    {
        if ($passwordHash !== null) {
            $sql = "UPDATE usuarios
                    SET usuario = :usuario, rol = :rol,
                        password_hash = :password_hash, cambio_password = 1
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

    public function eliminar(int $id): void
    {
        $sql = "DELETE FROM usuarios WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Obtiene el hash de contraseña actual de un usuario,
     * necesario para validar la "contraseña actual" antes
     * de permitir el cambio.
     */
    public function obtenerHashPassword(int $id): ?string
    {
        $sql = "SELECT password_hash FROM usuarios WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado["password_hash"] : null;
    }

    /**
     * Cambia la contraseña del propio usuario y apaga la bandera
     * cambio_password, ya que ahora la contraseña la definió él mismo.
     */
    public function cambiarPasswordPropia(int $id, string $passwordHash): void
    {
        $sql = "UPDATE usuarios
                SET password_hash = :password_hash, cambio_password = 0
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":password_hash", $passwordHash);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}