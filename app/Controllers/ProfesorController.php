<?php

require_once __DIR__ . '/../Models/ProfesorModel.php';
require_once __DIR__ . '/../Models/MateriaModel.php';
require_once __DIR__ . '/../Core/Sanitizer.php';
require_once __DIR__ . '/../Core/Validator.php';
require_once __DIR__ . '/../Core/Csrf.php';

class ProfesorController
{
    private ProfesorModel $modelo;
    private MateriaModel $modeloMateria;

    public function __construct()
    {
        $this->modelo = new ProfesorModel();
        $this->modeloMateria = new MateriaModel();
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

        $profesores = $this->modelo->listar($busqueda, $porPagina, $offset);
        $total = $this->modelo->contar($busqueda);
        $totalPaginas = (int) ceil($total / $porPagina);

        return [
            "profesores" => $profesores,
            "busqueda" => $busqueda,
            "paginaActual" => $pagina,
            "totalPaginas" => $totalPaginas,
        ];
    }

    /**
     * Datos que necesita el formulario: el profesor (si es edición),
     * la lista de materias, y los usuarios con rol 'profesor' disponibles.
     */
    public function datosFormulario(?int $id): array
    {
        $this->verificarSesionAdmin();

        $profesorActual = $id !== null ? $this->modelo->obtenerPorId($id) : null;
        $materias = $this->modeloMateria->listar("", 1000, 0);
        $usuariosDisponibles = $this->modelo->obtenerUsuariosDisponibles(
            $profesorActual["usuario_id"] ?? null
        );

        return [
            "profesor" => $profesorActual,
            "materias" => $materias,
            "usuariosDisponibles" => $usuariosDisponibles,
        ];
    }

    public function guardar(): void
    {
        $this->verificarSesionAdmin();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: profesores.php");
            exit;
        }

        $csrf = $_POST["csrf_token"] ?? "";
        if (!Csrf::validarToken($csrf)) {
            die("Token CSRF inválido.");
        }

        $id = !empty($_POST["id"]) ? (int) $_POST["id"] : null;
        $cedula = Sanitizer::limpiarTexto($_POST["cedula"] ?? "");
        $primerNombre = Sanitizer::limpiarTexto($_POST["primer_nombre"] ?? "");
        $segundoNombre = Sanitizer::limpiarTexto($_POST["segundo_nombre"] ?? "");
        $primerApellido = Sanitizer::limpiarTexto($_POST["primer_apellido"] ?? "");
        $segundoApellido = Sanitizer::limpiarTexto($_POST["segundo_apellido"] ?? "");
        $materiaId = (int) ($_POST["materia_id"] ?? 0);
        $usuarioId = !empty($_POST["usuario_id"]) ? (int) $_POST["usuario_id"] : null;

        $segundoNombre = $segundoNombre !== "" ? $segundoNombre : null;
        $segundoApellido = $segundoApellido !== "" ? $segundoApellido : null;

        if (!Validator::cipValido($cedula)) {
            header("Location: profesor_form.php?error=cedula&id=" . $id);
            exit;
        }

        if ($this->modelo->existeCedula($cedula, $id)) {
            header("Location: profesor_form.php?error=ceduladuplicada&id=" . $id);
            exit;
        }

        if ($primerNombre === "" || $primerApellido === "") {
            header("Location: profesor_form.php?error=nombres&id=" . $id);
            exit;
        }

        if ($this->modeloMateria->obtenerPorId($materiaId) === null) {
            header("Location: profesor_form.php?error=materia&id=" . $id);
            exit;
        }

        if ($id === null) {
            $this->modelo->crear(
                $cedula, $primerNombre, $segundoNombre, $primerApellido,
                $segundoApellido, $materiaId, $usuarioId
            );
        } else {
            $this->modelo->actualizar(
                $id, $cedula, $primerNombre, $segundoNombre, $primerApellido,
                $segundoApellido, $materiaId, $usuarioId
            );
        }

        header("Location: profesores.php?exito=1");
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
        $this->modelo->eliminar($id);

        header("Location: profesores.php?exito=1");
        exit;
    }
}