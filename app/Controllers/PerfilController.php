<?php

require_once __DIR__ . '/../Models/UsuarioModel.php';
require_once __DIR__ . '/../Core/Validator.php';
require_once __DIR__ . '/../Core/Csrf.php';
require_once __DIR__ . '/../Core/PasswordHasher.php';

class PerfilController
{
    private UsuarioModel $modelo;
    private CryptoInterface $passwordHasher;

    public function __construct()
    {
        $this->modelo = new UsuarioModel();
        $this->passwordHasher = new PasswordHasher();
    }

    /**
     * Cualquier rol con sesión activa puede acceder a su propio perfil.
     */
    private function verificarSesion(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION["usuario_id"])) {
            header("Location: login.php");
            exit;
        }
    }

    public function cambiarPassword(): void
    {
        $this->verificarSesion();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: perfil.php");
            exit;
        }

        $csrf = $_POST["csrf_token"] ?? "";
        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        $id = (int) $_SESSION["usuario_id"];
        $passwordActual = (string) ($_POST["password_actual"] ?? "");
        $passwordNueva = (string) ($_POST["password_nueva"] ?? "");
        $passwordConfirmar = (string) ($_POST["password_confirmar"] ?? "");

        // 1. Verificar que la contraseña actual sea correcta
        $hashActual = $this->modelo->obtenerHashPassword($id);
        if ($hashActual === null || !$this->passwordHasher->verificar($passwordActual, $hashActual)) {
            header("Location: perfil.php?error=actual");
            exit;
        }

        // 2. Validar formato de la nueva contraseña
        if (!Validator::passwordValida($passwordNueva)) {
            header("Location: perfil.php?error=formato");
            exit;
        }

        // 3. Confirmar que coincidan
        if ($passwordNueva !== $passwordConfirmar) {
            header("Location: perfil.php?error=coincidencia");
            exit;
        }

        // 4. No permitir reutilizar la misma contraseña actual
        if ($this->passwordHasher->verificar($passwordNueva, $hashActual)) {
            header("Location: perfil.php?error=igual");
            exit;
        }

        // 5. Guardar la nueva contraseña (apaga cambio_password automáticamente)
        try {
            $hashNuevo = $this->passwordHasher->transformar($passwordNueva);
            $this->modelo->cambiarPasswordPropia($id, $hashNuevo);
        } catch (Throwable $e) {
            header("Location: perfil.php?error=guardar");
            exit;
        }

        /*
         * Se regenera el ID de sesión tras el cambio de contraseña
         * (buena práctica OWASP contra session fixation),
         * sin exigir que el usuario vuelva a iniciar sesión.
         */
        $_SESSION["cambio_password"] = 0;

        session_regenerate_id(true);

        $rol = $_SESSION["rol"] ?? "";
        $destino = $rol === "admin" ? "dashboard.php" : "catalogo.php";

        header("Location: " . $destino . "?password_actualizada=1");
        exit;
    }
}