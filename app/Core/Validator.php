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
}