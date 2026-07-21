<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Configuración de la página pública
|--------------------------------------------------------------------------
*/

$rutaLogo = 'assets/img/LogoReadPoint1.png';

$logoDisponible = is_file(
    __DIR__ . '/' . $rutaLogo
);

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
        content="ReadPoint es una biblioteca digital para descubrir libros, consultar recursos académicos y gestionar el acceso al conocimiento."
    >

    <title>ReadPoint | Tu biblioteca digital</title>

    <script>
        document.documentElement.classList.add(
            'animaciones-disponibles'
        );
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
        href="assets/css/public-home.css?v=2"
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
                        src="<?php echo htmlspecialchars(
                            $rutaLogo,
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
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

                <a href="#importancia">
                    Importancia
                </a>

                <a href="#tecnologias">
                    Tecnologías
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
                        Descubre libros, consulta su disponibilidad
                        y gestiona tus reservas desde una biblioteca
                        digital diseñada para acercarte al
                        conocimiento.
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
                        El acceso está disponible exclusivamente
                        para miembros autorizados de ReadPoint.
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
                                Todo para encontrar tu próxima
                                lectura
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
                                    Comprueba qué libros están
                                    disponibles antes de realizar
                                    una reserva o compra.
                                </p>

                            </div>

                        </article>

                        <article class="servicio-destacado">

                            <div class="numero-servicio-destacado">
                                03
                            </div>

                            <div>

                                <h2 class="titulo-servicio-destacado">
                                    Lectura digital
                                </h2>

                                <p class="descripcion-servicio-destacado">
                                    Accede a tus libros disponibles
                                    desde el lector integrado de
                                    ReadPoint.
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
                        Una forma más sencilla de acceder a los
                        libros
                    </h2>

                    <p class="descripcion-seccion">
                        ReadPoint reúne los principales servicios
                        de la biblioteca en un entorno digital
                        claro, organizado y accesible.
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
                            Consulta la colección disponible y
                            encuentra libros por título, autor,
                            tema o categoría.
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
                            Reservas y acceso
                        </h3>

                        <p class="descripcion-tarjeta-servicio">
                            Selecciona ejemplares disponibles y
                            gestiona tus libros desde tu cuenta.
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
                            Utiliza diferentes criterios de
                            búsqueda para localizar rápidamente
                            el recurso que necesitas.
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
                            Comunica tu interés por libros que
                            todavía no forman parte de nuestra
                            colección.
                        </p>

                    </article>

                </div>

            </div>

        </section>

        <!-- =================================================
             IMPORTANCIA DE LAS BIBLIOTECAS DIGITALES
        ================================================== -->

        <section
            class="seccion-publica seccion-importancia"
            id="importancia"
        >

            <div class="contenedor-publico contenido-importancia">

                <div class="encabezado-seccion">

                    <p class="etiqueta-seccion">
                        Acceso al conocimiento
                    </p>

                    <h2 class="titulo-seccion">
                        Importancia de las bibliotecas digitales
                    </h2>

                    <p class="descripcion-seccion">
                        Las bibliotecas digitales facilitan el
                        acceso a libros, materiales educativos y
                        recursos académicos desde cualquier lugar
                        y en cualquier momento.
                    </p>

                </div>

                <div class="panel-importancia">

                    <div class="texto-importancia">

                        <p>
                            Las bibliotecas digitales permiten que
                            estudiantes, profesores e investigadores
                            consulten información sin depender de
                            una ubicación física. Mediante un
                            dispositivo con acceso a internet, los
                            usuarios pueden encontrar materiales
                            educativos, libros y recursos académicos
                            de forma rápida y organizada.
                        </p>

                        <p>
                            Estas plataformas reducen las barreras
                            de acceso al conocimiento, apoyan los
                            procesos de aprendizaje y facilitan la
                            investigación. También contribuyen a
                            conservar materiales, centralizar
                            información y mantener los recursos
                            disponibles para una comunidad más
                            amplia.
                        </p>

                    </div>

                    <div class="lista-beneficios-importancia">

                        <article class="beneficio-importancia">

                            <span aria-hidden="true">
                                01
                            </span>

                            <div>

                                <h3>
                                    Acceso remoto
                                </h3>

                                <p>
                                    Permite consultar libros y
                                    recursos académicos desde
                                    cualquier ubicación.
                                </p>

                            </div>

                        </article>

                        <article class="beneficio-importancia">

                            <span aria-hidden="true">
                                02
                            </span>

                            <div>

                                <h3>
                                    Disponibilidad continua
                                </h3>

                                <p>
                                    Los materiales pueden consultarse
                                    en distintos horarios según los
                                    permisos del sistema.
                                </p>

                            </div>

                        </article>

                        <article class="beneficio-importancia">

                            <span aria-hidden="true">
                                03
                            </span>

                            <div>

                                <h3>
                                    Apoyo educativo
                                </h3>

                                <p>
                                    Facilita el aprendizaje, la
                                    investigación y el acceso
                                    organizado al conocimiento.
                                </p>

                            </div>

                        </article>

                    </div>

                </div>

            </div>

        </section>

        <!-- =================================================
             STACK TECNOLÓGICO
        ================================================== -->

        <section
            class="seccion-publica seccion-tecnologias"
            id="tecnologias"
        >

            <div class="contenedor-publico">

                <div class="encabezado-seccion">

                    <p class="etiqueta-seccion">
                        Desarrollo del proyecto
                    </p>

                    <h2 class="titulo-seccion">
                        Stack tecnológico
                    </h2>

                    <p class="descripcion-seccion">
                        ReadPoint fue desarrollado con tecnologías
                        orientadas a aplicaciones web y una
                        arquitectura organizada para separar los
                        datos, la lógica y la interfaz del sistema.
                    </p>

                </div>

                <div class="lista-tecnologias">

                    <article class="tarjeta-tecnologia">

                        <span class="numero-tecnologia">
                            01
                        </span>

                        <h3>PHP</h3>

                        <p>
                            Gestiona la lógica del servidor, las
                            sesiones, las validaciones y las
                            operaciones principales del sistema.
                        </p>

                    </article>

                    <article class="tarjeta-tecnologia">

                        <span class="numero-tecnologia">
                            02
                        </span>

                        <h3>MySQL</h3>

                        <p>
                            Almacena y relaciona la información de
                            usuarios, libros, reservas, solicitudes,
                            accesos y facturas.
                        </p>

                    </article>

                    <article class="tarjeta-tecnologia">

                        <span class="numero-tecnologia">
                            03
                        </span>

                        <h3>HTML</h3>

                        <p>
                            Define la estructura semántica de las
                            páginas, formularios y componentes
                            visuales del proyecto.
                        </p>

                    </article>

                    <article class="tarjeta-tecnologia">

                        <span class="numero-tecnologia">
                            04
                        </span>

                        <h3>CSS</h3>

                        <p>
                            Controla el diseño visual, la
                            distribución del contenido y la
                            adaptación responsiva de la interfaz.
                        </p>

                    </article>

                    <article class="tarjeta-tecnologia">

                        <span class="numero-tecnologia">
                            05
                        </span>

                        <h3>JavaScript</h3>

                        <p>
                            Añade interactividad, comportamiento
                            dinámico y animaciones dentro de la
                            página pública.
                        </p>

                    </article>

                    <article class="tarjeta-tecnologia">

                        <span class="numero-tecnologia">
                            06
                        </span>

                        <h3>WAMP</h3>

                        <p>
                            Proporciona el entorno local de
                            desarrollo mediante Windows, Apache,
                            MySQL y PHP.
                        </p>

                    </article>

                    <article class="tarjeta-tecnologia tecnologia-mvc">

                        <span class="numero-tecnologia">
                            07
                        </span>

                        <h3>Arquitectura MVC</h3>

                        <p>
                            Organiza la aplicación en modelos,
                            vistas y controladores. Esta separación
                            mejora el orden, el mantenimiento y la
                            escalabilidad del sistema.
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
                        asistencia con el acceso, las reservas o
                        los servicios disponibles en ReadPoint.
                    </p>

                    <p class="descripcion-contacto">
                        Nuestro personal puede orientarte sobre el
                        uso del catálogo, la disponibilidad de
                        ejemplares y el acceso a tu cuenta.
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
                            Consultas sobre libros, reservas y
                            disponibilidad de ejemplares.
                        </span>

                    </div>

                    <div class="dato-contacto">

                        <strong class="nombre-dato-contacto">
                            Soporte de acceso
                        </strong>

                        <span class="descripcion-dato-contacto">
                            Asistencia para ingresar a tu cuenta
                            o resolver inconvenientes de acceso.
                        </span>

                    </div>

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
                    Lecturas, conocimiento y recursos al alcance
                    de nuestra comunidad.
                </p>

            </div>

            <p class="derechos-pie-pagina">

                &copy;

                <?php echo htmlspecialchars(
                    $anioActual,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>

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