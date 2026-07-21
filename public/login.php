<?php

session_start();

require_once __DIR__ . '/../app/Core/Csrf.php';

/*
|--------------------------------------------------------------------------
| Lógica existente del inicio de sesión
|--------------------------------------------------------------------------
*/

$token = Csrf::generarToken();
$error = $_GET['error'] ?? '';

/*
|--------------------------------------------------------------------------
| Recursos visuales
|--------------------------------------------------------------------------
| La imagen principal del login puede guardarse como:
|
| assets/img/imagen-login-readpoint.jpg
|
| Si no existe, se utilizará el logotipo de ReadPoint.
*/

$imagenesPanel = [
    'assets/img/imagen-login-readpoint.webp',
    'assets/img/imagen-login-readpoint.png',
    'assets/img/imagen-login-readpoint.jpg',
    'assets/img/imagen-login-readpoint.jpeg',
    'assets/img/LogoReadPoit.jpeg',
    'assets/img/PortadaLogin.png',
];

$rutaImagenPanel = null;

foreach ($imagenesPanel as $imagen) {
    if (is_file(__DIR__ . '/' . $imagen)) {
        $rutaImagenPanel = $imagen;
        break;
    }
}

/*
|--------------------------------------------------------------------------
| Símbolo pequeño de ReadPoint
|--------------------------------------------------------------------------
*/

$simbolosReadPoint = [
    'assets/img/readpoint-mark.svg',

    'assets/img/LogoReadPoint1.png',
];

$rutaSimbolo = null;

foreach ($simbolosReadPoint as $simbolo) {
    if (is_file(__DIR__ . '/' . $simbolo)) {
        $rutaSimbolo = $simbolo;
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Accede a tu cuenta de ReadPoint para consultar el catálogo y gestionar tus reservas."
    >

    <title>Iniciar sesión | ReadPoint</title>

    <!-- Fuente utilizada en los títulos -->
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- Estilos exclusivos del inicio de sesión -->
    <link
        rel="stylesheet"
        href="assets/css/login.css"
    >
</head>

<body class="pagina-login">

    <main class="contenido-principal-login">

        <section
            class="contenedor-login"
            aria-labelledby="titulo-login"
        >

            <!-- =================================================
                 LADO IZQUIERDO: FORMULARIO
            ================================================== -->
            <div class="lado-formulario-login">

                <div class="contenido-formulario-login">

                    <a
                        class="marca-login"
                        href="index.php"
                        aria-label="Volver a la página principal de ReadPoint"
                    >

                        <?php if ($rutaSimbolo !== null): ?>

                            <img
                                class="logo-login"
                                src="<?= htmlspecialchars(
                                    $rutaSimbolo,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                alt="Logo de ReadPoint"
                            >

                        <?php else: ?>

                            <span
                                class="logo-login-alternativo"
                                aria-hidden="true"
                            >
                                RP
                            </span>

                        <?php endif; ?>

                        <span class="nombre-readpoint-login">
                            ReadPoint
                        </span>

                    </a>

                    <div class="encabezado-formulario-login">

                        <h1
                            class="titulo-login"
                            id="titulo-login"
                        >
                            Bienvenido de nuevo
                        </h1>

                        <p class="descripcion-login">
                            Ingresa tus credenciales para acceder
                        </p>

                    </div>

                    <!-- =========================================
                         MENSAJES DE ERROR EXISTENTES
                    ========================================== -->
                    <?php if ($error === 'credenciales'): ?>

                        <div
                            class="mensaje-error-login"
                            role="alert"
                        >
                            <strong>
                                No pudimos iniciar la sesión.
                            </strong>

                            <span>
                                El usuario o la contraseña son incorrectos.
                            </span>
                        </div>

                    <?php elseif ($error === 'bloqueado'): ?>

                        <div
                            class="mensaje-error-login"
                            role="alert"
                        >
                            <strong>
                                Cuenta bloqueada.
                            </strong>

                            <span>
                                La cuenta fue bloqueada después de tres
                                intentos fallidos.
                            </span>
                        </div>

                    <?php elseif ($error === 'datos'): ?>

                        <div
                            class="mensaje-error-login"
                            role="alert"
                        >
                            <strong>
                                Verifica los datos ingresados.
                            </strong>

                            <span>
                                La contraseña debe contener entre 8 y
                                12 caracteres.
                            </span>
                        </div>

                    <?php endif; ?>

                    <!-- =========================================
                         FORMULARIO
                    ========================================== -->
                    <form
                        class="formulario-login"
                        action="procesar_login.php"
                        method="POST"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(
                                $token,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                        <div class="grupo-campo-login">

                            <label
                                class="etiqueta-campo-login"
                                for="usuario"
                            >
                                Usuario
                            </label>

                            <input
                                class="campo-login"
                                id="usuario"
                                type="text"
                                name="usuario"
                                placeholder="Ingresa tu usuario"
                                autocomplete="username"
                                required
                            >

                        </div>

                        <div class="grupo-campo-login">

                            <label
                                class="etiqueta-campo-login"
                                for="password"
                            >
                                Contraseña
                            </label>

                            <input
                                class="campo-login"
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Ingresa tu contraseña"
                                autocomplete="current-password"
                                required
                            >

                        </div>

                        <button
                            class="boton-iniciar-sesion"
                            type="submit"
                        >
                            <span class="texto-boton-login">
                                Iniciar sesión
                            </span>

                            <span
                                class="icono-boton-login"
                                aria-hidden="true"
                            >
                                →
                            </span>
                        </button>

                    </form>

                    <p class="aviso-acceso-login">
                        El acceso está disponible exclusivamente para
                        miembros autorizados de ReadPoint.
                    </p>

                    <a
                        class="enlace-volver-inicio"
                        href="index.php"
                    >
                        <span aria-hidden="true">
                            ←
                        </span>

                        <span>
                            Volver a la página principal
                        </span>
                    </a>

                </div>

            </div>

            <!-- =================================================
                 LADO DERECHO: PANEL VISUAL
            ================================================== -->
            <aside
                class="lado-visual-login"
                aria-label="Presentación de ReadPoint"
            >

                <?php if ($rutaImagenPanel !== null): ?>

                    <img
                        class="imagen-panel-login"
                        src="<?= htmlspecialchars(
                            $rutaImagenPanel,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        alt=""
                    >

                <?php endif; ?>

                <div
                    class="capa-oscura-panel-login"
                    aria-hidden="true"
                ></div>

                <div class="contenido-panel-login">

                    <span class="distintivo-panel-login">
                        Biblioteca digital
                    </span>

                    <div class="texto-panel-login">

                        <h2 class="titulo-panel-login">
                            Descubre, reserva y disfruta tu próxima lectura.
                        </h2>

                        <p class="descripcion-panel-login">
                            Accede al catálogo de ReadPoint y encuentra
                            recursos para aprender, investigar y explorar
                            nuevas historias.
                        </p>

                    </div>

                </div>

            </aside>

        </section>

    </main>

</body>

</html>