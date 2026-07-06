<?php

class NoCache
{
    /**
     * Envía encabezados que impiden que el navegador guarde
     * en caché las páginas protegidas. Así, al presionar "Atrás"
     * después de cerrar sesión, el navegador debe volver a pedir
     * la página al servidor (que lo redirige a login).
     */
    public static function aplicar(): void
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");
    }
}