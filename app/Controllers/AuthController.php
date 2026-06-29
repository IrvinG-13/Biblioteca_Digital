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
        session_start();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: login.php");
            exit;
        }

        $csrf = $_POST["csrf_token"] ?? "";

        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        $usuario = Sanitizer::limpiarTexto($_POST["usuario"] ?? "");
        $password = $_POST["password"] ?? "";
        $ip = $_SERVER["REMOTE_ADDR"] ?? "IP no detectada";

        if (!Validator::usuarioValido($usuario) || !Validator::passwordValida($password)) {
            $this->modelo->registrarLog(null, $usuario, $ip, "fallido");
            header("Location: login.php?error=datos");
            exit;
        }

        $datosUsuario = $this->modelo->buscarUsuario($usuario);

        if (!$datosUsuario) {
            $this->modelo->registrarLog(null, $usuario, $ip, "fallido");
            header("Location: login.php?error=credenciales");
            exit;
        }

        if ((int)$datosUsuario["bloqueado"] === 1) {
            $this->modelo->registrarLog((int)$datosUsuario["id"], $usuario, $ip, "fallido");
            header("Location: login.php?error=bloqueado");
            exit;
        }

        if (password_verify($password, $datosUsuario["password_hash"])) {
            $this->modelo->reiniciarIntentos((int)$datosUsuario["id"]);
            $this->modelo->registrarLog((int)$datosUsuario["id"], $usuario, $ip, "exitoso");

            $_SESSION["usuario_id"] = $datosUsuario["id"];
            $_SESSION["usuario"] = $datosUsuario["usuario"];
            $_SESSION["rol"] = $datosUsuario["rol"];

            header("Location: dashboard.php");
            exit;
        }

        $this->modelo->sumarIntentoFallido(
            (int)$datosUsuario["id"],
            (int)$datosUsuario["intentos_fallidos"]
        );

        $this->modelo->registrarLog((int)$datosUsuario["id"], $usuario, $ip, "fallido");

        header("Location: login.php?error=credenciales");
        exit;
    }
}