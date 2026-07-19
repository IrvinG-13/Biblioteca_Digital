<?php

/*
|--------------------------------------------------------------------------
| Iniciar sesión
|--------------------------------------------------------------------------
*/
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Validar sesión
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Solo estudiantes y profesores
|--------------------------------------------------------------------------
*/
if (
    !in_array(
        $_SESSION['rol'] ?? '',
        ['estudiante', 'profesor'],
        true
    )
) {
    header('Location: dashboard.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Archivos necesarios
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../app/Core/NoCache.php';
require_once __DIR__ . '/../app/Models/ReservaModel.php';

NoCache::aplicar();

/*
|--------------------------------------------------------------------------
| Escapar contenido
|--------------------------------------------------------------------------
*/
function escaparReserva(?string $valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
|--------------------------------------------------------------------------
| Formatear fecha
|--------------------------------------------------------------------------
*/
function formatearFechaReserva(
    ?string $fecha
): string {
    if (
        $fecha === null
        || trim($fecha) === ''
    ) {
        return 'Sin fecha límite';
    }

    try {
        $fechaObjeto = new DateTime(
            $fecha
        );

        return $fechaObjeto->format(
            'd/m/Y'
        );
    } catch (Throwable $e) {
        return escaparReserva($fecha);
    }
}

/*
|--------------------------------------------------------------------------
| Obtener los libros del usuario
|--------------------------------------------------------------------------
*/
$modelo = new ReservaModel();

$reservas = $modelo->obtenerPorUsuario(
    (int)$_SESSION['usuario_id']
);

/*
|--------------------------------------------------------------------------
| Estados visibles
|--------------------------------------------------------------------------
*/
$estados = [
    'reservado' => 'Reservado',
    'en_prestamo' => 'Leyendo',
    'por_vencer' => 'Por vencer',
    'devuelto' => 'Finalizado',
    'cancelado' => 'Cancelado'
];

$fechaActual = new DateTimeImmutable(
    'today'
);

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
        href="assets/css/student.css?v=7"
    >

</head>

<body class="student-body">

<div class="student-layout">

    <?php include __DIR__ . '/menu_estudiante.php'; ?>

    <main class="student-main">

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

            <section
                class="student-empty-state reservation-empty"
            >

                <div class="empty-icon">
                    ▣
                </div>

                <h2>Aún no tienes libros</h2>

                <p>
                    Cuando abras un libro gratuito o compres
                    un acceso digital, aparecerá en esta sección.
                </p>

                <a
                    href="catalogo.php"
                    class="student-primary-button"
                >
                    Explorar catálogo
                </a>

            </section>

        <?php else: ?>

            <section class="reservation-list">

                <?php foreach ($reservas as $reserva): ?>

                    <?php

                    $estado = $reserva['estado']
                        ?? 'en_prestamo';

                    $tipoAcceso =
                        $reserva['tipo_acceso']
                        ?? 'gratuito';

                    $fechaVencimiento =
                        $reserva['fecha_vencimiento']
                        ?? null;

                    $accesoVencido = false;

                    /*
                     * Los libros de pago tienen fecha
                     * de vencimiento.
                     */
                    if (
                        $tipoAcceso === 'pago'
                        && $fechaVencimiento !== null
                        && trim($fechaVencimiento) !== ''
                    ) {
                        try {
                            $fechaLimite =
                                new DateTimeImmutable(
                                    $fechaVencimiento
                                );

                            $accesoVencido =
                                $fechaLimite < $fechaActual;
                        } catch (Throwable $e) {
                            $accesoVencido = true;
                        }
                    }

                    $estadoActivo = in_array(
                        $estado,
                        [
                            'reservado',
                            'en_prestamo',
                            'por_vencer'
                        ],
                        true
                    );

                    $puedeLeer =
                        $estadoActivo
                        && !$accesoVencido;

                    if ($accesoVencido) {
                        $nombreEstado =
                            'Acceso vencido';

                        $claseEstado =
                            'devuelto';

                        $tituloBoton =
                            'Renovar acceso';

                        $urlBoton =
                            'libro_detalle.php?id='
                            . (int)$reserva['libro_id'];
                    } elseif ($puedeLeer) {
                        $nombreEstado =
                            $estados[$estado]
                            ?? 'Disponible';

                        $claseEstado =
                            $estado;

                        $tituloBoton =
                            'Continuar leyendo';

                        $urlBoton =
                            'abrir_libro.php?id='
                            . (int)$reserva['libro_id'];
                    } else {
                        $nombreEstado =
                            $estados[$estado]
                            ?? ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $estado
                                )
                            );

                        $claseEstado =
                            $estado;

                        $tituloBoton =
                            'Ver libro';

                        $urlBoton =
                            'libro_detalle.php?id='
                            . (int)$reserva['libro_id'];
                    }

                    ?>

                    <article class="reservation-card">

                        <!-- Portada -->

                        <a
                            href="libro_detalle.php?id=<?php
                            echo (int)$reserva['libro_id'];
                            ?>"
                            class="reservation-cover"
                        >

                            <?php if (
                                !empty(
                                    $reserva['thumbnail']
                                )
                            ): ?>

                                <img
                                    src="../uploads/thumbnails/<?php
                                    echo rawurlencode(
                                        basename(
                                            str_replace(
                                                '\\',
                                                '/',
                                                $reserva[
                                                    'thumbnail'
                                                ]
                                            )
                                        )
                                    );
                                    ?>"
                                    alt="Portada de <?php
                                    echo escaparReserva(
                                        $reserva['titulo']
                                        ?? 'Libro'
                                    );
                                    ?>"
                                >

                            <?php elseif (
                                !empty(
                                    $reserva['imagen']
                                )
                            ): ?>

                                <img
                                    src="../uploads/libros/<?php
                                    echo rawurlencode(
                                        basename(
                                            str_replace(
                                                '\\',
                                                '/',
                                                $reserva[
                                                    'imagen'
                                                ]
                                            )
                                        )
                                    );
                                    ?>"
                                    alt="Portada de <?php
                                    echo escaparReserva(
                                        $reserva['titulo']
                                        ?? 'Libro'
                                    );
                                    ?>"
                                >

                            <?php else: ?>

                                <div
                                    class="reservation-placeholder"
                                >
                                    LIBRO
                                </div>

                            <?php endif; ?>

                        </a>

                        <!-- Información -->

                        <div class="reservation-information">

                            <span class="book-category">

                                <?php echo escaparReserva(
                                    $reserva[
                                        'categoria_nombre'
                                    ]
                                    ?? 'Sin categoría'
                                ); ?>

                            </span>

                            <h2>

                                <a
                                    href="libro_detalle.php?id=<?php
                                    echo (int)$reserva[
                                        'libro_id'
                                    ];
                                    ?>"
                                >
                                    <?php echo escaparReserva(
                                        $reserva['titulo']
                                        ?? 'Libro sin título'
                                    ); ?>
                                </a>

                            </h2>

                            <p class="reservation-author">

                                <?php echo escaparReserva(
                                    $reserva['autor']
                                    ?? 'Autor no especificado'
                                ); ?>

                            </p>

                            <div class="reservation-dates">

                                <div>

                                    <span>Agregado el</span>

                                    <strong>

                                        <?php echo
                                            formatearFechaReserva(
                                                $reserva[
                                                    'fecha_reserva'
                                                ]
                                                ?? null
                                            );
                                        ?>

                                    </strong>

                                </div>

                                <div>

                                    <span>
                                        Acceso disponible hasta
                                    </span>

                                    <strong>

                                        <?php echo
                                            formatearFechaReserva(
                                                $reserva[
                                                    'fecha_vencimiento'
                                                ]
                                                ?? null
                                            );
                                        ?>

                                    </strong>

                                </div>

                                <?php if (
                                    !empty(
                                        $reserva[
                                            'fecha_devolucion'
                                        ]
                                    )
                                ): ?>

                                    <div>

                                        <span>Finalizado el</span>

                                        <strong>

                                            <?php echo
                                                formatearFechaReserva(
                                                    $reserva[
                                                        'fecha_devolucion'
                                                    ]
                                                );
                                            ?>

                                        </strong>

                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>

                        <!-- Estado y acción -->

                        <div class="reservation-actions">

                            <span
                                class="reservation-status <?php
                                echo escaparReserva(
                                    $claseEstado
                                );
                                ?>"
                            >
                                <?php echo escaparReserva(
                                    $nombreEstado
                                ); ?>
                            </span>

                            <a
                                href="<?php
                                echo escaparReserva(
                                    $urlBoton
                                );
                                ?>"
                                class="reservation-button"
                                <?php if ($puedeLeer): ?>
                                    target="_blank"
                                <?php endif; ?>
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