<?php

require_once __DIR__ . '/../Models/UsuarioModel.php';
require_once __DIR__ . '/../Core/Sanitizer.php';
require_once __DIR__ . '/../Core/Validator.php';
require_once __DIR__ . '/../Core/Csrf.php';

class UsuarioController
{
    private UsuarioModel $modelo;

    public function __construct()
    {
        $this->modelo = new UsuarioModel();
    }

    /**
     * Verifica que haya sesión activa y que el rol sea admin.
     * Esta pantalla es exclusiva del Administrador.
     */
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

    /**
     * Lista usuarios con buscador y paginación.
     * Llamado desde usuarios.php (GET).
     */
    public function listar(): array
    {
        $this->verificarSesionAdmin();

        $busqueda = Sanitizer::limpiarTexto($_GET["busqueda"] ?? "");
        $pagina = max(1, (int) ($_GET["pagina"] ?? 1));
        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $usuarios = $this->modelo->listar($busqueda, $porPagina, $offset);
        $total = $this->modelo->contar($busqueda);
        $totalPaginas = (int) ceil($total / $porPagina);

        return [
            "usuarios" => $usuarios,
            "busqueda" => $busqueda,
            "paginaActual" => $pagina,
            "totalPaginas" => $totalPaginas,
        ];
    }

    /**
     * Procesa alta o edición (según si viene "id" en el POST).
     * Llamado desde usuario_procesar.php (POST).
     */
    public function guardar(): void
    {
        $this->verificarSesionAdmin();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: usuarios.php");
            exit;
        }

        $csrf = $_POST["csrf_token"] ?? "";
        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        $id = !empty($_POST["id"]) ? (int) $_POST["id"] : null;
        $usuario = Sanitizer::limpiarTexto($_POST["usuario"] ?? "");
        $password = $_POST["password"] ?? "";
        $rol = Sanitizer::limpiarTexto($_POST["rol"] ?? "");

        if (!Validator::usuarioValido($usuario)) {
            header("Location: usuario_form.php?error=usuario&id=" . $id);
            exit;
        }

        if (!in_array($rol, ["admin", "estudiante"], true)) {
            header("Location: usuario_form.php?error=rol&id=" . $id);
            exit;
        }

        // La contraseña solo es obligatoria al crear (id es null).
        // Al editar, si viene vacía, no se cambia.
        if ($id === null && !Validator::passwordValida($password)) {
            header("Location: usuario_form.php?error=password");
            exit;
        }

        if ($password !== "" && !Validator::passwordValida($password)) {
            header("Location: usuario_form.php?error=password&id=" . $id);
            exit;
        }

        if ($this->modelo->existeUsuario($usuario, $id)) {
            header("Location: usuario_form.php?error=duplicado&id=" . $id);
            exit;
        }

        if ($id === null) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $this->modelo->crear($usuario, $hash, $rol);
        } else {
            $hash = $password !== "" ? password_hash($password, PASSWORD_BCRYPT) : null;
            $this->modelo->actualizar($id, $usuario, $rol, $hash);
        }

        header("Location: usuarios.php?exito=1");
        exit;
    }

    /**
     * Bloquea o reactiva un usuario (baja lógica reversible).
     * Llamado desde usuario_estado.php (POST).
     */
    public function cambiarEstado(): void
    {
        $this->verificarSesionAdmin();

        $csrf = $_POST["csrf_token"] ?? "";
        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        $id = (int) ($_POST["id"] ?? 0);
        $bloqueado = (int) ($_POST["bloqueado"] ?? 0);

        if ($id === (int) $_SESSION["usuario_id"]) {
            header("Location: usuarios.php?error=automodificacion");
            exit;
        }

        $this->modelo->cambiarEstado($id, $bloqueado);

        header("Location: usuarios.php?exito=1");
        exit;
    }

    /**
     * Elimina físicamente un usuario (baja física, irreversible).
     * Llamado desde usuario_eliminar.php (POST).
     */
    public function eliminar(): void
    {
        $this->verificarSesionAdmin();

        $csrf = $_POST["csrf_token"] ?? "";
        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        $id = (int) ($_POST["id"] ?? 0);

        if ($id === (int) $_SESSION["usuario_id"]) {
            header("Location: usuarios.php?error=automodificacion");
            exit;
        }

        $this->modelo->eliminar($id);

        header("Location: usuarios.php?exito=1");
        exit;
    }
}