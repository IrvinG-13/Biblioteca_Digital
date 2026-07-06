<?php

require_once __DIR__ . '/../Models/EstudianteModel.php';
require_once __DIR__ . '/../Models/CarreraModel.php';
require_once __DIR__ . '/../Core/Sanitizer.php';
require_once __DIR__ . '/../Core/Validator.php';
require_once __DIR__ . '/../Core/Csrf.php';

class EstudianteController
{
    private EstudianteModel $modelo;
    private CarreraModel $modeloCarrera;

    public function __construct()
    {
        $this->modelo = new EstudianteModel();
        $this->modeloCarrera = new CarreraModel();
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

        $estudiantes = $this->modelo->listar($busqueda, $porPagina, $offset);
        $total = $this->modelo->contar($busqueda);
        $totalPaginas = (int) ceil($total / $porPagina);

        return [
            "estudiantes" => $estudiantes,
            "busqueda" => $busqueda,
            "paginaActual" => $pagina,
            "totalPaginas" => $totalPaginas,
        ];
    }

    /**
     * Datos que necesita el formulario: el estudiante (si es edición),
     * la lista de carreras, y los usuarios con rol 'estudiante' disponibles.
     */
    public function datosFormulario(?int $id): array
    {
        $this->verificarSesionAdmin();

        $estudianteActual = $id !== null ? $this->modelo->obtenerPorId($id) : null;
        $carreras = $this->modeloCarrera->listar("", 1000, 0);
        $usuariosDisponibles = $this->modelo->obtenerUsuariosDisponibles(
            $estudianteActual["usuario_id"] ?? null
        );

        return [
            "estudiante" => $estudianteActual,
            "carreras" => $carreras,
            "usuariosDisponibles" => $usuariosDisponibles,
        ];
    }

    public function guardar(): void
    {
        $this->verificarSesionAdmin();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: estudiantes.php");
            exit;
        }

        $csrf = $_POST["csrf_token"] ?? "";
        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        $id = !empty($_POST["id"]) ? (int) $_POST["id"] : null;

        $cip = Sanitizer::limpiarTexto($_POST["cip"] ?? "");
        $primerNombre = Sanitizer::limpiarTexto($_POST["primer_nombre"] ?? "");
        $segundoNombre = Sanitizer::limpiarTexto($_POST["segundo_nombre"] ?? "");
        $primerApellido = Sanitizer::limpiarTexto($_POST["primer_apellido"] ?? "");
        $segundoApellido = Sanitizer::limpiarTexto($_POST["segundo_apellido"] ?? "");
        $fechaNacimiento = Sanitizer::limpiarTexto($_POST["fecha_nacimiento"] ?? "");
        $carreraId = (int) ($_POST["carrera_id"] ?? 0);
        $usuarioId = !empty($_POST["usuario_id"]) ? (int) $_POST["usuario_id"] : null;

        // Los campos "segundo" son opcionales: si vienen vacíos, se guardan como NULL.
        $segundoNombre = $segundoNombre !== "" ? $segundoNombre : null;
        $segundoApellido = $segundoApellido !== "" ? $segundoApellido : null;

        if (!Validator::cipValido($cip)) {
            header("Location: estudiante_form.php?error=cip&id=" . $id);
            exit;
        }

        if ($this->modelo->existeCip($cip, $id)) {
            header("Location: estudiante_form.php?error=cipduplicado&id=" . $id);
            exit;
        }

        if ($primerNombre === "" || $primerApellido === "") {
            header("Location: estudiante_form.php?error=nombres&id=" . $id);
            exit;
        }

        if (!Validator::fechaNacimientoValida($fechaNacimiento)) {
            header("Location: estudiante_form.php?error=fecha&id=" . $id);
            exit;
        }

        if ($this->modeloCarrera->obtenerPorId($carreraId) === null) {
            header("Location: estudiante_form.php?error=carrera&id=" . $id);
            exit;
        }

        if ($id === null) {
            $this->modelo->crear(
                $cip, $primerNombre, $segundoNombre, $primerApellido,
                $segundoApellido, $fechaNacimiento, $carreraId, $usuarioId
            );
        } else {
            $this->modelo->actualizar(
                $id, $cip, $primerNombre, $segundoNombre, $primerApellido,
                $segundoApellido, $fechaNacimiento, $carreraId, $usuarioId
            );
        }

        header("Location: estudiantes.php?exito=1");
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

        if ($this->modelo->contarReservasAsociadas($id) > 0) {
            header("Location: estudiantes.php?error=tienereservas");
            exit;
        }

        $this->modelo->eliminar($id);

        header("Location: estudiantes.php?exito=1");
        exit;
    }
}