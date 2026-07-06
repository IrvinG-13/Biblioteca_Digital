<?php

require_once __DIR__ . '/../Models/CarreraModel.php';
require_once __DIR__ . '/../Core/Sanitizer.php';
require_once __DIR__ . '/../Core/Csrf.php';

class CarreraController
{
    private CarreraModel $modelo;

    public function __construct()
    {
        $this->modelo = new CarreraModel();
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
    }

    public function listar(): array
    {
        $this->verificarSesionAdmin();

        $busqueda = Sanitizer::limpiarTexto($_GET["busqueda"] ?? "");
        $pagina = max(1, (int) ($_GET["pagina"] ?? 1));
        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $carreras = $this->modelo->listar($busqueda, $porPagina, $offset);
        $total = $this->modelo->contar($busqueda);
        $totalPaginas = (int) ceil($total / $porPagina);

        return [
            "carreras" => $carreras,
            "busqueda" => $busqueda,
            "paginaActual" => $pagina,
            "totalPaginas" => $totalPaginas,
        ];
    }

    /**
     * Valida que el nombre tenga una longitud razonable.
     * No usamos Validator::usuarioValido porque es específico de login;
     * aquí hacemos una validación propia del módulo de Carreras.
     */
    private function nombreValido(string $nombre): bool
    {
        return strlen($nombre) >= 3 && strlen($nombre) <= 100;
    }

    public function guardar(): void
    {
        $this->verificarSesionAdmin();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: carreras.php");
            exit;
        }

        $csrf = $_POST["csrf_token"] ?? "";
        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        $id = !empty($_POST["id"]) ? (int) $_POST["id"] : null;
        $nombre = Sanitizer::limpiarTexto($_POST["nombre"] ?? "");

        if (!$this->nombreValido($nombre)) {
            header("Location: carrera_form.php?error=nombre&id=" . $id);
            exit;
        }

        if ($this->modelo->existeNombre($nombre, $id)) {
            header("Location: carrera_form.php?error=duplicado&id=" . $id);
            exit;
        }

        if ($id === null) {
            $this->modelo->crear($nombre);
        } else {
            $this->modelo->actualizar($id, $nombre);
        }

        header("Location: carreras.php?exito=1");
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

        // Bloquea la eliminación si hay estudiantes asignados a esta carrera,
        // en vez de dejar que la restricción de la FK lance una excepción.
        if ($this->modelo->contarEstudiantesAsociados($id) > 0) {
            header("Location: carreras.php?error=tieneestudiantes");
            exit;
        }

        $this->modelo->eliminar($id);

        header("Location: carreras.php?exito=1");
        exit;
    }
}