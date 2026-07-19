<?php

/*
|--------------------------------------------------------------------------
| Contrato para servicios criptográficos
|--------------------------------------------------------------------------
|
| Toda clase que implemente esta interfaz debe poder:
|
| 1. Transformar un dato en un resultado protegido.
| 2. Verificar un dato original contra un resultado almacenado.
|
| Esto permite utilizar la misma abstracción para contraseñas
| y firmas digitales.
|
*/

interface CryptoInterface
{
    /**
     * Transforma un dato utilizando el mecanismo criptográfico
     * correspondiente.
     */
    public function transformar(string $dato): string;

    /**
     * Verifica si un dato corresponde al resultado almacenado.
     */
    public function verificar(
        string $dato,
        string $resultado
    ): bool;
}