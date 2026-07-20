<?php

require_once __DIR__ . '/../Models/AuthModel.php';
require_once __DIR__ . '/../Core/Sanitizer.php';
require_once __DIR__ . '/../Core/Validator.php';
require_once __DIR__ . '/../Core/Csrf.php';
require_once __DIR__ . '/../Core/PasswordHasher.php';

class AuthController
{
    private AuthModel $modelo;
    private CryptoInterface $passwordHasher;

    public function __construct()
    {
        $this->modelo = new AuthModel();

        /*
         * PasswordHasher implementa CryptoInterface.
         * De esta manera, el controlador depende del contrato
         * y no directamente de password_verify().
         */
        $this->passwordHasher = new PasswordHasher();
    }

    public function login(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Iniciar sesión
        |--------------------------------------------------------------------------
        */
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        /*
        |--------------------------------------------------------------------------
        | Validar método HTTP
        |--------------------------------------------------------------------------
        */
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: login.php");
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Validar token CSRF
        |--------------------------------------------------------------------------
        */
        $csrf = $_POST["csrf_token"] ?? "";

        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        /*
        |--------------------------------------------------------------------------
        | Obtener y limpiar datos
        |--------------------------------------------------------------------------
        */
        $usuario = Sanitizer::limpiarTexto(
            $_POST["usuario"] ?? ""
        );

        $password = (string)(
            $_POST["password"] ?? ""
        );

        $ip = $_SERVER["REMOTE_ADDR"]
            ?? "IP no detectada";

        /*
        |--------------------------------------------------------------------------
        | Validar formato de los datos
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | Buscar usuario
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | Comprobar si está bloqueado
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | Verificar contraseña mediante CryptoInterface
        |--------------------------------------------------------------------------
        */
        $passwordCorrecta =
            $this->passwordHasher->verificar(
                $password,
                (string)$datosUsuario["password_hash"]
            );

        if ($passwordCorrecta) {
            /*
            |--------------------------------------------------------------------------
            | Reiniciar intentos y registrar acceso exitoso
            |--------------------------------------------------------------------------
            */
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
             * Generar un nuevo identificador después
             * de iniciar sesión correctamente.
             */
            session_regenerate_id(true);

            $rol = (string)(
                $datosUsuario["rol"] ?? ""
            );

            $_SESSION["usuario_id"] =
                (int)$datosUsuario["id"];

            $_SESSION["usuario"] =
                (string)$datosUsuario["usuario"];

            $_SESSION["rol"] = $rol;

            $_SESSION["cambio_password"] =
                (int)($datosUsuario["cambio_password"] ?? 0);

            /*
            |--------------------------------------------------------------------------
            | Forzar cambio de contraseña si corresponde
            |--------------------------------------------------------------------------
            | Si la contraseña fue definida por el administrador
            | (al crear o resetear la cuenta), el usuario debe
            | cambiarla antes de acceder al resto del sistema.
            |
            */
            if ((int)($datosUsuario["cambio_password"] ?? 0) === 1) {
                header("Location: perfil.php?forzado=1");
                exit;
            }

            /*
            |--------------------------------------------------------------------------
            | Redirección según el rol
            |--------------------------------------------------------------------------
            */
            /*
             * El administrador entra al panel administrativo.
             */
            if ($rol === "admin") {
                header("Location: dashboard.php");
                exit;
            }

            /*
             * Estudiantes y profesores entran al catálogo.
             */
            if (
                $rol === "estudiante"
                || $rol === "profesor"
            ) {
                header("Location: catalogo.php");
                exit;
            }

            /*
             * Si el rol es desconocido, se elimina la sesión.
             */
            session_unset();
            session_destroy();

            header("Location: login.php?error=datos");
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Contraseña incorrecta
        |--------------------------------------------------------------------------
        */
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