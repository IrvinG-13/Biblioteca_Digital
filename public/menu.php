<?php

$rolActual = $_SESSION["rol"] ?? "";

$paginaActual = basename(
    $_SERVER["PHP_SELF"]
);

$paginaInicio = $rolActual === "estudiante" || $rolActual === "profesor"
    ? "catalogo.php"
    : "dashboard.php";

?>

<nav class="sidebar">

    <div class="sidebar-title">
        Biblioteca
    </div>

    <a
        class="<?php echo $paginaActual === $paginaInicio
            ? 'active'
            : ''; ?>"
        href="<?php echo $paginaInicio; ?>"
    >
        Inicio
    </a>

    <?php if ($rolActual === "admin"): ?>

        <a
            class="<?php echo $paginaActual === 'usuarios.php'
                ? 'active'
                : ''; ?>"
            href="usuarios.php"
        >
            Usuarios
        </a>

        <a
            class="<?php echo $paginaActual === 'estudiantes.php'
                ? 'active'
                : ''; ?>"
            href="estudiantes.php"
        >
            Estudiantes
        </a>

        <a
            class="<?php echo $paginaActual === 'profesores.php'
                ? 'active'
                : ''; ?>"
            href="profesores.php"
        >
            Profesores
        </a>

        <a class="<?php echo $paginaActual === 'materias.php'
                ? 'active'
                : ''; ?>"
            href="materias.php"
        >
            Materias
        </a>

        <a
            class="<?php echo $paginaActual === 'carreras.php'
                ? 'active'
                : ''; ?>"
            href="carreras.php"
        >
            Carreras
        </a>

        <a
            class="<?php echo $paginaActual === 'categorias.php'
                ? 'active'
                : ''; ?>"
            href="categorias.php"
        >
            Categorías
        </a>

        <a
            class="<?php echo in_array(
                $paginaActual,
                [
                    'libros.php',
                    'libro_form.php'
                ],
                true
            ) ? 'active' : ''; ?>"
            href="libros.php"
        >
            Libros
        </a>

        <a
            class="<?php echo in_array(
                $paginaActual,
                [
                    'solicitudes_admin.php',
                    'solicitud_estado_procesar.php'
                ],
                true
            ) ? 'active' : ''; ?>"
            href="solicitudes_admin.php"
        >
            Solicitudes de libros
        </a>

        <a
            class="<?php echo in_array(
                $paginaActual,
                [
                    'reporte_reservas.php',
                    'reserva_exportar.php'
                ],
                true
            ) ? 'active' : ''; ?>"
            href="reporte_reservas.php"
        >
            Reporte de reservas
        </a>

    <?php endif; ?>

    <?php if ($rolActual === "estudiante" || $rolActual === "profesor"): ?>

        <a
            class="<?php echo $paginaActual === 'mis_reservas.php'
                ? 'active'
                : ''; ?>"
            href="mis_reservas.php"
        >
            Mis libros
        </a>

        <a
            class="<?php echo $paginaActual === 'mis_solicitudes.php'
                ? 'active'
                : ''; ?>"
            href="mis_solicitudes.php"
        >
            Mis solicitudes
        </a>

        <a
            class="<?php echo $paginaActual === 'catalogo.php'
                ? 'active'
                : ''; ?>"
            href="catalogo.php"
        >
            Catálogo de libros
        </a>

    <?php endif; ?>

    <div class="sidebar-bottom">

        <a href="logout.php">
            Salir
        </a>

    </div>

</nav>