<?php

class Validator
{
    public static function usuarioValido(string $usuario): bool
    {
        return strlen($usuario) >= 3 && strlen($usuario) <= 50;
    }

    public static function passwordValida(string $password): bool
    {
        return strlen($password) >= 8 && strlen($password) <= 12;
    }

    /**
     * Valida formato de CIP (identificación): letras, números y guiones,
     * entre 5 y 20 caracteres. Ajusta el patrón si tu institución usa
     * un formato distinto de cédula/carné.
     */
    public static function cipValido(string $cip): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9\-]{5,20}$/', $cip);
    }

    /**
     * Valida que la fecha tenga formato Y-m-d, sea una fecha real,
     * y que el estudiante tenga al menos 15 años (regla de sentido común).
     */
    public static function fechaNacimientoValida(string $fecha): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);

        if (!$d || $d->format('Y-m-d') !== $fecha) {
            return false;
        }

        $edadMinima = new DateTime('-15 years');
        return $d <= $edadMinima;
    }
}