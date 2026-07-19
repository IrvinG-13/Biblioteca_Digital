<?php

require_once __DIR__
    . '/../Interfaces/CryptoInterface.php';

/*
|--------------------------------------------------------------------------
| Servicio para hashing de contraseñas
|--------------------------------------------------------------------------
|
| Esta clase implementa CryptoInterface y centraliza:
|
| 1. La creación del hash de una contraseña.
| 2. La verificación de una contraseña contra un hash almacenado.
|
*/

class PasswordHasher implements CryptoInterface
{
    /**
     * Genera el hash seguro de una contraseña.
     */
    public function transformar(string $dato): string
    {
        $dato = trim($dato);

        if ($dato === '') {
            throw new InvalidArgumentException(
                'La contraseña no puede estar vacía.'
            );
        }

        $hash = password_hash(
            $dato,
            PASSWORD_DEFAULT
        );

        if ($hash === false) {
            throw new RuntimeException(
                'No fue posible generar el hash de la contraseña.'
            );
        }

        return $hash;
    }

    /**
     * Verifica la contraseña contra el hash almacenado.
     */
    public function verificar(
        string $dato,
        string $resultado
    ): bool {
        if (
            $dato === ''
            || $resultado === ''
        ) {
            return false;
        }

        return password_verify(
            $dato,
            $resultado
        );
    }
}