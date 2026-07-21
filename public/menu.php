<?php

/*
|--------------------------------------------------------------------------
| Rol y página actual
|--------------------------------------------------------------------------
*/

$rolActual = $_SESSION["rol"] ?? "";

$paginaActual = basename(
    $_SERVER["PHP_SELF"]
);

/*
|--------------------------------------------------------------------------
| Página inicial según el rol
|--------------------------------------------------------------------------
*/

$paginaInicio = in_array(
    $rolActual,
    ["estudiante", "profesor"],
    true
)
    ? "catalogo.php"
    : "dashboard.php";

/*
|--------------------------------------------------------------------------
| Logo de ReadPoint
|--------------------------------------------------------------------------
*/

$rutaLogoMenu = "assets/img/LogoReadPoint2.png";

?>

<nav class="sidebar">

    <div class="encabezado-sidebar">

    <img
        class="logo-sidebar"
        src="<?php echo htmlspecialchars(
            $rutaLogoMenu,
            ENT_QUOTES,
            "UTF-8"
        ); ?>"
        alt="Logo de ReadPoint"
    >

    </div>

    <div class="contenido-enlaces-sidebar">

        <!-- Inicio -->

        <a
            class="enlace-sidebar <?php echo $paginaActual === $paginaInicio
                ? "active"
                : ""; ?>"
            href="<?php echo htmlspecialchars(
                $paginaInicio,
                ENT_QUOTES,
                "UTF-8"
            ); ?>"
        >
            <span class="icono-sidebar" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M3 10.5 12 3l9 7.5"></path>
                    <path d="M5 9.5V21h14V9.5"></path>
                    <path d="M9 21v-7h6v7"></path>
                </svg>
            </span>

            <span class="texto-enlace-sidebar">
                Inicio
            </span>
        </a>

        <!-- =========================================================
             MENÚ DEL ADMINISTRADOR
        ========================================================== -->

        <?php if ($rolActual === "admin"): ?>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "usuarios.php",
                        "usuario_form.php",
                        "usuario_procesar.php",
                        "usuario_estado.php",
                        "usuario_eliminar.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="usuarios.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="9" cy="8" r="3"></circle>
                        <path d="M3.5 19c.5-3.5 2.5-5 5.5-5s5 1.5 5.5 5"></path>
                        <circle cx="17" cy="9" r="2.3"></circle>
                        <path d="M15.5 14.5c3.2-.4 5 1.1 5.5 4.5"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Usuarios
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "estudiantes.php",
                        "estudiante_form.php",
                        "estudiante_procesar.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="estudiantes.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="m3 9 9-5 9 5-9 5-9-5Z"></path>
                        <path d="M7 12v4.5c2.8 2 7.2 2 10 0V12"></path>
                        <path d="M21 9v6"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Estudiantes
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "profesores.php",
                        "profesor_form.php",
                        "profesor_procesar.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="profesores.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="8" cy="7" r="3"></circle>
                        <path d="M3 20c.4-4 2.2-6 5-6s4.6 2 5 6"></path>
                        <path d="M15 5h6v10h-6"></path>
                        <path d="m16.5 8 1.5 1.5L20 7"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Profesores
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "materias.php",
                        "materia_form.php",
                        "materia_procesar.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="materias.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 3h11l3 3v15H5V3Z"></path>
                        <path d="M16 3v4h4"></path>
                        <path d="M8 11h8M8 15h8"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Materias
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "carreras.php",
                        "carrera_form.php",
                        "carrera_procesar.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="carreras.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="m15 9-2 4-4 2 2-4 4-2Z"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Carreras
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "categorias.php",
                        "categoria_form.php",
                        "categoria_procesar.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="categorias.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="4" y="4" width="6" height="6" rx="1"></rect>
                        <rect x="14" y="4" width="6" height="6" rx="1"></rect>
                        <rect x="4" y="14" width="6" height="6" rx="1"></rect>
                        <rect x="14" y="14" width="6" height="6" rx="1"></rect>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Categorías
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "libros.php",
                        "libro_form.php",
                        "libro_guardar.php",
                        "libro_eliminar.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="libros.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v17H6.5A2.5 2.5 0 0 0 4 22V5.5Z"></path>
                        <path d="M20 5.5A2.5 2.5 0 0 0 17.5 3H13v17h4.5A2.5 2.5 0 0 1 20 22V5.5Z"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Libros
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "facturas.php",
                        "factura_admin_detalle.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="facturas.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"></path>
                        <path d="M9 8h6M9 12h6M9 16h3"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Facturación
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo $paginaActual === "estadisticas.php"
                    ? "active"
                    : ""; ?>"
                href="estadisticas.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 20V10h4v10H4ZM10 20V4h4v16h-4ZM16 20v-7h4v7h-4Z"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Estadísticas
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "solicitudes_admin.php",
                        "solicitud_estado_procesar.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="solicitudes_admin.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 4h14v16H5V4Z"></path>
                        <path d="M8 8h8M8 12h8M8 16h5"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Solicitudes de libros
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "reporte_reservas.php",
                        "reserva_exportar.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="reporte_reservas.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M7 4h10v17H7V4Z"></path>
                        <path d="M9 4V2h6v2"></path>
                        <path d="M10 9h4M10 13h4M10 17h4"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Reporte de reservas
                </span>
            </a>

        <?php endif; ?>

        <!-- =========================================================
             MENÚ DEL ESTUDIANTE Y PROFESOR
        ========================================================== -->

        <?php if (
            in_array(
                $rolActual,
                ["estudiante", "profesor"],
                true
            )
        ): ?>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "mis_reservas.php",
                        "abrir_libro.php",
                        "leer_libro.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="mis_reservas.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 4h14v17H5V4Z"></path>
                        <path d="M8 2v4M16 2v4M5 9h14"></path>
                        <path d="m9 15 2 2 4-5"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Mis libros
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "mis_facturas.php",
                        "factura_detalle.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="mis_facturas.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"></path>
                        <path d="M9 8h6M9 12h6M9 16h3"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Mis facturas
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "mis_solicitudes.php",
                        "solicitud_libro.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="mis_solicitudes.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 4h14v16H5V4Z"></path>
                        <path d="M8 8h8M8 12h8M8 16h5"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Mis solicitudes
                </span>
            </a>

            <a
                class="enlace-sidebar <?php echo in_array(
                    $paginaActual,
                    [
                        "catalogo.php",
                        "libro_detalle.php",
                        "comprar_libro.php"
                    ],
                    true
                )
                    ? "active"
                    : ""; ?>"
                href="catalogo.php"
            >
                <span class="icono-sidebar" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v17H6.5A2.5 2.5 0 0 0 4 22V5.5Z"></path>
                        <path d="M20 5.5A2.5 2.5 0 0 0 17.5 3H13v17h4.5A2.5 2.5 0 0 1 20 22V5.5Z"></path>
                    </svg>
                </span>

                <span class="texto-enlace-sidebar">
                    Catálogo de libros
                </span>
            </a>

        <?php endif; ?>

    </div>

    <div class="sidebar-bottom">

        <a
            class="enlace-sidebar <?php echo $paginaActual === "perfil.php"
                ? "active"
                : ""; ?>"
            href="perfil.php"
        >
            <span class="icono-sidebar" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4"></circle>
                    <path d="M4.5 21c.7-5 3.2-7 7.5-7s6.8 2 7.5 7"></path>
                </svg>
            </span>

            <span class="texto-enlace-sidebar">
                Mi cuenta
            </span>
        </a>

        <a
            class="enlace-sidebar enlace-salir-sidebar"
            href="logout.php"
        >
            <span class="icono-sidebar" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M10 4H5v16h5"></path>
                    <path d="M14 8l4 4-4 4"></path>
                    <path d="M18 12H9"></path>
                </svg>
            </span>

            <span class="texto-enlace-sidebar">
                Salir
            </span>
        </a>

    </div>

</nav>