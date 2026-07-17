<?php

class ImagenLibro
{
    public const CARPETA_IMAGENES = __DIR__ . '/../../uploads/libros/';
    public const CARPETA_THUMBNAILS = __DIR__ . '/../../uploads/thumbnails/';

    public static function subir(array $archivo): array
    {
        if (!isset($archivo["tmp_name"]) || $archivo["error"] !== UPLOAD_ERR_OK) {
            return [
                "error" => "Error al subir la imagen."
            ];
        }

        $extensionesPermitidas = ["jpg", "jpeg", "png"];

        $extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas)) {
            return [
                "error" => "Solo se permiten imágenes JPG, JPEG o PNG."
            ];
        }

        if ($archivo["size"] > 2 * 1024 * 1024) {
            return [
                "error" => "La imagen no debe superar 2MB."
            ];
        }

        if (!is_dir(self::CARPETA_IMAGENES)) {
            mkdir(self::CARPETA_IMAGENES, 0755, true);
        }

        if (!is_dir(self::CARPETA_THUMBNAILS)) {
            mkdir(self::CARPETA_THUMBNAILS, 0755, true);
        }

        $nombreArchivo = uniqid("libro_", true) . "." . $extension;

        $rutaImagen = self::CARPETA_IMAGENES . $nombreArchivo;

        $rutaThumbnail = self::CARPETA_THUMBNAILS . $nombreArchivo;

        if (!move_uploaded_file($archivo["tmp_name"], $rutaImagen)) {
            return [
                "error" => "No se pudo guardar la imagen."
            ];
        }

        self::crearThumbnail($rutaImagen, $rutaThumbnail, $extension, 200, 280);

        return [
            "imagen" => $nombreArchivo,
            "thumbnail" => $nombreArchivo
        ];
    }

    private static function crearThumbnail(
        string $origen,
        string $destino,
        string $extension,
        int $ancho,
        int $alto
    ): void {
        if ($extension === "png") {
            $imagenOriginal = imagecreatefrompng($origen);
        } else {
            $imagenOriginal = imagecreatefromjpeg($origen);
        }

        $anchoOriginal = imagesx($imagenOriginal);
        $altoOriginal = imagesy($imagenOriginal);

        $thumbnail = imagecreatetruecolor($ancho, $alto);

        if ($extension === "png") {
            $blanco = imagecolorallocate($thumbnail, 255, 255, 255);
            imagefill($thumbnail, 0, 0, $blanco);
        }

        imagecopyresampled(
            $thumbnail,
            $imagenOriginal,
            0,
            0,
            0,
            0,
            $ancho,
            $alto,
            $anchoOriginal,
            $altoOriginal
        );

        if ($extension === "png") {
            imagepng($thumbnail, $destino);
        } else {
            imagejpeg($thumbnail, $destino, 85);
        }

        imagedestroy($imagenOriginal);
        imagedestroy($thumbnail);
    }

    public static function eliminar(?string $imagen, ?string $thumbnail): void
    {
        if ($imagen !== null && $imagen !== "") {
            $rutaImagen = self::CARPETA_IMAGENES . $imagen;

            if (file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }
        }

        if ($thumbnail !== null && $thumbnail !== "") {
            $rutaThumbnail = self::CARPETA_THUMBNAILS . $thumbnail;

            if (file_exists($rutaThumbnail)) {
                unlink($rutaThumbnail);
            }
        }
    }
}