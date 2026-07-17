<?php

require_once __DIR__ . '/../Models/AuthModel.php';
require_once __DIR__ . '/../Core/Sanitizer.php';
require_once __DIR__ . '/../Core/Validator.php';
require_once __DIR__ . '/../Core/Csrf.php';

class AuthController
{
    private AuthModel $modelo;

    public function __construct()
    {
        $this->modelo = new AuthModel();
    }

    public function login(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: login.php");
            exit;
        }

        $csrf = $_POST["csrf_token"] ?? "";

        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        $usuario = Sanitizer::limpiarTexto(
            $_POST["usuario"] ?? ""
        );

        $password = $_POST["password"] ?? "";

        $ip = $_SERVER["REMOTE_ADDR"]
            ?? "IP no detectada";

        if (
            !Validator::usuarioValido($usuario)
            || !Validator::passwordValida($password)
        ) {
            $this->modelo->registrarLog(
                null,
                $usuario,
                $ip,
                "fallido"
            );

            header("Location: login.php?error=datos");
            exit;
        }

        $datosUsuario = $this->modelo->buscarUsuario(
            $usuario
        );

        if (!$datosUsuario) {
            $this->modelo->registrarLog(
                null,
                $usuario,
                $ip,
                "fallido"
            );

            header(
                "Location: login.php?error=credenciales"
            );

            exit;
        }

        if ((int)$datosUsuario["bloqueado"] === 1) {
            $this->modelo->registrarLog(
                (int)$datosUsuario["id"],
                $usuario,
                $ip,
                "fallido"
            );

            header(
                "Location: login.php?error=bloqueado"
            );

            exit;
        }

        if (
            password_verify(
                $password,
                $datosUsuario["password_hash"]
            )
        ) {
            $this->modelo->reiniciarIntentos(
                (int)$datosUsuario["id"]
            );

            $this->modelo->registrarLog(
                (int)$datosUsuario["id"],
                $usuario,
                $ip,
                "exitoso"
            );

            /*
             * Se genera un nuevo identificador de sesión
             * después de iniciar sesión correctamente.
             */
            session_regenerate_id(true);

            $rol = (string)($datosUsuario["rol"] ?? "");

            $_SESSION["usuario_id"] =
                (int)$datosUsuario["id"];

            $_SESSION["usuario"] =
                $datosUsuario["usuario"];

            $_SESSION["rol"] = $rol;

            /*
             * El administrador entra al panel administrativo.
             */
            if ($rol === "admin") {
                header("Location: dashboard.php");
                exit;
            }

            /*
             * El estudiante entra directamente al catálogo.
             * El catálogo será su página principal.
             */
            if ($rol === "estudiante") {
                header("Location: catalogo.php");
                exit;
            }

            /*
             * Si el usuario tiene un rol desconocido,
             * se elimina la sesión.
             */
            session_unset();
            session_destroy();

            header("Location: login.php?error=datos");
            exit;
        }

        $this->modelo->sumarIntentoFallido(
            (int)$datosUsuario["id"],
            (int)$datosUsuario["intentos_fallidos"]
        );

        $this->modelo->registrarLog(
            (int)$datosUsuario["id"],
            $usuario,
            $ip,
            "fallido"
        );

        header(
            "Location: login.php?error=credenciales"
        );

        exit;
    }
}