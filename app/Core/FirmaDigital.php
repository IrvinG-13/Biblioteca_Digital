<?php

class FirmaDigital
{
    private static string $clave = "clave_privada_biblioteca_2026";

    public static function generarFirmaReserva(
        int $estudianteId,
        int $libroId,
        string $fechaReserva
    ): string {
        $datos = $estudianteId . "|" . $libroId . "|" . $fechaReserva;

        return hash_hmac("sha256", $datos, self::$clave);
    }

    public static function verificarFirmaReserva(
        int $estudianteId,
        int $libroId,
        string $fechaReserva,
        string $firmaGuardada
    ): bool {
        $firmaCalculada = self::generarFirmaReserva($estudianteId, $libroId, $fechaReserva);

        return hash_equals($firmaCalculada, $firmaGuardada);
    }
}