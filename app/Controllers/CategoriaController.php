<?php

require_once __DIR__ . '/../Models/CategoriaModel.php';
require_once __DIR__ . '/../Core/Sanitizer.php';
require_once __DIR__ . '/../Core/Csrf.php';

class CategoriaController
{
    private CategoriaModel $modelo;

    public function __construct()
    {
        $this->modelo = new CategoriaModel();
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

        $pagina = max(1, (int)($_GET["pagina"] ?? 1));

        $porPagina = 10;

        $offset = ($pagina - 1) * $porPagina;

        $categorias = $this->modelo->listar(
            $busqueda,
            $porPagina,
            $offset
        );

        $total = $this->modelo->contar($busqueda);

        $totalPaginas = ceil($total / $porPagina);

        return [
            "categorias" => $categorias,
            "busqueda" => $busqueda,
            "paginaActual" => $pagina,
            "totalPaginas" => $totalPaginas
        ];
    }

    public function obtenerPorId(int $id): ?array
    {
        $this->verificarSesionAdmin();

        return $this->modelo->obtenerPorId($id);
    }

    private function nombreValido(string $nombre): bool
    {
        return strlen($nombre) >= 3 && strlen($nombre) <= 100;
    }

    public function guardar(): void
    {
        $this->verificarSesionAdmin();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: categorias.php");
            exit;
        }

        $csrf = $_POST["csrf_token"] ?? "";

        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        $id = !empty($_POST["id"])
            ? (int)$_POST["id"]
            : null;

        $nombre = Sanitizer::limpiarTexto(
            $_POST["nombre"] ?? ""
        );

        if (!$this->nombreValido($nombre)) {

            header(
                "Location: categoria_form.php?error=nombre&id=" . $id
            );

            exit;
        }

        if ($this->modelo->existeNombre($nombre, $id)) {

            header(
                "Location: categoria_form.php?error=duplicado&id=" . $id
            );

            exit;
        }

        if ($id === null) {

            $this->modelo->crear($nombre);

        } else {

            $this->modelo->actualizar($id, $nombre);

        }

        header("Location: categorias.php?exito=1");

        exit;
    }

    public function eliminar(): void
    {
        $this->verificarSesionAdmin();

        $csrf = $_POST["csrf_token"] ?? "";

        if (!Csrf::validarToken($csrf)) {

            die("Token CSRF inválido.");

        }

        $id = (int)($_POST["id"] ?? 0);

        if ($this->modelo->contarLibrosAsociados($id) > 0) {

            header("Location: categorias.php?error=tienelibros");

            exit;

        }

        $this->modelo->eliminar($id);

        header("Location: categorias.php?exito=1");

        exit;
    }
}