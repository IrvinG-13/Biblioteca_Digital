<?php

require_once __DIR__
    . '/../Interfaces/CryptoInterface.php';

/*
|--------------------------------------------------------------------------
| Servicio de firma digital
|--------------------------------------------------------------------------
|
| Esta clase implementa CryptoInterface para utilizar el mismo contrato
| que PasswordHasher.
|
| También conserva los métodos generarFirmaReserva() y
| verificarFirmaReserva(), porque pueden estar siendo utilizados
| actualmente por los controladores o modelos del proyecto.
|
*/

class FirmaDigital implements CryptoInterface
{
    /*
    |--------------------------------------------------------------------------
    | Clave privada
    |--------------------------------------------------------------------------
    |
    | Se utiliza para generar firmas mediante HMAC SHA-256.
    |
    */

    private const CLAVE =
        'clave_privada_biblioteca_2026';

    /*
    |--------------------------------------------------------------------------
    | Implementación de CryptoInterface
    |--------------------------------------------------------------------------
    */

    /**
     * Genera una firma digital para cualquier cadena de datos.
     */
    public function transformar(string $dato): string
    {
        $dato = trim($dato);

        if ($dato === '') {
            throw new InvalidArgumentException(
                'Los datos que se desean firmar no pueden estar vacíos.'
            );
        }

        return hash_hmac(
            'sha256',
            $dato,
            self::CLAVE
        );
    }

    /**
     * Verifica si una cadena corresponde con una firma almacenada.
     */
    public function verificar(
        string $dato,
        string $resultado
    ): bool {
        $dato = trim($dato);
        $resultado = trim($resultado);

        if (
            $dato === ''
            || $resultado === ''
        ) {
            return false;
        }

        $firmaCalculada = $this->transformar(
            $dato
        );

        return hash_equals(
            $firmaCalculada,
            $resultado
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos específicos para reservas
    |--------------------------------------------------------------------------
    */

    /**
     * Genera la firma digital de una reserva.
     */
    public static function generarFirmaReserva(
        int $estudianteId,
        int $libroId,
        string $fechaReserva
    ): string {
        self::validarDatosReserva(
            $estudianteId,
            $libroId,
            $fechaReserva
        );

        $datos = self::construirDatosReserva(
            $estudianteId,
            $libroId,
            $fechaReserva
        );

        $servicio = new self();

        return $servicio->transformar(
            $datos
        );
    }

    /**
     * Verifica la firma digital guardada de una reserva.
     */
    public static function verificarFirmaReserva(
        int $estudianteId,
        int $libroId,
        string $fechaReserva,
        string $firmaGuardada
    ): bool {
        if (trim($firmaGuardada) === '') {
            return false;
        }

        try {
            self::validarDatosReserva(
                $estudianteId,
                $libroId,
                $fechaReserva
            );
        } catch (InvalidArgumentException $e) {
            return false;
        }

        $datos = self::construirDatosReserva(
            $estudianteId,
            $libroId,
            $fechaReserva
        );

        $servicio = new self();

        return $servicio->verificar(
            $datos,
            $firmaGuardada
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos auxiliares
    |--------------------------------------------------------------------------
    */

    /**
     * Construye la cadena que será firmada.
     */
    private static function construirDatosReserva(
        int $estudianteId,
        int $libroId,
        string $fechaReserva
    ): string {
        return $estudianteId
            . '|'
            . $libroId
            . '|'
            . trim($fechaReserva);
    }

    /**
     * Valida la información necesaria para firmar una reserva.
     */
    private static function validarDatosReserva(
        int $estudianteId,
        int $libroId,
        string $fechaReserva
    ): void {
        if ($estudianteId <= 0) {
            throw new InvalidArgumentException(
                'El identificador del estudiante no es válido.'
            );
        }

        if ($libroId <= 0) {
            throw new InvalidArgumentException(
                'El identificador del libro no es válido.'
            );
        }

        if (trim($fechaReserva) === '') {
            throw new InvalidArgumentException(
                'La fecha de reserva no puede estar vacía.'
            );
        }
    }
}