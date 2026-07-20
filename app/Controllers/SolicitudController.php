<?php
require_once __DIR__ . '/../Models/SolicitudModel.php';
require_once __DIR__ . '/../Models/EstudianteModel.php';
require_once __DIR__ . '/../Models/ProfesorModel.php';
require_once __DIR__ . '/../Core/Sanitizer.php';
require_once __DIR__ . '/../Core/Csrf.php';
class SolicitudController
{
    private SolicitudModel $solicitudModelo;
    private EstudianteModel $estudianteModelo;
    private ProfesorModel $profesorModelo;
    public function __construct()
    {
        $this->solicitudModelo = new SolicitudModel();
        $this->estudianteModelo = new EstudianteModel();
        $this->profesorModelo = new ProfesorModel();
    }
    private function iniciarSesion(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    /**
     * Permite el acceso tanto a estudiantes como a profesores,
     * ya que ambos pueden solicitar libros.
     */
    private function verificarSesionSolicitante(): void
    {
        $this->iniciarSesion();
        $rolActual = $_SESSION['rol'] ?? '';
        if (
            !isset($_SESSION['usuario_id'])
            || !in_array($rolActual, ['estudiante', 'profesor'], true)
        ) {
            header('Location: login.php');
            exit;
        }
    }
    private function verificarSesionAdmin(): void
    {
        $this->iniciarSesion();
        if (
            !isset($_SESSION['usuario_id'])
            || ($_SESSION['rol'] ?? '') !== 'admin'
        ) {
            header('Location: login.php');
            exit;
        }
        require_once __DIR__ . '/../Core/SesionGuard.php';
        SesionGuard::bloquearSiCambioPasswordPendiente();
    }
    /**
     * Obtiene el perfil académico del solicitante en sesión
     * (estudiante o profesor) junto con su tipo.
     *
     * Retorna: ['tipo' => 'estudiante'|'profesor', 'datos' => array]
     */
    private function obtenerSolicitanteSesion(): array
    {
        $this->verificarSesionSolicitante();
        require_once __DIR__ . '/../Core/SesionGuard.php';
        SesionGuard::bloquearSiCambioPasswordPendiente();
        $usuarioId = (int)$_SESSION['usuario_id'];
        $rolActual = $_SESSION['rol'] ?? '';
        if ($rolActual === 'profesor') {
            $profesor = $this->profesorModelo->obtenerPorUsuarioId($usuarioId);
            if ($profesor === null) {
                header('Location: catalogo.php?error=perfil_estudiante');
                exit;
            }
            return ['tipo' => 'profesor', 'datos' => $profesor];
        }
        $estudiante = $this->estudianteModelo->obtenerPorUsuarioId($usuarioId);
        if ($estudiante === null) {
            header('Location: catalogo.php?error=perfil_estudiante');
            exit;
        }
        return ['tipo' => 'estudiante', 'datos' => $estudiante];
    }

    public function obtenerCategorias(): array
    {
        return $this->solicitudModelo->obtenerCategorias();
    }
    /**
     * Alias para conservar compatibilidad.
     */
    public function obtenerAreas(): array
    {
        return $this->obtenerCategorias();
    }
    public function datosFormulario(): array
    {
        $solicitante = $this->obtenerSolicitanteSesion();
        return [
            'tipo' => $solicitante['tipo'],
            'solicitante' => $solicitante['datos'],
            'categorias' => $this->solicitudModelo->obtenerCategorias()
        ];
    }
    public function guardar(): void
    {
        $solicitante = $this->obtenerSolicitanteSesion();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: solicitar_libro.php');
            exit;
        }
        $csrf = $_POST['csrf_token'] ?? '';
        if (!Csrf::validarToken($csrf)) {
            die('Token CSRF inválido.');
        }
        $titulo = Sanitizer::limpiarTexto(
            $_POST['titulo_solicitado'] ?? ''
        );
        $categoria = Sanitizer::limpiarTexto(
            $_POST['categoria']
            ?? $_POST['area']
            ?? ''
        );
        $comentario = Sanitizer::limpiarTexto(
            $_POST['comentario'] ?? ''
        );
        $longitudTitulo = strlen($titulo);
        if ($longitudTitulo < 3 || $longitudTitulo > 200) {
            header('Location: solicitar_libro.php?error=titulo');
            exit;
        }
        $categoriasPermitidas = $this->solicitudModelo->obtenerCategorias();
        if (!in_array($categoria, $categoriasPermitidas, true)) {
            header('Location: solicitar_libro.php?error=categoria');
            exit;
        }
        if (strlen($comentario) > 1000) {
            header('Location: solicitar_libro.php?error=comentario');
            exit;
        }
        $comentarioFinal = trim($comentario) === '' ? null : $comentario;
        $tipoSolicitante = $solicitante['tipo'];
        $solicitanteId = (int)$solicitante['datos']['id'];
        if (
            $this->solicitudModelo->existeSolicitudPendiente(
                $tipoSolicitante,
                $solicitanteId,
                $titulo
            )
        ) {
            header('Location: solicitar_libro.php?error=duplicada');
            exit;
        }
        try {
            $this->solicitudModelo->crear(
                $tipoSolicitante,
                $solicitanteId,
                $titulo,
                $categoria,
                $comentarioFinal
            );
            header('Location: mis_solicitudes.php?exito=1');
            exit;
        } catch (Throwable $e) {
            header('Location: solicitar_libro.php?error=guardar');
            exit;
        }
    }
    public function listarMisSolicitudes(): array
    {
        $solicitante = $this->obtenerSolicitanteSesion();
        $solicitudes = $this->solicitudModelo->obtenerPorSolicitante(
            $solicitante['tipo'],
            (int)$solicitante['datos']['id']
        );
        return [
            'tipo' => $solicitante['tipo'],
            'solicitante' => $solicitante['datos'],
            'solicitudes' => $solicitudes
        ];
    }
    /**
     * Por defecto abre las solicitudes pendientes.
     */
    public function listarAdmin(): array
    {
        $this->verificarSesionAdmin();
        $categoria = Sanitizer::limpiarTexto(
            $_GET['categoria']
            ?? $_GET['area']
            ?? ''
        );
        $estado = Sanitizer::limpiarTexto(
            $_GET['estado'] ?? 'pendiente'
        );
        $categorias = $this->solicitudModelo->obtenerCategorias();
        $estadosPermitidos = [
            'pendiente', 'respondidas', 'aprobada', 'rechazada', 'todas'
        ];
        if ($categoria !== '' && !in_array($categoria, $categorias, true)) {
            $categoria = '';
        }
        if (!in_array($estado, $estadosPermitidos, true)) {
            $estado = 'pendiente';
        }
        $total = $this->solicitudModelo->contarTotal($categoria, $estado);
        $porPagina = 10;
        $totalPaginas = max(1, (int)ceil($total / $porPagina));
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        if ($pagina > $totalPaginas) {
            $pagina = $totalPaginas;
        }
        $offset = ($pagina - 1) * $porPagina;
        $solicitudes = $this->solicitudModelo->obtenerTodas(
            $categoria, $estado, $porPagina, $offset
        );
        return [
            'solicitudes' => $solicitudes,
            'categorias' => $categorias,
            'estados' => $estadosPermitidos,
            'categoriaActual' => $categoria,
            'estadoActual' => $estado,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas,
            'totalSolicitudes' => $total
        ];
    }
    public function obtenerPorId(int $id): ?array
    {
        $this->verificarSesionAdmin();
        if ($id <= 0) {
            return null;
        }
        return $this->solicitudModelo->obtenerPorId($id);
    }
    public function cambiarEstado(): void
    {
        $this->verificarSesionAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: solicitudes_admin.php');
            exit;
        }
        $csrf = $_POST['csrf_token'] ?? '';
        if (!Csrf::validarToken($csrf)) {
            die('Token CSRF inválido.');
        }
        $id = (int)($_POST['id'] ?? 0);
        $estado = Sanitizer::limpiarTexto($_POST['estado'] ?? '');
        $observacion = Sanitizer::limpiarTexto($_POST['observacion_admin'] ?? '');
        $vistaActual = Sanitizer::limpiarTexto($_POST['vista_actual'] ?? 'pendiente');
        $categoriaActual = Sanitizer::limpiarTexto(
            $_POST['categoria_actual'] ?? $_POST['area_actual'] ?? ''
        );
        $estadosPermitidos = ['pendiente', 'aprobada', 'rechazada'];
        $vistasPermitidas = ['pendiente', 'respondidas', 'aprobada', 'rechazada', 'todas'];
        if (!in_array($vistaActual, $vistasPermitidas, true)) {
            $vistaActual = 'pendiente';
        }
        $categorias = $this->solicitudModelo->obtenerCategorias();
        if ($categoriaActual !== '' && !in_array($categoriaActual, $categorias, true)) {
            $categoriaActual = '';
        }
        $parametrosRegreso = ['estado' => $vistaActual];
        if ($categoriaActual !== '') {
            $parametrosRegreso['categoria'] = $categoriaActual;
        }
        if ($id <= 0) {
            $parametrosRegreso['error'] = 'no_encontrada';
            header('Location: solicitudes_admin.php?' . http_build_query($parametrosRegreso));
            exit;
        }
        if (!in_array($estado, $estadosPermitidos, true)) {
            $parametrosRegreso['error'] = 'estado';
            header('Location: solicitudes_admin.php?' . http_build_query($parametrosRegreso));
            exit;
        }
        $solicitud = $this->solicitudModelo->obtenerPorId($id);
        if ($solicitud === null) {
            $parametrosRegreso['error'] = 'no_encontrada';
            header('Location: solicitudes_admin.php?' . http_build_query($parametrosRegreso));
            exit;
        }
        if (strlen($observacion) > 1000) {
            $parametrosRegreso['error'] = 'observacion';
            header('Location: solicitudes_admin.php?' . http_build_query($parametrosRegreso));
            exit;
        }
        $observacionFinal = trim($observacion) === '' ? null : $observacion;
        $usuarioGestorId = (int)$_SESSION['usuario_id'];
        try {
            $actualizada = $this->solicitudModelo->cambiarEstado(
                $id, $estado, $usuarioGestorId, $observacionFinal
            );
            $parametrosRegreso[$actualizada ? 'exito' : 'error'] = $actualizada ? '1' : 'estado';
            header('Location: solicitudes_admin.php?' . http_build_query($parametrosRegreso));
            exit;
        } catch (Throwable $e) {
            $parametrosRegreso['error'] = 'guardar';
            header('Location: solicitudes_admin.php?' . http_build_query($parametrosRegreso));
            exit;
        }
    }
}