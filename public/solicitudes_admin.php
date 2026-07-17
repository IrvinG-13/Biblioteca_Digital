<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../app/Core/NoCache.php';
require_once __DIR__ . '/../app/Core/Csrf.php';
require_once __DIR__ . '/../app/Controllers/SolicitudController.php';

NoCache::aplicar();

$controller = new SolicitudController();
$datos = $controller->listarAdmin();

$solicitudes = $datos['solicitudes'];
$categorias = $datos['categorias'];
$estados = $datos['estados'];

$categoriaActual = $datos['categoriaActual'];
$estadoActual = $datos['estadoActual'];

$paginaActual = $datos['paginaActual'];
$totalPaginas = $datos['totalPaginas'];
$totalSolicitudes = $datos['totalSolicitudes'];

$token = Csrf::generarToken();

$exito = $_GET['exito'] ?? '';
$error = $_GET['error'] ?? '';

$mensajesError = [
    'no_encontrada' =>
        'La solicitud seleccionada no existe.',

    'estado' =>
        'El estado seleccionado no es válido.',

    'observacion' =>
        'La observación no puede superar los 1000 caracteres.',

    'guardar' =>
        'No fue posible actualizar la solicitud.'
];

$esc = static function ($valor): string {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

$nombreCompleto = static function (
    array $solicitud
): string {
    $partes = [
        $solicitud['primer_nombre'] ?? '',
        $solicitud['segundo_nombre'] ?? '',
        $solicitud['primer_apellido'] ?? '',
        $solicitud['segundo_apellido'] ?? ''
    ];

    $partes = array_filter(
        $partes,
        static fn ($parte) =>
            trim((string)$parte) !== ''
    );

    $nombre = implode(' ', $partes);

    return $nombre !== ''
        ? $nombre
        : 'Estudiante no identificado';
};

$formatearFecha = static function (
    ?string $fecha
): string {
    if ($fecha === null || trim($fecha) === '') {
        return 'No registrada';
    }

    try {
        return (new DateTime($fecha))
            ->format('d/m/Y H:i');
    } catch (Throwable $e) {
        return $fecha;
    }
};

$textoEstadoSolicitud = static function (
    string $estado
): string {
    return match ($estado) {
        'aprobada' => 'Aprobada',
        'rechazada' => 'Rechazada',
        default => 'Pendiente'
    };
};

$textoFiltroEstado = static function (
    string $estado
): string {
    return match ($estado) {
        'respondidas' => 'Respondidas',
        'aprobada' => 'Aprobadas',
        'rechazada' => 'Rechazadas',
        'todas' => 'Todas',
        default => 'Pendientes'
    };
};

$construirUrlPagina = static function (
    int $pagina,
    string $categoria,
    string $estado
): string {
    $parametros = [
        'pagina' => $pagina,
        'estado' => $estado
    ];

    if ($categoria !== '') {
        $parametros['categoria'] = $categoria;
    }

    return 'solicitudes_admin.php?'
        . http_build_query($parametros);
};

$mensajeVacio = match ($estadoActual) {
    'pendiente' =>
        'No tienes solicitudes pendientes por responder.',

    'respondidas' =>
        'Todavía no hay solicitudes respondidas.',

    'aprobada' =>
        'No se encontraron solicitudes aprobadas.',

    'rechazada' =>
        'No se encontraron solicitudes rechazadas.',

    default =>
        'No se encontraron solicitudes con esos filtros.'
};

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
        Solicitudes de libros - Administración
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css?v=solicitudes-8"
    >

</head>

<body>

<div class="app-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="main-content">

        <div class="content-card">

            <div class="page-header admin-requests-header">

                <div>

                    <span class="admin-request-eyebrow">
                        Bandeja administrativa
                    </span>

                    <h2>Solicitudes de libros</h2>

                    <p>
                        Revisa las solicitudes de los estudiantes,
                        apruébalas o recházalas y registra una respuesta.
                    </p>

                </div>

                <div class="admin-request-counter">

                    <strong>
                        <?php echo (int)$totalSolicitudes; ?>
                    </strong>

                    <span>
                        resultado<?php echo $totalSolicitudes === 1
                            ? ''
                            : 's'; ?>
                    </span>

                </div>

            </div>

            <?php if ($exito === '1'): ?>

                <div class="alert alert-success">

                    La solicitud fue actualizada correctamente.
                    Las solicitudes respondidas dejan de aparecer
                    cuando el filtro está en Pendientes.

                </div>

            <?php elseif (isset($mensajesError[$error])): ?>

                <div class="alert alert-error">

                    <?php echo $esc(
                        $mensajesError[$error]
                    ); ?>

                </div>

            <?php endif; ?>

            <!-- Los selectores aplican el filtro automáticamente. -->

            <form
                action="solicitudes_admin.php"
                method="GET"
                class="admin-request-filters"
                id="admin-request-filters"
            >

                <div class="form-group">

                    <label for="categoria">
                        Categoría
                    </label>

                    <select
                        id="categoria"
                        name="categoria"
                    >

                        <option value="">
                            Todas las categorías
                        </option>

                        <?php foreach ($categorias as $categoria): ?>

                            <option
                                value="<?php echo $esc($categoria); ?>"
                                <?php echo $categoriaActual === $categoria
                                    ? 'selected'
                                    : ''; ?>
                            >
                                <?php echo $esc($categoria); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="form-group">

                    <label for="estado">
                        Estado
                    </label>

                    <select
                        id="estado"
                        name="estado"
                    >

                        <?php foreach ($estados as $estado): ?>

                            <option
                                value="<?php echo $esc($estado); ?>"
                                <?php echo $estadoActual === $estado
                                    ? 'selected'
                                    : ''; ?>
                            >
                                <?php echo $esc(
                                    $textoFiltroEstado($estado)
                                ); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <a
                    href="solicitudes_admin.php"
                    class="admin-request-reset"
                >
                    Restablecer filtros
                </a>

                <noscript>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Aplicar filtros
                    </button>

                </noscript>

            </form>

            <?php if (empty($solicitudes)): ?>

                <section class="admin-request-empty">

                    <div class="admin-request-empty-icon">
                        ✓
                    </div>

                    <h3>No hay solicitudes</h3>

                    <p>
                        <?php echo $esc($mensajeVacio); ?>
                    </p>

                </section>

            <?php else: ?>

                <section class="admin-request-list">

                    <?php foreach ($solicitudes as $solicitud): ?>

                        <?php

                        $estadoSolicitud =
                            $solicitud['estado']
                            ?? 'pendiente';

                        $observacionActual = trim(
                            (string)(
                                $solicitud[
                                    'observacion_admin'
                                ] ?? ''
                            )
                        );

                        ?>

                        <article class="admin-request-card">

                            <header class="admin-request-card-header">

                                <div>

                                    <span class="admin-request-id">

                                        Solicitud #

                                        <?php echo (int)(
                                            $solicitud['id']
                                        ); ?>

                                    </span>

                                    <h3>

                                        <?php echo $esc(
                                            $solicitud[
                                                'titulo_solicitado'
                                            ] ?? ''
                                        ); ?>

                                    </h3>

                                    <div class="admin-request-tags">

                                        <span class="admin-request-category">

                                            <?php echo $esc(
                                                $solicitud['area']
                                                ?? 'Sin categoría'
                                            ); ?>

                                        </span>

                                        <span
                                            class="admin-request-status <?php
                                            echo $esc(
                                                $estadoSolicitud
                                            );
                                            ?>"
                                        >

                                            <?php echo $esc(
                                                $textoEstadoSolicitud(
                                                    $estadoSolicitud
                                                )
                                            ); ?>

                                        </span>

                                    </div>

                                </div>

                                <div class="admin-request-date">

                                    <span>Enviada el</span>

                                    <strong>

                                        <?php echo $esc(
                                            $formatearFecha(
                                                $solicitud['fecha']
                                                ?? null
                                            )
                                        ); ?>

                                    </strong>

                                </div>

                            </header>

                            <div class="admin-request-card-body">

                                <section class="admin-request-information">

                                    <div class="admin-request-student">

                                        <h4>Estudiante</h4>

                                        <strong>

                                            <?php echo $esc(
                                                $nombreCompleto(
                                                    $solicitud
                                                )
                                            ); ?>

                                        </strong>

                                        <p>

                                            <span>CIP:</span>

                                            <?php echo $esc(
                                                $solicitud['cip']
                                                ?? 'No registrado'
                                            ); ?>

                                        </p>

                                        <p>

                                            <span>Carrera:</span>

                                            <?php echo $esc(
                                                $solicitud[
                                                    'carrera_nombre'
                                                ]
                                                ?? 'No especificada'
                                            ); ?>

                                        </p>

                                    </div>

                                    <div class="admin-request-comment">

                                        <h4>
                                            Motivo de la solicitud
                                        </h4>

                                        <?php if (
                                            !empty(
                                                $solicitud['comentario']
                                            )
                                        ): ?>

                                            <p>

                                                <?php echo nl2br(
                                                    $esc(
                                                        $solicitud[
                                                            'comentario'
                                                        ]
                                                    )
                                                ); ?>

                                            </p>

                                        <?php else: ?>

                                            <p class="admin-muted-text">

                                                El estudiante no agregó
                                                un motivo adicional.

                                            </p>

                                        <?php endif; ?>

                                    </div>

                                    <?php if (
                                        !empty(
                                            $solicitud[
                                                'fecha_respuesta'
                                            ]
                                        )
                                    ): ?>

                                        <div class="admin-request-last-response">

                                            <h4>
                                                Última respuesta
                                            </h4>

                                            <p>

                                                Atendida por:

                                                <strong>

                                                    <?php echo $esc(
                                                        $solicitud[
                                                            'gestor_usuario'
                                                        ]
                                                        ?? 'Administrador'
                                                    ); ?>

                                                </strong>

                                            </p>

                                            <p>

                                                Fecha:

                                                <strong>

                                                    <?php echo $esc(
                                                        $formatearFecha(
                                                            $solicitud[
                                                                'fecha_respuesta'
                                                            ]
                                                        )
                                                    ); ?>

                                                </strong>

                                            </p>

                                        </div>

                                    <?php endif; ?>

                                </section>

                                <form
                                    action="solicitud_estado_procesar.php"
                                    method="POST"
                                    class="admin-request-response-form"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php echo $esc(
                                            $token
                                        ); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?php echo (int)(
                                            $solicitud['id']
                                        ); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="vista_actual"
                                        value="<?php echo $esc(
                                            $estadoActual
                                        ); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="categoria_actual"
                                        value="<?php echo $esc(
                                            $categoriaActual
                                        ); ?>"
                                    >

                                    <div class="form-group">

                                        <label
                                            for="estado-<?php
                                            echo (int)$solicitud['id'];
                                            ?>"
                                        >
                                            Estado de la solicitud
                                        </label>

                                        <select
                                            id="estado-<?php
                                            echo (int)$solicitud['id'];
                                            ?>"
                                            name="estado"
                                            required
                                        >

                                            <option
                                                value="pendiente"
                                                <?php echo $estadoSolicitud
                                                    === 'pendiente'
                                                    ? 'selected'
                                                    : ''; ?>
                                            >
                                                Pendiente
                                            </option>

                                            <option
                                                value="aprobada"
                                                <?php echo $estadoSolicitud
                                                    === 'aprobada'
                                                    ? 'selected'
                                                    : ''; ?>
                                            >
                                                Aprobada
                                            </option>

                                            <option
                                                value="rechazada"
                                                <?php echo $estadoSolicitud
                                                    === 'rechazada'
                                                    ? 'selected'
                                                    : ''; ?>
                                            >
                                                Rechazada
                                            </option>

                                        </select>

                                    </div>

                                    <div class="form-group">

                                        <label
                                            for="observacion-<?php
                                            echo (int)$solicitud['id'];
                                            ?>"
                                        >
                                            Respuesta para el estudiante
                                        </label>

                                        <textarea
                                            id="observacion-<?php
                                            echo (int)$solicitud['id'];
                                            ?>"
                                            name="observacion_admin"
                                            rows="5"
                                            maxlength="1000"
                                            placeholder="Ej. La solicitud fue aprobada y el libro será incorporado próximamente."
                                        ><?php echo $esc(
                                            $observacionActual
                                        ); ?></textarea>

                                        <small>
                                            La respuesta aparecerá en
                                            “Mis solicitudes” del estudiante.
                                        </small>

                                    </div>

                                    <button
                                        type="submit"
                                        class="btn btn-primary admin-request-save"
                                    >
                                        Guardar respuesta
                                    </button>

                                </form>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </section>

                <?php if ($totalPaginas > 1): ?>

                    <nav class="admin-request-pagination">

                        <?php if ($paginaActual > 1): ?>

                            <a
                                href="<?php echo $esc(
                                    $construirUrlPagina(
                                        $paginaActual - 1,
                                        $categoriaActual,
                                        $estadoActual
                                    )
                                ); ?>"
                            >
                                ← Anterior
                            </a>

                        <?php endif; ?>

                        <?php for (
                            $pagina = 1;
                            $pagina <= $totalPaginas;
                            $pagina++
                        ): ?>

                            <a
                                href="<?php echo $esc(
                                    $construirUrlPagina(
                                        $pagina,
                                        $categoriaActual,
                                        $estadoActual
                                    )
                                ); ?>"
                                class="<?php echo $pagina
                                    === $paginaActual
                                    ? 'active'
                                    : ''; ?>"
                            >
                                <?php echo $pagina; ?>
                            </a>

                        <?php endfor; ?>

                        <?php if (
                            $paginaActual < $totalPaginas
                        ): ?>

                            <a
                                href="<?php echo $esc(
                                    $construirUrlPagina(
                                        $paginaActual + 1,
                                        $categoriaActual,
                                        $estadoActual
                                    )
                                ); ?>"
                            >
                                Siguiente →
                            </a>

                        <?php endif; ?>

                    </nav>

                <?php endif; ?>

            <?php endif; ?>

        </div>

    </main>

</div>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const formulario =
            document.getElementById(
                'admin-request-filters'
            );

        const categoria =
            document.getElementById('categoria');

        const estado =
            document.getElementById('estado');

        [categoria, estado].forEach(
            function (selector) {
                selector.addEventListener(
                    'change',
                    function () {
                        formulario.submit();
                    }
                );
            }
        );
    }
);

</script>

</body>

</html>