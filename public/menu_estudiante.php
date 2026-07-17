<?php

$paginaActual = basename($_SERVER['PHP_SELF']);
?>

<aside class="student-sidebar">

    <div>
        <a href="catalogo.php" class="student-brand">

            <span class="student-brand-icon">
                B
            </span>

            <div>
                <strong>Biblioteca Digital</strong>
                <small>Área estudiantil</small>
            </div>

        </a>

        <nav class="student-nav">

            <a
                href="catalogo.php"
                class="<?php echo in_array(
                    $paginaActual,
                    ['catalogo.php', 'libro_detalle.php'],
                    true
                ) ? 'active' : ''; ?>"
            >
                <span class="student-nav-icon">⌂</span>
                Inicio
            </a>

            <a
                href="mis_reservas.php"
                class="<?php echo $paginaActual === 'mis_reservas.php'
                    ? 'active'
                    : ''; ?>"
            >
                <span class="student-nav-icon">▣</span>
                Mis libros
            </a>

            <a
                href="mis_solicitudes.php"
                class="<?php echo $paginaActual === 'mis_solicitudes.php'
                    ? 'active'
                    : ''; ?>"
            >
                <span class="student-nav-icon">＋</span>
                Mis solicitudes
            </a>

        </nav>
    </div>

    <div class="student-sidebar-footer">

        <span class="student-sidebar-user">
            <?php echo htmlspecialchars(
                $_SESSION['usuario'] ?? 'Estudiante',
                ENT_QUOTES,
                'UTF-8'
            ); ?>
        </span>

        <a href="logout.php" class="student-logout">
            <span>↪</span>
            Cerrar sesión
        </a>

    </div>

</aside>