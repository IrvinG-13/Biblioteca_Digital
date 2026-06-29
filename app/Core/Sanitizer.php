<?php

class Sanitizer
{
    public static function limpiarTexto(string $dato): string
    {
        return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
    }
}