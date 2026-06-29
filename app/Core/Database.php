<?php

class Database
{
    private string $host = "localhost";
    private string $db = "biblioteca_digital";
    private string $user = "root";
    private string $pass = "";
    private string $charset = "utf8mb4";

    public function conectar(): PDO
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
            $pdo = new PDO($dsn, $this->user, $this->pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos.");
        }
    }
}