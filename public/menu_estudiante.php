<?php

$paginaActual = basename(
    $_SERVER["PHP_SELF"]
);

$nombreUsuario = htmlspecialchars(
    $_SESSION["usuario"] ?? "Usuario",
    ENT_QUOTES,
    "UTF-8"
);

$rolUsuario = ($_SESSION["rol"] ?? "") === "profesor"
    ? "Profesor"
    : "Estudiante";

?>

<aside class="student-sidebar">

    <div class="student-sidebar-top">

        <a
            href="catalogo.php"
            class="student-brand"
        >

            <span
                class="student-brand-icon"
                aria-hidden="true"
            >
                RP
            </span>

            <div class="student-brand-text">

                <strong>ReadPoint</strong>

                <small>
                    Biblioteca digital
                </small>

            </div>

        </a>

        <nav
            class="student-nav"
            aria-label="Navegación principal"
        >

            <a
                href="catalogo.php"
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
            >

                <span
                    class="student-nav-icon"
                    aria-hidden="true"
                >
                    ⌂
                </span>

                <span>Catálogo</span>

            </a>

            <a
                href="mis_reservas.php"
                class="<?php echo $paginaActual
                    === "mis_reservas.php"
                    ? "active"
                    : ""; ?>"
            >

                <span
                    class="student-nav-icon"
                    aria-hidden="true"
                >
                    ▣
                </span>

                <span>Mis libros</span>

            </a>

            <a
                href="mis_facturas.php"
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
            >

                <span
                    class="student-nav-icon"
                    aria-hidden="true"
                >
                    ▤
                </span>

                <span>Mis facturas</span>

            </a>

            <a
                href="mis_solicitudes.php"
                class="<?php echo $paginaActual
                    === "mis_solicitudes.php"
                    ? "active"
                    : ""; ?>"
            >

                <span
                    class="student-nav-icon"
                    aria-hidden="true"
                >
                    ＋
                </span>

                <span>Mis solicitudes</span>

            </a>

            <a
                href="perfil.php"
                class="<?php echo $paginaActual
                    === "perfil.php"
                    ? "active"
                    : ""; ?>"
            >

                <span
                    class="student-nav-icon"
                    aria-hidden="true"
                >
                    ◎
                </span>

                <span>Mi perfil</span>

            </a>

        </nav>

    </div>

    <div class="student-sidebar-footer">

        <div class="student-sidebar-profile">

            <span
                class="student-profile-avatar"
                aria-hidden="true"
            >
                <?php echo strtoupper(
                    substr($nombreUsuario, 0, 1)
                ); ?>
            </span>

            <div class="student-profile-information">

                <strong>
                    <?php echo $nombreUsuario; ?>
                </strong>

                <small>
                    <?php echo $rolUsuario; ?>
                </small>

            </div>

        </div>

        <a
            href="logout.php"
            class="student-logout"
        >

            <span aria-hidden="true">
                ↪
            </span>

            <span>Cerrar sesión</span>

        </a>

    </div>

</aside>