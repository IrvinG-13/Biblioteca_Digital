<?php

class PdfLibro
{
    public const CARPETA_PDFS = __DIR__ . '/../../uploads/pdfs/';
    public const TAMANO_MAXIMO = 100 * 1024 * 1024; // 100 MB

    /**
     * Valida y guarda el archivo PDF.
     *
     * @return array{pdf?: string, error?: string}
     */
    public static function subir(array $archivo): array
    {
        if (
            !isset(
                $archivo['tmp_name'],
                $archivo['name'],
                $archivo['size'],
                $archivo['error']
            )
        ) {
            return [
                'error' => 'No se recibió correctamente el archivo PDF.'
            ];
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return [
                'error' => self::mensajeErrorSubida((int)$archivo['error'])
            ];
        }

        if (!is_uploaded_file($archivo['tmp_name'])) {
            return [
                'error' => 'El archivo recibido no es válido.'
            ];
        }

        if ((int)$archivo['size'] <= 0) {
            return [
                'error' => 'El archivo PDF está vacío.'
            ];
        }

        if ((int)$archivo['size'] > self::TAMANO_MAXIMO) {
            return [
                'error' => 'El archivo PDF no debe superar los 100 MB.'
            ];
        }

        $extension = strtolower(
            pathinfo($archivo['name'], PATHINFO_EXTENSION)
        );

        if ($extension !== 'pdf') {
            return [
                'error' => 'Solo se permiten archivos con extensión PDF.'
            ];
        }

        /*
         * Se comprueba el tipo MIME real del archivo.
         * Esto evita aceptar otro tipo de archivo renombrado como .pdf.
         */
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipoMime = $finfo->file($archivo['tmp_name']);

        $tiposPermitidos = [
            'application/pdf',
            'application/x-pdf'
        ];

        if (!in_array($tipoMime, $tiposPermitidos, true)) {
            return [
                'error' => 'El archivo seleccionado no es un PDF válido.'
            ];
        }

        /*
         * Validación adicional de la firma inicial del PDF.
         * Los documentos PDF normalmente comienzan con %PDF-.
         */
        $manejador = fopen($archivo['tmp_name'], 'rb');

        if ($manejador === false) {
            return [
                'error' => 'No se pudo comprobar el archivo PDF.'
            ];
        }

        $firmaPdf = fread($manejador, 5);
        fclose($manejador);

        if ($firmaPdf !== '%PDF-') {
            return [
                'error' => 'El contenido del archivo no corresponde a un PDF.'
            ];
        }

        if (!is_dir(self::CARPETA_PDFS)) {
            if (
                !mkdir(self::CARPETA_PDFS, 0755, true)
                && !is_dir(self::CARPETA_PDFS)
            ) {
                return [
                    'error' => 'No se pudo crear la carpeta para guardar los PDF.'
                ];
            }
        }

        try {
            $nombreArchivo = 'pdf_libro_' .
                bin2hex(random_bytes(16)) .
                '.pdf';
        } catch (Exception $e) {
            $nombreArchivo = uniqid('pdf_libro_', true) . '.pdf';
        }

        $rutaDestino = self::CARPETA_PDFS . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            return [
                'error' => 'No se pudo guardar el archivo PDF.'
            ];
        }

        return [
            'pdf' => $nombreArchivo
        ];
    }

    /**
     * Elimina un PDF almacenado anteriormente.
     */
    public static function eliminar(?string $nombrePdf): void
    {
        if ($nombrePdf === null || trim($nombrePdf) === '') {
            return;
        }

        /*
         * basename evita que se utilicen rutas como ../../archivo.
         */
        $nombreSeguro = basename($nombrePdf);
        $rutaPdf = self::CARPETA_PDFS . $nombreSeguro;

        if (is_file($rutaPdf)) {
            unlink($rutaPdf);
        }
    }

    /**
     * Devuelve un mensaje entendible según el error de subida.
     */
    private static function mensajeErrorSubida(int $codigo): string
    {
        return match ($codigo) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE =>
                'El archivo PDF supera el tamaño permitido.',

            UPLOAD_ERR_PARTIAL =>
                'El archivo PDF se subió de manera incompleta.',

            UPLOAD_ERR_NO_FILE =>
                'No se seleccionó ningún archivo PDF.',

            UPLOAD_ERR_NO_TMP_DIR =>
                'No existe la carpeta temporal del servidor.',

            UPLOAD_ERR_CANT_WRITE =>
                'El servidor no pudo guardar el archivo PDF.',

            UPLOAD_ERR_EXTENSION =>
                'Una extensión del servidor detuvo la subida del PDF.',

            default =>
                'Ocurrió un error al subir el archivo PDF.'
        };
    }
}