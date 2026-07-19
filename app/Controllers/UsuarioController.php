<?php

require_once __DIR__ . '/../Models/UsuarioModel.php';
require_once __DIR__ . '/../Core/Sanitizer.php';
require_once __DIR__ . '/../Core/Validator.php';
require_once __DIR__ . '/../Core/Csrf.php';
require_once __DIR__ . '/../Core/PasswordHasher.php';

class UsuarioController
{
    private UsuarioModel $modelo;
    private CryptoInterface $passwordHasher;

    public function __construct()
    {
        $this->modelo = new UsuarioModel();

        /*
         * PasswordHasher implementa CryptoInterface.
         * El controlador depende del contrato criptográfico.
         */
        $this->passwordHasher = new PasswordHasher();
    }

    /**
     * Verifica que haya sesión activa y que el rol sea admin.
     * Esta pantalla es exclusiva del administrador.
     */
    private function verificarSesionAdmin(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (
            !isset($_SESSION["usuario_id"])
            || ($_SESSION["rol"] ?? "") !== "admin"
        ) {
            header("Location: login.php");
            exit;
        }
    }

    /**
     * Lista usuarios con buscador y paginación.
     * Es llamado desde usuarios.php mediante GET.
     */
    public function listar(): array
    {
        $this->verificarSesionAdmin();

        $busqueda = Sanitizer::limpiarTexto(
            $_GET["busqueda"] ?? ""
        );

        $pagina = max(
            1,
            (int)($_GET["pagina"] ?? 1)
        );

        $porPagina = 10;

        $offset = ($pagina - 1) * $porPagina;

        $usuarios = $this->modelo->listar(
            $busqueda,
            $porPagina,
            $offset
        );

        $total = $this->modelo->contar(
            $busqueda
        );

        $totalPaginas = (int)ceil(
            $total / $porPagina
        );

        return [
            "usuarios" => $usuarios,
            "busqueda" => $busqueda,
            "paginaActual" => $pagina,
            "totalPaginas" => $totalPaginas
        ];
    }

    /**
     * Procesa la creación o edición de un usuario.
     * Es llamado desde usuario_procesar.php mediante POST.
     */
    public function guardar(): void
    {
        $this->verificarSesionAdmin();

        /*
        |--------------------------------------------------------------------------
        | Validar método HTTP
        |--------------------------------------------------------------------------
        */
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: usuarios.php");
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
        | Obtener los datos
        |--------------------------------------------------------------------------
        */
        $id = !empty($_POST["id"])
            ? (int)$_POST["id"]
            : null;

        $usuario = Sanitizer::limpiarTexto(
            $_POST["usuario"] ?? ""
        );

        $password = (string)(
            $_POST["password"] ?? ""
        );

        $rol = Sanitizer::limpiarTexto(
            $_POST["rol"] ?? ""
        );

        /*
        |--------------------------------------------------------------------------
        | Validar nombre de usuario
        |--------------------------------------------------------------------------
        */
        if (!Validator::usuarioValido($usuario)) {
            $this->redirigirFormulario(
                "usuario",
                $id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validar rol
        |--------------------------------------------------------------------------
        */
        $rolesPermitidos = [
            "admin",
            "estudiante",
            "profesor"
        ];

        if (
            !in_array(
                $rol,
                $rolesPermitidos,
                true
            )
        ) {
            $this->redirigirFormulario(
                "rol",
                $id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validar contraseña
        |--------------------------------------------------------------------------
        |
        | Al crear un usuario, la contraseña es obligatoria.
        | Al editar, puede dejarse vacía para conservar la actual.
        |
        */
        if (
            $id === null
            && !Validator::passwordValida($password)
        ) {
            $this->redirigirFormulario(
                "password",
                null
            );
        }

        if (
            $password !== ""
            && !Validator::passwordValida($password)
        ) {
            $this->redirigirFormulario(
                "password",
                $id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Evitar usuarios duplicados
        |--------------------------------------------------------------------------
        */
        if (
            $this->modelo->existeUsuario(
                $usuario,
                $id
            )
        ) {
            $this->redirigirFormulario(
                "duplicado",
                $id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Crear o actualizar usuario
        |--------------------------------------------------------------------------
        */
        try {
            if ($id === null) {
                /*
                 * La contraseña se transforma mediante
                 * CryptoInterface y PasswordHasher.
                 */
                $hash = $this->passwordHasher->transformar(
                    $password
                );

                $this->modelo->crear(
                    $usuario,
                    $hash,
                    $rol
                );
            } else {
                /*
                 * Al editar, solamente se genera un hash
                 * cuando se escribió una contraseña nueva.
                 */
                $hash = null;

                if ($password !== "") {
                    $hash =
                        $this->passwordHasher->transformar(
                            $password
                        );
                }

                $this->modelo->actualizar(
                    $id,
                    $usuario,
                    $rol,
                    $hash
                );
            }
        } catch (
            InvalidArgumentException
            | RuntimeException $e
        ) {
            $this->redirigirFormulario(
                "password",
                $id
            );
        } catch (Throwable $e) {
            $this->redirigirFormulario(
                "guardar",
                $id
            );
        }

        header("Location: usuarios.php?exito=1");
        exit;
    }

    /**
     * Bloquea o reactiva un usuario.
     * Es una baja lógica reversible.
     */
    public function cambiarEstado(): void
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

        $id = (int)(
            $_POST["id"] ?? 0
        );

        $bloqueado = (int)(
            $_POST["bloqueado"] ?? 0
        );

        if ($id <= 0) {
            header("Location: usuarios.php?error=datos");
            exit;
        }

        /*
         * El administrador no puede bloquearse a sí mismo.
         */
        if (
            $id ===
            (int)$_SESSION["usuario_id"]
        ) {
            header(
                "Location: usuarios.php"
                . "?error=automodificacion"
            );

            exit;
        }

        /*
         * Solo se permiten los valores 0 y 1.
         */
        $bloqueado = $bloqueado === 1
            ? 1
            : 0;

        $this->modelo->cambiarEstado(
            $id,
            $bloqueado
        );

        header("Location: usuarios.php?exito=1");
        exit;
    }

    /**
     * Elimina físicamente un usuario.
     * Esta acción es irreversible.
     */
    public function eliminar(): void
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

        $id = (int)(
            $_POST["id"] ?? 0
        );

        if ($id <= 0) {
            header("Location: usuarios.php?error=datos");
            exit;
        }

        /*
         * El administrador no puede eliminar
         * su propia cuenta.
         */
        if (
            $id ===
            (int)$_SESSION["usuario_id"]
        ) {
            header(
                "Location: usuarios.php"
                . "?error=automodificacion"
            );

            exit;
        }

        $this->modelo->eliminar($id);

        header("Location: usuarios.php?exito=1");
        exit;
    }

    /**
     * Redirige al formulario con un código de error.
     */
    private function redirigirFormulario(
        string $error,
        ?int $id
    ): never {
        $url = "usuario_form.php?error="
            . urlencode($error);

        if ($id !== null && $id > 0) {
            $url .= "&id=" . $id;
        }

        header("Location: " . $url);
        exit;
    }
}