<?php

require_once __DIR__ . '/../Core/Database.php';

class AuthModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    public function buscarUsuario(string $usuario): ?array
    {
        $sql = "SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":usuario", $usuario);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    public function registrarLog(?int $usuarioId, string $usuarioTxt, string $ip, string $resultado): void
    {
        $sql = "INSERT INTO logs_acceso (usuario_id, usuario_txt, ip, resultado)
                VALUES (:usuario_id, :usuario_txt, :ip, :resultado)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":usuario_id", $usuarioId, $usuarioId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(":usuario_txt", $usuarioTxt);
        $stmt->bindParam(":ip", $ip);
        $stmt->bindParam(":resultado", $resultado);
        $stmt->execute();
    }

    public function sumarIntentoFallido(int $id, int $intentosActuales): void
    {
        $nuevosIntentos = $intentosActuales + 1;
        $bloqueado = $nuevosIntentos >= 3 ? 1 : 0;

        $sql = "UPDATE usuarios
                SET intentos_fallidos = :intentos, bloqueado = :bloqueado
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":intentos", $nuevosIntentos, PDO::PARAM_INT);
        $stmt->bindParam(":bloqueado", $bloqueado, PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function reiniciarIntentos(int $id): void
    {
        $sql = "UPDATE usuarios
                SET intentos_fallidos = 0
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}