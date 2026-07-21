<?php

session_start();

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/SolicitudController.php';

$controller = new SolicitudController();

/*
 * También valida que el usuario tenga sesión iniciada,
 * sea estudiante o profesor y esté vinculado a su tabla.
 */
$datos = $controller->listarMisSolicitudes();

$tipoSolicitante = $datos["tipo"];
$solicitante = $datos["solicitante"];
$solicitudes = $datos["solicitudes"];

$exito = $_GET["exito"] ?? "";
$error = $_GET["error"] ?? "";

$esc = static function ($valor): string {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
};

/*
 * Construir información del solicitante.
 */
if ($tipoSolicitante === "profesor") {
    $nombreCompleto = $solicitante["nombre"] ?? "";
    $identificacion = $solicitante["cedula"] ?? "";
    $carreraOMateria =
        $solicitante["materia"] ?? "No especificada";
} else {
    $partesNombre = [
        $solicitante["primer_nombre"] ?? "",
        $solicitante["segundo_nombre"] ?? "",
        $solicitante["primer_apellido"] ?? "",
        $solicitante["segundo_apellido"] ?? ""
    ];

    $partesNombre = array_filter(
        $partesNombre,
        static fn ($parte) =>
            trim((string)$parte) !== ""
    );

    $nombreCompleto = implode(
        " ",
        $partesNombre
    );

    $identificacion =
        $solicitante["cip"] ?? "";

    $carreraOMateria =
        $solicitante["carrera_nombre"]
        ?? "No especificada";
}

/*
 * Formatear fechas.
 */
$formatearFecha = static function (
    ?string $fecha,
    bool $incluirHora = true
): string {
    if (
        $fecha === null
        || trim($fecha) === ""
    ) {
        return "No registrada";
    }

    try {
        $fechaObjeto = new DateTime($fecha);

        return $incluirHora
            ? $fechaObjeto->format("d/m/Y H:i")
            : $fechaObjeto->format("d/m/Y");
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
        Mis solicitudes - ReadPoint
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/student.css?v=8"
    >
        <link
        rel="stylesheet"
        href="assets/css/admin.css?v=7"
    >

</head>

<body class="student-body">

<div class="student-layout">

    <?php include __DIR__ . '/menu.php'; ?>

    <main class="student-main">

        <section class="student-request-page-header">

            <div>

                <span class="student-eyebrow">
                    Gestión de solicitudes
                </span>

                <h1>
                    Mis solicitudes de libros
                </h1>

                <p>
                    Consulta el estado, la fecha y la respuesta
                    de las solicitudes que has enviado.
                </p>

            </div>

            <a
                href="solicitar_libro.php"
                class="student-primary-button"
            >
                + Nueva solicitud
            </a>

        </section>

        <section class="student-request-profile">

            <div>

                <span>
                    <?php echo $tipoSolicitante === "profesor"
                        ? "Profesor"
                        : "Estudiante"; ?>
                </span>

                <strong>
                    <?php echo $esc($nombreCompleto); ?>
                </strong>

            </div>

            <div>

                <span>Cédula / CIP</span>

                <strong>
                    <?php echo $esc($identificacion); ?>
                </strong>

            </div>

            <div>

                <span>
                    <?php echo $tipoSolicitante === "profesor"
                        ? "Materia"
                        : "Carrera"; ?>
                </span>

                <strong>
                    <?php echo $esc($carreraOMateria); ?>
                </strong>

            </div>

        </section>

        <?php if ($exito === "1"): ?>

            <div class="alert alert-success">

                La solicitud fue registrada correctamente
                y se encuentra pendiente de revisión.

            </div>

        <?php elseif ($error === "cargar"): ?>

            <div class="alert alert-error">

                No fue posible cargar las solicitudes.

            </div>

        <?php endif; ?>

        <section class="student-request-summary">

            <div>

                <span>Total de solicitudes</span>

                <strong>
                    <?php echo count($solicitudes); ?>
                </strong>

            </div>

        </section>

        <?php if (empty($solicitudes)): ?>

            <section class="student-empty-state request-empty-state">

                <div class="empty-icon">
                    ＋
                </div>

                <h2>
                    Aún no tienes solicitudes
                </h2>

                <p>
                    Puedes solicitar un libro que todavía
                    no esté disponible en el catálogo.
                </p>

                <a
                    href="solicitar_libro.php"
                    class="student-primary-button"
                >
                    Realizar mi primera solicitud
                </a>

            </section>

        <?php else: ?>

            <section class="request-list">

                <?php foreach ($solicitudes as $solicitud): ?>

                    <?php

                    $estado =
                        $solicitud["estado"]
                        ?? "pendiente";

                    $textoEstado = match ($estado) {
                        "aprobada" => "Aprobada",
                        "rechazada" => "Rechazada",
                        default => "Pendiente"
                    };

                    $observacion = trim(
                        (string)(
                            $solicitud["observacion_admin"]
                            ?? ""
                        )
                    );

                    ?>

                    <article class="request-card">

                        <header class="request-card-header">

                            <div>

                                <span class="request-card-id">
                                    Solicitud
                                    #<?php echo (int)$solicitud["id"]; ?>
                                </span>

                                <h2>
                                    <?php echo $esc(
                                        $solicitud[
                                            "titulo_solicitado"
                                        ] ?? ""
                                    ); ?>
                                </h2>

                            </div>

                            <span
                                class="request-status <?php
                                echo $esc($estado);
                                ?>"
                            >
                                <?php echo $textoEstado; ?>
                            </span>

                        </header>

                        <div class="request-card-grid">

                            <div class="request-information-box">

                                <span>Materia o área</span>

                                <strong>
                                    <?php echo $esc(
                                        $solicitud["area"]
                                        ?? "No especificada"
                                    ); ?>
                                </strong>

                            </div>

                            <div class="request-information-box">

                                <span>Fecha de solicitud</span>

                                <strong>
                                    <?php echo $esc(
                                        $formatearFecha(
                                            $solicitud["fecha"]
                                            ?? null
                                        )
                                    ); ?>
                                </strong>

                            </div>

                            <div class="request-information-box">

                                <span>Fecha de respuesta</span>

                                <strong>
                                    <?php echo !empty(
                                        $solicitud["fecha_respuesta"]
                                    )
                                        ? $esc(
                                            $formatearFecha(
                                                $solicitud[
                                                    "fecha_respuesta"
                                                ]
                                            )
                                        )
                                        : "Pendiente"; ?>
                                </strong>

                            </div>

                        </div>

                        <div class="request-card-content">

                            <div>

                                <span class="request-section-label">
                                    Motivo de la solicitud
                                </span>

                                <p>

                                    <?php if (
                                        !empty(
                                            $solicitud["comentario"]
                                        )
                                    ): ?>

                                        <?php echo nl2br(
                                            $esc(
                                                $solicitud[
                                                    "comentario"
                                                ]
                                            )
                                        ); ?>

                                    <?php else: ?>

                                        Sin motivo adicional.

                                    <?php endif; ?>

                                </p>

                            </div>

                            <div>

                                <span class="request-section-label">
                                    Respuesta del administrador
                                </span>

                                <p>

                                    <?php if ($observacion !== ""): ?>

                                        <?php echo nl2br(
                                            $esc($observacion)
                                        ); ?>

                                    <?php elseif (
                                        $estado === "pendiente"
                                    ): ?>

                                        Aún no existe una respuesta.

                                    <?php else: ?>

                                        Sin observación registrada.

                                    <?php endif; ?>

                                </p>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </section>

        <?php endif; ?>

    </main>

</div>

</body>

</html>