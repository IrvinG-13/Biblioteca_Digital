<?php

session_start();

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/SolicitudController.php';

$controller = new SolicitudController();

/*
 * También valida que el usuario tenga sesión iniciada,
 * sea estudiante y esté vinculado a la tabla estudiantes.
 */
$datos = $controller->listarMisSolicitudes();
$tipoSolicitante = $datos['tipo'];
$solicitante = $datos['solicitante'];
$solicitudes = $datos['solicitudes'];

$exito = $_GET['exito'] ?? '';
$error = $_GET['error'] ?? '';

$esc = static function ($valor): string {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

/*
 * Construye el nombre completo del estudiante.
 */
if ($tipoSolicitante === 'profesor') {
    $nombreCompleto = $solicitante['nombre'] ?? '';
    $identificacion = $solicitante['cedula'] ?? '';
    $carreraOMateria = $solicitante['materia'] ?? 'No especificada';
} else {
    $partesNombre = [
        $solicitante['primer_nombre'] ?? '',
        $solicitante['segundo_nombre'] ?? '',
        $solicitante['primer_apellido'] ?? '',
        $solicitante['segundo_apellido'] ?? ''
    ];
    $partesNombre = array_filter(
        $partesNombre,
        static fn ($parte) => trim((string)$parte) !== ''
    );
    $nombreCompleto = implode(' ', $partesNombre);
    $identificacion = $solicitante['cip'] ?? '';
    $carreraOMateria = $solicitante['carrera_nombre'] ?? 'No especificada';
}

/*
 * Formatea las fechas para mostrarlas de manera más clara.
 */
$formatearFecha = static function (
    ?string $fecha,
    bool $incluirHora = true
): string {
    if ($fecha === null || trim($fecha) === '') {
        return 'No registrada';
    }

    try {
        $fechaObjeto = new DateTime($fecha);

        return $incluirHora
            ? $fechaObjeto->format('d/m/Y H:i')
            : $fechaObjeto->format('d/m/Y');
    } catch (Throwable $e) {
        return $fecha;
    }
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
        Mis Solicitudes - Biblioteca Digital
    </title>
        <link
    rel="stylesheet"
    href="assets/css/style.css"
>

<link
    rel="stylesheet"
    href="assets/css/student.css?v=1"
>
</head>

</head>

<body>

<div class="app-layout">

    <main
        class="main-content"
        style="margin-left: 0; width: 100%;"
    >

        <div class="content-card">

            <!-- Encabezado -->

            <div class="page-header">

                <div>

                    <h2>
                        Mis solicitudes de libros
                    </h2>

                    <p>
                        Consulta el estado y la respuesta de las
                        solicitudes que has enviado.
                    </p>

                </div>

                <div>

                    <a
                        class="btn btn-secondary"
                        href="catalogo.php"
                    >
                        Inicio
                    </a>

                    <a
                        class="btn btn-primary"
                        href="solicitar_libro.php"
                    >
                        + Nueva solicitud
                    </a>

                    <a
                        class="btn btn-danger"
                        href="logout.php"
                    >
                        Cerrar sesión
                    </a>

                </div>

            </div>

            <!-- Información del estudiante -->

            <div class="alert alert-success">

                <strong><?php echo $tipoSolicitante === 'profesor' ? 'Profesor' : 'Estudiante'; ?>:</strong>
                
                <?php echo $esc($nombreCompleto); ?>
                
                <br>
                
                <strong>Cédula / CIP:</strong>
                
                <?php echo $esc($identificacion); ?>
                
                <br>
                
                <strong><?php echo $tipoSolicitante === 'profesor' ? 'Materia' : 'Carrera'; ?>:</strong>
                
                <?php echo $esc($carreraOMateria); ?>
            
            </div>

            <!-- Mensajes -->

            <?php if ($exito === '1'): ?>

                <div class="alert alert-success">

                    La solicitud fue registrada correctamente
                    y se encuentra pendiente de revisión.

                </div>

            <?php elseif ($error === 'cargar'): ?>

                <div class="alert alert-error">

                    No fue posible cargar las solicitudes.

                </div>

            <?php endif; ?>

            <!-- Resumen -->

            <div class="actions-bar">

                <strong>

                    Total de solicitudes:

                    <?php echo count($solicitudes); ?>

                </strong>

            </div>

            <!-- Tabla -->

            <div style="overflow-x: auto;">

                <table class="table">

                    <thead>

                    <tr>

                        <th>ID</th>

                        <th>Libro solicitado</th>

                        <th>Materia o área</th>

                        <th>Motivo</th>

                        <th>Estado</th>

                        <th>Fecha de solicitud</th>

                        <th>Respuesta del administrador</th>

                        <th>Fecha de respuesta</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php if (empty($solicitudes)): ?>

                        <tr>

                            <td colspan="8">

                                Todavía no has realizado solicitudes
                                de libros.

                                <br><br>

                                <a
                                    class="btn btn-primary"
                                    href="solicitar_libro.php"
                                >
                                    Realizar mi primera solicitud
                                </a>

                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($solicitudes as $solicitud): ?>

                            <?php

                            $estado =
                                $solicitud['estado']
                                ?? 'pendiente';

                            $textoEstado = match ($estado) {
                                'aprobada' => 'Aprobada',
                                'rechazada' => 'Rechazada',
                                default => 'Pendiente'
                            };

                            $claseEstado = match ($estado) {
                                'aprobada' => 'badge-blue',
                                'rechazada' => 'badge-yellow',
                                default => 'badge-yellow'
                            };

                            $observacion =
                                trim(
                                    (string)(
                                        $solicitud[
                                            'observacion_admin'
                                        ] ?? ''
                                    )
                                );

                            ?>

                            <tr>

                                <!-- ID -->

                                <td>

                                    <?php echo (int)$solicitud['id']; ?>

                                </td>

                                <!-- Título -->

                                <td>

                                    <strong>

                                        <?php echo $esc(
                                            $solicitud[
                                                'titulo_solicitado'
                                            ] ?? ''
                                        ); ?>

                                    </strong>

                                </td>

                                <!-- Área -->

                                <td>

                                    <span class="badge badge-blue">

                                        <?php echo $esc(
                                            $solicitud['area'] ?? ''
                                        ); ?>

                                    </span>

                                </td>

                                <!-- Motivo -->

                                <td>

                                    <?php if (
                                        !empty(
                                            $solicitud['comentario']
                                        )
                                    ): ?>

                                        <?php echo nl2br(
                                            $esc(
                                                $solicitud[
                                                    'comentario'
                                                ]
                                            )
                                        ); ?>

                                    <?php else: ?>

                                        <small>
                                            Sin motivo adicional.
                                        </small>

                                    <?php endif; ?>

                                </td>

                                <!-- Estado -->

                                <td>

                                    <span
                                        class="badge <?php
                                        echo $claseEstado;
                                        ?>"
                                    >

                                        <?php echo $textoEstado; ?>

                                    </span>

                                    <?php if (
                                        $estado === 'pendiente'
                                    ): ?>

                                        <br>

                                        <small>
                                            Esperando revisión.
                                        </small>

                                    <?php elseif (
                                        $estado === 'aprobada'
                                    ): ?>

                                        <br>

                                        <small>
                                            La solicitud fue aceptada.
                                        </small>

                                    <?php elseif (
                                        $estado === 'rechazada'
                                    ): ?>

                                        <br>

                                        <small>
                                            La solicitud no fue aceptada.
                                        </small>

                                    <?php endif; ?>

                                </td>

                                <!-- Fecha de solicitud -->

                                <td>

                                    <?php echo $esc(
                                        $formatearFecha(
                                            $solicitud['fecha']
                                            ?? null
                                        )
                                    ); ?>

                                </td>

                                <!-- Observación -->

                                <td>

                                    <?php if (
                                        $observacion !== ''
                                    ): ?>

                                        <?php echo nl2br(
                                            $esc($observacion)
                                        ); ?>

                                    <?php elseif (
                                        $estado === 'pendiente'
                                    ): ?>

                                        <small>
                                            Aún no existe una respuesta.
                                        </small>

                                    <?php else: ?>

                                        <small>
                                            Sin observación.
                                        </small>

                                    <?php endif; ?>

                                </td>

                                <!-- Fecha de respuesta -->

                                <td>

                                    <?php if (
                                        !empty(
                                            $solicitud[
                                                'fecha_respuesta'
                                            ]
                                        )
                                    ): ?>

                                        <?php echo $esc(
                                            $formatearFecha(
                                                $solicitud[
                                                    'fecha_respuesta'
                                                ]
                                            )
                                        ); ?>

                                    <?php else: ?>

                                        <small>
                                            Pendiente
                                        </small>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>

</body>
</html>