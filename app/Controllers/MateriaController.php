<?php

require_once __DIR__ . '/../Models/MateriaModel.php';
require_once __DIR__ . '/../Core/Sanitizer.php';
require_once __DIR__ . '/../Core/Csrf.php';

class MateriaController
{
    private MateriaModel $modelo;

    public function __construct()
    {
        $this->modelo = new MateriaModel();
    }

    private function verificarSesionAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
            header("Location: login.php");
            exit;
        }

        require_once __DIR__ . '/../Core/SesionGuard.php';
        SesionGuard::bloquearSiCambioPasswordPendiente();
    }

    public function listar(): array
    {
        $this->verificarSesionAdmin();

        $busqueda = Sanitizer::limpiarTexto($_GET["busqueda"] ?? "");
        $pagina = max(1, (int) ($_GET["pagina"] ?? 1));
        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $materias = $this->modelo->listar($busqueda, $porPagina, $offset);
        $total = $this->modelo->contar($busqueda);
        $totalPaginas = (int) ceil($total / $porPagina);

        return [
            "materias" => $materias,
            "busqueda" => $busqueda,
            "paginaActual" => $pagina,
            "totalPaginas" => $totalPaginas,
        ];
    }

    public function obtenerTodas(): array
    {
        $this->verificarSesionAdmin();
        return $this->modelo->listar("", 1000, 0);
    }

    private function nombreValido(string $nombre): bool
    {
        return strlen($nombre) >= 3 && strlen($nombre) <= 100;
    }

    public function guardar(): void
    {
        $this->verificarSesionAdmin();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: materias.php");
            exit;
        }

        $csrf = $_POST["csrf_token"] ?? "";
        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        $id = !empty($_POST["id"]) ? (int) $_POST["id"] : null;
        $nombre = Sanitizer::limpiarTexto($_POST["nombre"] ?? "");

        if (!$this->nombreValido($nombre)) {
            header("Location: materia_form.php?error=nombre&id=" . $id);
            exit;
        }

        if ($this->modelo->existeNombre($nombre, $id)) {
            header("Location: materia_form.php?error=duplicado&id=" . $id);
            exit;
        }

        if ($id === null) {
            $this->modelo->crear($nombre);
        } else {
            $this->modelo->actualizar($id, $nombre);
        }

        header("Location: materias.php?exito=1");
        exit;
    }

    public function eliminar(): void
    {
        $this->verificarSesionAdmin();

        $csrf = $_POST["csrf_token"] ?? "";
        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        $id = (int) ($_POST["id"] ?? 0);

        if ($this->modelo->contarProfesoresAsociados($id) > 0) {
            header("Location: materias.php?error=tieneprofesores");
            exit;
        }

        $this->modelo->eliminar($id);

        header("Location: materias.php?exito=1");
        exit;
    }
}