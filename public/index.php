<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Configuración de la página pública
|--------------------------------------------------------------------------
*/

$rutaLogo = 'assets/img/LogoReadPoint1.png';
$logoDisponible = is_file(__DIR__ . '/' . $rutaLogo);

$anioActual = date('Y');

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
        content="ReadPoint es una biblioteca digital para descubrir libros, consultar su disponibilidad y gestionar reservas."
    >

    <title>ReadPoint | Tu biblioteca digital</title>
    <script>
        document.documentElement.classList.add('animaciones-disponibles');
    </script>
    <!-- Fuente de los títulos principales -->
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

    <!-- Estilos de la página pública -->
    <link
        rel="stylesheet"
        href="assets/css/public-home.css"
    >
</head>

<body class="pagina-publica">

    <!-- Enlace para navegación mediante teclado -->
    <a
        class="enlace-saltar-contenido"
        href="#contenido-principal"
    >
        Saltar al contenido principal
    </a>

    <!-- =====================================================
         ENCABEZADO
    ====================================================== -->
    <header class="encabezado-publico">

        <div class="contenedor-publico contenido-encabezado">

            <a
                class="marca-readpoint"
                href="#inicio"
                aria-label="Ir al inicio de ReadPoint"
            >

                <?php if ($logoDisponible): ?>

                    <img
                        class="logo-encabezado"
                        src="<?= htmlspecialchars(
                            $rutaLogo,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        alt="Logo de ReadPoint"
                    >

                <?php else: ?>

                    <span
                        class="logo-alternativo"
                        aria-hidden="true"
                    >
                        RP
                    </span>

                <?php endif; ?>

                <span class="nombre-readpoint">
                    ReadPoint
                </span>

            </a>

            <nav
                class="menu-principal"
                aria-label="Navegación principal"
            >

                <a href="#inicio">
                    Inicio
                </a>

                <a href="#servicios">
                    Servicios
                </a>

                <a href="#contacto">
                    Contacto
                </a>

                <a
                    class="boton-principal boton-pequeno"
                    href="login.php"
                >
                    Iniciar sesión
                </a>

            </nav>

        </div>

    </header>

    <!-- =====================================================
         CONTENIDO PRINCIPAL
    ====================================================== -->
    <main id="contenido-principal">

        <!-- =================================================
             PRESENTACIÓN DE READPOINT
        ================================================== -->
        <section
            class="seccion-inicio"
            id="inicio"
        >

            <div class="contenedor-publico contenido-inicio">

                <div class="texto-inicio">


                    <h1 class="titulo-inicio">
                        Tu próxima lectura comienza en ReadPoint.
                    </h1>

                    <p class="descripcion-inicio">
                        Descubre libros, consulta su disponibilidad y
                        gestiona tus reservas desde una biblioteca diseñada
                        para acercarte al conocimiento.
                    </p>

                    <div class="botones-inicio">

                        <a
                            class="boton-principal"
                            href="login.php"
                        >
                            Iniciar sesión
                        </a>

                        <a
                            class="boton-secundario"
                            href="#servicios"
                        >
                            Explorar servicios
                        </a>

                    </div>

                    <p class="mensaje-acceso">
                        El acceso está disponible exclusivamente para
                        miembros autorizados de ReadPoint.
                    </p>

                </div>

                <!-- Panel de servicios destacados -->
                <aside
                    class="panel-servicios-destacados"
                    aria-label="Servicios destacados de ReadPoint"
                >

                    <div class="encabezado-panel-servicios">

                        <div>

                            <span class="etiqueta-panel">
                                Descubre ReadPoint
                            </span>

                            <strong class="titulo-panel">
                                Todo para encontrar tu próxima lectura
                            </strong>

                        </div>

                    </div>

                    <div class="lista-servicios-destacados">

                        <article class="servicio-destacado">

                            <div class="numero-servicio-destacado">
                                01
                            </div>

                            <div>

                                <h2 class="titulo-servicio-destacado">
                                    Catálogo organizado
                                </h2>

                                <p class="descripcion-servicio-destacado">
                                    Explora libros por título, autor,
                                    categoría o área de conocimiento.
                                </p>

                            </div>

                        </article>

                        <article class="servicio-destacado">

                            <div class="numero-servicio-destacado">
                                02
                            </div>

                            <div>

                                <h2 class="titulo-servicio-destacado">
                                    Disponibilidad de ejemplares
                                </h2>

                                <p class="descripcion-servicio-destacado">
                                    Comprueba qué libros están disponibles
                                    antes de realizar una reserva.
                                </p>

                            </div>

                        </article>

                        <article class="servicio-destacado">

                            <div class="numero-servicio-destacado">
                                03
                            </div>

                            <div>

                                <h2 class="titulo-servicio-destacado">
                                    Reservas en línea
                                </h2>

                                <p class="descripcion-servicio-destacado">
                                    Gestiona tus reservas directamente
                                    desde tu cuenta.
                                </p>

                            </div>

                        </article>

                    </div>

                    <a
                        class="enlace-panel-servicios"
                        href="login.php"
                    >
                        <span>
                            Acceder a mi cuenta
                        </span>

                        <span aria-hidden="true">
                            →
                        </span>
                    </a>

                </aside>

            </div>

        </section>

        <!-- =================================================
             SERVICIOS
        ================================================== -->
        <section
            class="seccion-publica seccion-servicios"
            id="servicios"
        >

            <div class="contenedor-publico">

                <div class="encabezado-seccion">

                    <p class="etiqueta-seccion">
                        Nuestros servicios
                    </p>

                    <h2 class="titulo-seccion">
                        Una forma más sencilla de acceder a los libros
                    </h2>

                    <p class="descripcion-seccion">
                        ReadPoint reúne los principales servicios de la
                        biblioteca en un entorno digital claro, organizado
                        y accesible.
                    </p>

                </div>

                <div class="lista-servicios">

                    <article class="tarjeta-servicio">

                        <span
                            class="numero-tarjeta-servicio"
                            aria-hidden="true"
                        >
                            01
                        </span>

                        <h3 class="titulo-tarjeta-servicio">
                            Catálogo digital
                        </h3>

                        <p class="descripcion-tarjeta-servicio">
                            Consulta la colección disponible y encuentra
                            libros por título, autor, tema o categoría.
                        </p>

                    </article>

                    <article class="tarjeta-servicio">

                        <span
                            class="numero-tarjeta-servicio"
                            aria-hidden="true"
                        >
                            02
                        </span>

                        <h3 class="titulo-tarjeta-servicio">
                            Reservas de libros
                        </h3>

                        <p class="descripcion-tarjeta-servicio">
                            Selecciona los ejemplares disponibles y gestiona
                            tus reservas desde tu cuenta.
                        </p>

                    </article>

                    <article class="tarjeta-servicio">

                        <span
                            class="numero-tarjeta-servicio"
                            aria-hidden="true"
                        >
                            03
                        </span>

                        <h3 class="titulo-tarjeta-servicio">
                            Búsqueda especializada
                        </h3>

                        <p class="descripcion-tarjeta-servicio">
                            Utiliza diferentes criterios de búsqueda para
                            localizar rápidamente el recurso que necesitas.
                        </p>

                    </article>

                    <article class="tarjeta-servicio">

                        <span
                            class="numero-tarjeta-servicio"
                            aria-hidden="true"
                        >
                            04
                        </span>

                        <h3 class="titulo-tarjeta-servicio">
                            Solicitud de nuevos títulos
                        </h3>

                        <p class="descripcion-tarjeta-servicio">
                            Comunica tu interés por libros que todavía no
                            forman parte de nuestra colección.
                        </p>

                    </article>

                </div>

            </div>

        </section>





        <!-- =================================================
             CONTACTO
        ================================================== -->
        <section
            class="seccion-publica seccion-contacto"
            id="contacto"
        >

            <div class="contenedor-publico contenido-contacto">

                <div class="texto-contacto">

                    <p class="etiqueta-seccion">
                        Contacto
                    </p>

                    <h2 class="titulo-contacto">
                        Estamos para ayudarte
                    </h2>

                    <p class="descripcion-contacto">
                        Comunícate con nuestro equipo para recibir
                        asistencia con el acceso, las reservas o los
                        servicios disponibles en ReadPoint.
                    </p>

                    <p class="descripcion-contacto">
                        Nuestro personal puede orientarte sobre el uso del
                        catálogo, la disponibilidad de ejemplares y el
                        acceso a tu cuenta.
                    </p>

                </div>

                <div class="panel-contacto">

                    <h3 class="titulo-panel-contacto">
                        Canales de atención
                    </h3>

                    <div class="dato-contacto">

                        <strong class="nombre-dato-contacto">
                            Atención al usuario
                        </strong>

                        <span class="descripcion-dato-contacto">
                            Consultas sobre libros, reservas y disponibilidad
                            de ejemplares.
                        </span>

                    </div>

                    <div class="dato-contacto">

                        <strong class="nombre-dato-contacto">
                            Soporte de acceso
                        </strong>

                        <span class="descripcion-dato-contacto">
                            Asistencia para ingresar a tu cuenta o resolver
                            inconvenientes de acceso.
                        </span>

                    </div>

                    <!--
                    Agrega aquí el correo real cuando esté definido:

                    <div class="dato-contacto">

                        <strong class="nombre-dato-contacto">
                            Correo electrónico
                        </strong>

                        <a
                            class="enlace-contacto"
                            href="mailto:correo@readpoint.com"
                        >
                            correo@readpoint.com
                        </a>

                    </div>
                    -->

                    <!--
                    También puedes agregar teléfono, horario y ubicación:

                    <div class="dato-contacto">
                        <strong class="nombre-dato-contacto">
                            Horario de atención
                        </strong>

                        <span class="descripcion-dato-contacto">
                            Lunes a viernes, de 8:00 a. m. a 5:00 p. m.
                        </span>
                    </div>
                    -->

                    <a
                        class="boton-contacto"
                        href="login.php"
                    >
                        Acceder a ReadPoint
                    </a>

                </div>

            </div>

        </section>

    </main>

    <!-- =====================================================
         PIE DE PÁGINA
    ====================================================== -->
    <footer class="pie-pagina">

        <div class="contenedor-publico contenido-pie-pagina">

            <div class="informacion-pie-pagina">

                <strong class="nombre-pie-pagina">
                    ReadPoint
                </strong>

                <p class="descripcion-pie-pagina">
                    Lecturas, conocimiento y recursos al alcance de nuestra
                    comunidad.
                </p>

            </div>



            <p class="derechos-pie-pagina">
                &copy; <?= htmlspecialchars(
                    $anioActual,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
                ReadPoint. Todos los derechos reservados.
            </p>

        </div>

    </footer>
    <script
    src="assets/js/animaciones-index.js"
    defer
    ></script>
</body>

</html>