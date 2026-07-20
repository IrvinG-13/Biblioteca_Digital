<?php

class SesionGuard
{
    /**
     * Bloquea el acceso a cualquier módulo protegido si el usuario
     * todavía tiene pendiente el cambio de contraseña obligatorio.
     * Debe llamarse DESPUÉS de confirmar que hay sesión activa.
     */
    public static function bloquearSiCambioPasswordPendiente(): void
    {
        if ((int) ($_SESSION["cambio_password"] ?? 0) === 1) {
            header("Location: perfil.php?forzado=1");
            exit;
        }
    }
}