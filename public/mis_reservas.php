<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION["rol"] ?? "") !== "estudiante") {
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
require_once __DIR__ . '/../app/Models/ReservaModel.php';

NoCache::aplicar();

/**
 * Escapa contenido antes de mostrarlo.
 */
function escaparReserva(?string $valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

/**
 * Formatea una fecha de base de datos.
 */
function formatearFechaReserva(?string $fecha): string
{
    if ($fecha === null || trim($fecha) === "") {
        return "Sin fecha límite";
    }

    try {
        $fechaObjeto = new DateTime($fecha);

        return $fechaObjeto->format("d/m/Y");
    } catch (Throwable $e) {
        return escaparReserva($fecha);
    }
}

$modelo = new ReservaModel();

$reservas = $modelo->obtenerPorUsuario(
    (int)$_SESSION["usuario_id"]
);

/*
|--------------------------------------------------------------------------
| Nombres visibles de los estados
|--------------------------------------------------------------------------
| La base de datos conserva "en_prestamo", pero al estudiante
| le mostramos "Leyendo".
*/
$estados = [
    "reservado" => "Reservado",
    "en_prestamo" => "Leyendo",
    "por_vencer" => "Por vencer",
    "devuelto" => "Finalizado"
];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Mis libros - Biblioteca Digital
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/student.css?v=3"
    >

</head>

<body class="student-body">

<div class="student-layout">

    <?php include __DIR__ . '/menu_estudiante.php'; ?>

    <main class="student-main">

        <!-- Encabezado -->

        <section class="student-page-header">

            <div>

                <span class="student-eyebrow">
                    Área estudiantil
                </span>

                <h1>Mis libros</h1>

                <p>
                    Consulta los libros que tienes disponibles
                    y continúa con tus lecturas.
                </p>

            </div>

            <a
                href="catalogo.php"
                class="student-primary-button"
            >
                Explorar catálogo
            </a>

        </section>

        <?php if (empty($reservas)): ?>

            <!-- Estado vacío -->

            <section class="student-empty-state reservation-empty">

                <div class="empty-icon">
                    ▣
                </div>

                <h2>Aún no tienes libros</h2>

                <p>
                    Cuando abras un libro gratuito o reserves uno,
                    aparecerá en esta sección.
                </p>

                <a
                    href="catalogo.php"
                    class="student-primary-button"
                >
                    Explorar catálogo
                </a>

            </section>

        <?php else: ?>

            <!-- Lista de libros -->

            <section class="reservation-list">

                <?php foreach ($reservas as $reserva): ?>

                    <?php

                    $estado = $reserva["estado"]
                        ?? "en_prestamo";

                    $nombreEstado = $estados[$estado]
                        ?? ucfirst(
                            str_replace("_", " ", $estado)
                        );

                    $tituloBoton = in_array(
                        $estado,
                        ["en_prestamo", "por_vencer"],
                        true
                    )
                        ? "Continuar leyendo"
                        : "Ver libro";

                    ?>

                    <article class="reservation-card">

                        <!-- Portada -->

                        <a
                            href="libro_detalle.php?id=<?php
                            echo (int)$reserva["libro_id"];
                            ?>"
                            class="reservation-cover"
                        >

                            <?php if (!empty($reserva["thumbnail"])): ?>

                                <img
                                    src="../uploads/thumbnails/<?php
                                    echo rawurlencode(
                                        basename(
                                            str_replace(
                                                "\\",
                                                "/",
                                                $reserva["thumbnail"]
                                            )
                                        )
                                    );
                                    ?>"
                                    alt="Portada de <?php
                                    echo escaparReserva(
                                        $reserva["titulo"]
                                    );
                                    ?>"
                                >

                            <?php elseif (!empty($reserva["imagen"])): ?>

                                <img
                                    src="../uploads/libros/<?php
                                    echo rawurlencode(
                                        basename(
                                            str_replace(
                                                "\\",
                                                "/",
                                                $reserva["imagen"]
                                            )
                                        )
                                    );
                                    ?>"
                                    alt="Portada de <?php
                                    echo escaparReserva(
                                        $reserva["titulo"]
                                    );
                                    ?>"
                                >

                            <?php else: ?>

                                <div class="reservation-placeholder">
                                    LIBRO
                                </div>

                            <?php endif; ?>

                        </a>

                        <!-- Información -->

                        <div class="reservation-information">

                            <span class="book-category">

                                <?php echo escaparReserva(
                                    $reserva["categoria_nombre"]
                                    ?? "Sin categoría"
                                ); ?>

                            </span>

                            <h2>

                                <a
                                    href="libro_detalle.php?id=<?php
                                    echo (int)$reserva["libro_id"];
                                    ?>"
                                >
                                    <?php echo escaparReserva(
                                        $reserva["titulo"]
                                    ); ?>
                                </a>

                            </h2>

                            <p class="reservation-author">

                                <?php echo escaparReserva(
                                    $reserva["autor"]
                                    ?? "Autor no especificado"
                                ); ?>

                            </p>

                            <div class="reservation-dates">

                                <div>

                                    <span>Agregado el</span>

                                    <strong>

                                        <?php echo formatearFechaReserva(
                                            $reserva["fecha_reserva"]
                                            ?? null
                                        ); ?>

                                    </strong>

                                </div>

                                <div>

                                    <span>Acceso disponible hasta</span>

                                    <strong>

                                        <?php echo formatearFechaReserva(
                                            $reserva["fecha_vencimiento"]
                                            ?? null
                                        ); ?>

                                    </strong>

                                </div>

                                <?php if (
                                    !empty(
                                        $reserva["fecha_devolucion"]
                                    )
                                ): ?>

                                    <div>

                                        <span>Finalizado el</span>

                                        <strong>

                                            <?php echo formatearFechaReserva(
                                                $reserva["fecha_devolucion"]
                                            ); ?>

                                        </strong>

                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>

                        <!-- Estado y acción -->

                        <div class="reservation-actions">

                            <span
                                class="reservation-status <?php
                                echo escaparReserva($estado);
                                ?>"
                            >
                                <?php echo escaparReserva(
                                    $nombreEstado
                                ); ?>
                            </span>

                            <a
                                href="libro_detalle.php?id=<?php
                                echo (int)$reserva["libro_id"];
                                ?>"
                                class="reservation-button"
                            >
                                <?php echo escaparReserva(
                                    $tituloBoton
                                ); ?>
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </section>

        <?php endif; ?>

    </main>

</div>

</body>

</html>