<?php

require_once __DIR__ . '/../Models/LibroModel.php';
require_once __DIR__ . '/../Models/CategoriaModel.php';
require_once __DIR__ . '/../Core/Sanitizer.php';
require_once __DIR__ . '/../Core/Csrf.php';
require_once __DIR__ . '/../Core/ImagenLibro.php';
require_once __DIR__ . '/../Core/PdfLibro.php';
require_once __DIR__ . '/../Core/ExcelLibro.php';

class LibroController
{
    private LibroModel $modelo;
    private CategoriaModel $categoriaModelo;

    public function __construct()
    {
        $this->modelo = new LibroModel();
        $this->categoriaModelo = new CategoriaModel();
    }

    /**
     * Permite el acceso únicamente a administradores.
     */
    private function verificarSesionAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (
            !isset($_SESSION['usuario_id']) ||
            ($_SESSION['rol'] ?? '') !== 'admin'
        ) {
            header('Location: login.php');
            exit;
        }
        require_once __DIR__ . '/../Core/SesionGuard.php';
        SesionGuard::bloquearSiCambioPasswordPendiente();
    }

    /**
     * Regresa al formulario con un código de error.
     */
    private function redirigirFormulario(
        string $error,
        ?int $id = null
    ): void {
        $url = 'libro_form.php?error=' . urlencode($error);

        if ($id !== null && $id > 0) {
            $url .= '&id=' . $id;
        }

        header('Location: ' . $url);
        exit;
    }

    /**
     * Convierte cadenas vacías en NULL.
     */
    private function valorNullable(?string $valor): ?string
    {
        $valor = trim((string)$valor);

        return $valor === '' ? null : $valor;
    }

    /**
     * Verifica que una URL utilice HTTP o HTTPS.
     */
    private function urlValida(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $esquema = strtolower(
            (string)parse_url($url, PHP_URL_SCHEME)
        );

        return in_array(
            $esquema,
            ['http', 'https'],
            true
        );
    }

    /**
     * Lista los libros del panel administrativo.
     */
    public function listar(): array
    {
        $this->verificarSesionAdmin();

        $busqueda = Sanitizer::limpiarTexto(
            $_GET['busqueda'] ?? ''
        );

        $pagina = max(
            1,
            (int)($_GET['pagina'] ?? 1)
        );

        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $libros = $this->modelo->listar(
            $busqueda,
            $porPagina,
            $offset
        );

        $total = $this->modelo->contar($busqueda);

        $totalPaginas = max(
            1,
            (int)ceil($total / $porPagina)
        );

        return [
            'libros' => $libros,
            'busqueda' => $busqueda,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas
        ];
    }

    /**
     * Busca un libro por su identificador.
     */
    public function obtenerPorId(int $id): ?array
    {
        $this->verificarSesionAdmin();

        return $this->modelo->obtenerPorId($id);
    }

    /**
     * Obtiene las categorías disponibles.
     */
    public function obtenerCategorias(): array
    {
        $this->verificarSesionAdmin();

        return $this->categoriaModelo->obtenerTodas();
    }

    private function tituloValido(string $titulo): bool
    {
        $longitud = strlen($titulo);

        return $longitud >= 3 && $longitud <= 200;
    }

    private function autorValido(string $autor): bool
    {
        $longitud = strlen($autor);

        return $longitud >= 2 && $longitud <= 150;
    }

    /**
     * Crea o actualiza un libro.
     */
    public function guardar(): void
    {
        $this->verificarSesionAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: libros.php');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';

        if (!Csrf::validarToken($csrf)) {
            die('Token CSRF inválido.');
        }

        $id = !empty($_POST['id'])
            ? (int)$_POST['id']
            : null;

        /*
         * En una edición se obtienen los archivos actuales
         * directamente desde la base de datos.
         */
        $libroActual = null;

        if ($id !== null) {
            $libroActual = $this->modelo->obtenerPorId($id);

            if ($libroActual === null) {
                header('Location: libros.php?error=no_encontrado');
                exit;
            }
        }

        /*
         * Datos generales.
         */
        $titulo = Sanitizer::limpiarTexto(
            $_POST['titulo'] ?? ''
        );

        $autor = Sanitizer::limpiarTexto(
            $_POST['autor'] ?? ''
        );

        $descripcion = Sanitizer::limpiarTexto(
            $_POST['descripcion'] ?? ''
        );

        $categoriaId = (int)(
            $_POST['categoria_id'] ?? 0
        );

        $origen = trim(
            $_POST['origen'] ?? 'propio'
        );

        $unidades = (int)(
            $_POST['unidades_totales'] ?? 0
        );

        /*
         * Costo de adquisición del libro.
         */
        $costoTexto = str_replace(
            ',',
            '.',
            trim($_POST['costo'] ?? '0')
        );

        /*
         * Configuración de acceso al PDF.
         */
        $tipoAcceso = trim(
            $_POST['tipo_acceso'] ?? 'gratuito'
        );

        $precioAccesoTexto = str_replace(
            ',',
            '.',
            trim($_POST['precio_acceso'] ?? '0')
        );

        $diasAccesoTexto = trim(
            $_POST['dias_acceso'] ?? ''
        );

        /*
         * Datos utilizados únicamente por libros externos.
         */
        $institucionOrigen = $this->valorNullable(
            Sanitizer::limpiarTexto(
                $_POST['institucion_origen'] ?? ''
            )
        );

        $urlExterno = $this->valorNullable(
            $_POST['url_externo'] ?? null
        );

        /*
         * Validación del título.
         */
        if (!$this->tituloValido($titulo)) {
            $this->redirigirFormulario(
                'titulo',
                $id
            );
        }

        /*
         * Validación del autor.
         */
        if (!$this->autorValido($autor)) {
            $this->redirigirFormulario(
                'autor',
                $id
            );
        }

        /*
         * Validación de categoría.
         */
        if ($categoriaId <= 0) {
            $this->redirigirFormulario(
                'categoria',
                $id
            );
        }

        /*
         * Validación del costo de adquisición.
         */
        if (
            $costoTexto === '' ||
            !is_numeric($costoTexto) ||
            (float)$costoTexto < 0
        ) {
            $this->redirigirFormulario(
                'costo',
                $id
            );
        }

        $costo = number_format(
            (float)$costoTexto,
            2,
            '.',
            ''
        );

        /*
         * Validación del origen.
         */
        if (
            !in_array(
                $origen,
                ['propio', 'externo'],
                true
            )
        ) {
            $this->redirigirFormulario(
                'origen',
                $id
            );
        }

        /*
         * Libro propio.
         */
        if ($origen === 'propio') {
            if ($unidades < 0) {
                $this->redirigirFormulario(
                    'unidades',
                    $id
                );
            }

            $institucionOrigen = null;
            $urlExterno = null;

            /*
             * Validación del tipo de acceso.
             */
            if (
                !in_array(
                    $tipoAcceso,
                    ['gratuito', 'pago'],
                    true
                )
            ) {
                $this->redirigirFormulario(
                    'tipo_acceso',
                    $id
                );
            }

            /*
             * Los libros gratuitos tienen acceso permanente.
             */
            if ($tipoAcceso === 'gratuito') {
                $precioAcceso = '0.00';
                $diasAcceso = null;
            } else {
                /*
                 * Libro pagado: precio fijo y acceso temporal.
                 */
                if (
                    $precioAccesoTexto === '' ||
                    !is_numeric($precioAccesoTexto) ||
                    (float)$precioAccesoTexto <= 0
                ) {
                    $this->redirigirFormulario(
                        'precio_acceso',
                        $id
                    );
                }

                $precioAcceso = number_format(
                    (float)$precioAccesoTexto,
                    2,
                    '.',
                    ''
                );

                if (
                    $diasAccesoTexto === '' ||
                    !ctype_digit($diasAccesoTexto) ||
                    (int)$diasAccesoTexto <= 0
                ) {
                    $this->redirigirFormulario(
                        'dias_acceso',
                        $id
                    );
                }

                $diasAcceso = (int)$diasAccesoTexto;
            }
        } else {
            /*
             * Libro externo:
             * nuestro sistema no controla unidades,
             * precio, factura ni período de acceso.
             */
            $unidades = 0;

            $tipoAcceso = 'gratuito';
            $precioAcceso = '0.00';
            $diasAcceso = null;

            if (
                $institucionOrigen === null ||
                strlen($institucionOrigen) < 2 ||
                strlen($institucionOrigen) > 150
            ) {
                $this->redirigirFormulario(
                    'institucion',
                    $id
                );
            }

            if (
                $urlExterno === null ||
                !$this->urlValida($urlExterno)
            ) {
                $this->redirigirFormulario(
                    'url',
                    $id
                );
            }
        }

        /*
         * Archivos actualmente registrados.
         */
        $imagen = $libroActual['imagen'] ?? null;
        $thumbnail = $libroActual['thumbnail'] ?? null;
        $archivoPdf = $libroActual['archivo_pdf'] ?? null;

        /*
         * Archivos anteriores para eliminarlos únicamente
         * después de guardar correctamente en la BD.
         */
        $imagenAnterior = $imagen;
        $thumbnailAnterior = $thumbnail;
        $pdfAnterior = $archivoPdf;

        /*
         * Archivos subidos durante este intento.
         */
        $imagenNueva = null;
        $thumbnailNuevo = null;
        $pdfNuevo = null;

        /*
         * Procesar una portada nueva.
         */
        $hayImagenNueva =
            isset($_FILES['imagen']) &&
            (
                $_FILES['imagen']['error']
                ?? UPLOAD_ERR_NO_FILE
            ) !== UPLOAD_ERR_NO_FILE;

        if ($hayImagenNueva) {
            $resultadoImagen = ImagenLibro::subir(
                $_FILES['imagen']
            );

            if (isset($resultadoImagen['error'])) {
                $this->redirigirFormulario(
                    'imagen',
                    $id
                );
            }

            $imagenNueva = $resultadoImagen['imagen'];
            $thumbnailNuevo =
                $resultadoImagen['thumbnail'];

            $imagen = $imagenNueva;
            $thumbnail = $thumbnailNuevo;
        }

        /*
         * Procesar un PDF nuevo.
         */
        $hayPdfNuevo =
            isset($_FILES['archivo_pdf']) &&
            (
                $_FILES['archivo_pdf']['error']
                ?? UPLOAD_ERR_NO_FILE
            ) !== UPLOAD_ERR_NO_FILE;

        if ($hayPdfNuevo) {
            $resultadoPdf = PdfLibro::subir(
                $_FILES['archivo_pdf']
            );

            if (isset($resultadoPdf['error'])) {
                /*
                 * Si ya se había guardado una imagen nueva,
                 * se elimina para evitar archivos huérfanos.
                 */
                if (
                    $imagenNueva !== null ||
                    $thumbnailNuevo !== null
                ) {
                    ImagenLibro::eliminar(
                        $imagenNueva,
                        $thumbnailNuevo
                    );
                }

                $this->redirigirFormulario(
                    'pdf',
                    $id
                );
            }

            $pdfNuevo = $resultadoPdf['pdf'];
            $archivoPdf = $pdfNuevo;
        }

        /*
         * Firma digital de los datos principales.
         */
        $firmaData = implode('|', [
            $titulo,
            $autor,
            $categoriaId,
            $costo,
            $origen,
            $unidades,
            $tipoAcceso,
            $precioAcceso,
            $diasAcceso ?? '',
            $institucionOrigen ?? '',
            $urlExterno ?? ''
        ]);

        $firma = hash(
            'sha256',
            $firmaData
        );

        $datos = [
            'titulo' => $titulo,
            'autor' => $autor,
            'descripcion' => $descripcion,
            'categoria_id' => $categoriaId,

            'costo' => $costo,
            'tipo_acceso' => $tipoAcceso,
            'precio_acceso' => $precioAcceso,
            'dias_acceso' => $diasAcceso,

            'origen' => $origen,
            'institucion_origen' => $institucionOrigen,
            'url_externo' => $urlExterno,

            'unidades_totales' => $unidades,

            'imagen' => $imagen,
            'thumbnail' => $thumbnail,
            'archivo_pdf' => $archivoPdf,

            'firma_digital' => $firma
        ];

        try {
            if ($id === null) {
                $this->modelo->crear($datos);
            } else {
                $this->modelo->actualizar(
                    $id,
                    $datos
                );
            }

            /*
             * La base de datos ya fue actualizada.
             * Ahora se pueden eliminar los archivos anteriores.
             */
            if (
                $id !== null &&
                $imagenNueva !== null
            ) {
                ImagenLibro::eliminar(
                    $imagenAnterior,
                    $thumbnailAnterior
                );
            }

            if (
                $id !== null &&
                $pdfNuevo !== null
            ) {
                PdfLibro::eliminar($pdfAnterior);
            }

            header('Location: libros.php?exito=1');
            exit;
        } catch (RuntimeException $e) {
            /*
             * Limpiar archivos nuevos si falla la actualización.
             */
            if (
                $imagenNueva !== null ||
                $thumbnailNuevo !== null
            ) {
                ImagenLibro::eliminar(
                    $imagenNueva,
                    $thumbnailNuevo
                );
            }

            if ($pdfNuevo !== null) {
                PdfLibro::eliminar($pdfNuevo);
            }

            $this->redirigirFormulario(
                'unidades_prestadas',
                $id
            );
        } catch (Throwable $e) {
            /*
             * Evitar archivos huérfanos si falla la consulta.
             */
            if (
                $imagenNueva !== null ||
                $thumbnailNuevo !== null
            ) {
                ImagenLibro::eliminar(
                    $imagenNueva,
                    $thumbnailNuevo
                );
            }

            if ($pdfNuevo !== null) {
                PdfLibro::eliminar($pdfNuevo);
            }

            $this->redirigirFormulario(
                'guardar',
                $id
            );
        }
    }

    /**
     * Elimina un libro y todos sus archivos.
     */
    public function eliminar(): void
    {
        $this->verificarSesionAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: libros.php');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';

        if (!Csrf::validarToken($csrf)) {
            die('Token CSRF inválido.');
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            header(
                'Location: libros.php?error=no_encontrado'
            );
            exit;
        }

        $libro = $this->modelo->obtenerPorId($id);

        if ($libro === null) {
            header(
                'Location: libros.php?error=no_encontrado'
            );
            exit;
        }

        /*
         * No se elimina si existen reservas relacionadas.
         */
        if (
            $this->modelo
                ->contarReservasAsociadas($id) > 0
        ) {
            header(
                'Location: libros.php?error=reserva'
            );
            exit;
        }

        try {
            /*
             * Primero se elimina el registro.
             */
            $this->modelo->eliminar($id);

            /*
             * Después se eliminan sus archivos.
             */
            ImagenLibro::eliminar(
                $libro['imagen'] ?? null,
                $libro['thumbnail'] ?? null
            );

            PdfLibro::eliminar(
                $libro['archivo_pdf'] ?? null
            );

            header('Location: libros.php?exito=1');
            exit;
        } catch (Throwable $e) {
            header(
                'Location: libros.php?error=eliminar'
            );
            exit;
        }
    }

    /**
     * Exporta el catálogo a Excel.
     */
    public function exportarExcel(): void
    {
        $this->verificarSesionAdmin();

        $libros = $this->modelo->obtenerParaExcel();

        ExcelLibro::exportar($libros);
    }
}