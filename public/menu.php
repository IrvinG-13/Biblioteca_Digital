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

?>

<nav class="sidebar">

    <div class="sidebar-title">
        Biblioteca
    </div>

    <!-- Inicio -->

    <a
        class="<?php echo $paginaActual === $paginaInicio
            ? "active"
            : ""; ?>"
        href="<?php echo htmlspecialchars(
            $paginaInicio,
            ENT_QUOTES,
            "UTF-8"
        ); ?>"
    >
        Inicio
    </a>

    <!-- =========================================================
         MENÚ DEL ADMINISTRADOR
    ========================================================== -->

    <?php if ($rolActual === "admin"): ?>

        <!-- Usuarios -->

        <a
            class="<?php echo in_array(
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
            Usuarios
        </a>

        <!-- Estudiantes -->

        <a
            class="<?php echo in_array(
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
            Estudiantes
        </a>

        <!-- Profesores -->

        <a
            class="<?php echo in_array(
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
            Profesores
        </a>

        <!-- Materias -->

        <a
            class="<?php echo in_array(
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
            Materias
        </a>

        <!-- Carreras -->

        <a
            class="<?php echo in_array(
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
            Carreras
        </a>

        <!-- Categorías -->

        <a
            class="<?php echo in_array(
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
            Categorías
        </a>

        <!-- Libros -->

        <a
            class="<?php echo in_array(
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
            Libros
        </a>

        <!-- Facturación -->

        <a
            class="<?php echo in_array(
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
            Facturación
        </a>

        <!-- Estadísticas -->

        <a
            class="<?php echo $paginaActual === "estadisticas.php"
                ? "active"
                : ""; ?>"
            href="estadisticas.php"
        >
            Estadísticas
        </a>

        <!-- Solicitudes -->

        <a
            class="<?php echo in_array(
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
            Solicitudes de libros
        </a>

        <!-- Reporte de reservas -->

        <a
            class="<?php echo in_array(
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
            Reporte de reservas
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

        <!-- Mis libros -->

        <a
            class="<?php echo in_array(
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
            Mis libros
        </a>

        <!-- Mis facturas -->

        <a
            class="<?php echo in_array(
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
            Mis facturas
        </a>

        <!-- Mis solicitudes -->

        <a
            class="<?php echo in_array(
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
            Mis solicitudes
        </a>

        <!-- Catálogo -->

        <a
            class="<?php echo in_array(
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
            Catálogo de libros
        </a>

    <?php endif; ?>

    <!-- Cerrar sesión -->

    <div class="sidebar-bottom">

        <a href="logout.php">
            Salir
        </a>

    </div>

</nav>