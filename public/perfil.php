<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

NoCache::aplicar();

$token = Csrf::generarToken();

$exito = trim(
    (string)($_GET["exito"] ?? "")
);

$error = trim(
    (string)($_GET["error"] ?? "")
);

$rol = (string)(
    $_SESSION["rol"] ?? ""
);

$forzado =
    (int)($_SESSION["cambio_password"] ?? 0) === 1;

$esAdmin = $rol === "admin";

$esEstudianteOProfesor = in_array(
    $rol,
    ["estudiante", "profesor"],
    true
);

$rutaCancelar = $esAdmin
    ? "dashboard.php"
    : "catalogo.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mi cuenta - ReadPoint</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/admin.css?v=4"
    >

    <?php if ($esEstudianteOProfesor): ?>

        <link
            rel="stylesheet"
            href="assets/css/student.css?v=11"
        >

    <?php endif; ?>

    <link
        rel="stylesheet"
        href="assets/css/perfil.css?v=3"
    >

</head>

<body class="<?php
echo $esEstudianteOProfesor && !$forzado
    ? "student-body"
    : "";
?>">

<?php if ($forzado): ?>

    <!-- Cambio obligatorio sin sidebar -->

    <div class="profile-forced-layout">

        <main class="profile-forced-main">

            <div class="content-card">

                <div class="page-header">

                    <h2>Crear nueva contraseña</h2>

                </div>

                <div class="alert alert-error">

                    Tu contraseña fue asignada por el administrador.
                    Debes crear una nueva antes de continuar.

                </div>

                <?php if ($exito === "1"): ?>

                    <div class="alert alert-success">

                        Tu contraseña fue actualizada correctamente.
                        Ya puedes continuar usando el sistema.

                    </div>

                    <div class="form-actions">

                        <a
                            class="btn btn-primary boton-principal-readpoint"
                            href="<?php echo htmlspecialchars(
                                $rutaCancelar,
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                        >
                            Continuar
                        </a>

                    </div>

                <?php else: ?>

                    <?php if ($error === "actual"): ?>

                        <div class="alert alert-error">
                            La contraseña actual no es correcta.
                        </div>

                    <?php elseif ($error === "formato"): ?>

                        <div class="alert alert-error">
                            La nueva contraseña debe tener entre
                            8 y 12 caracteres.
                        </div>

                    <?php elseif ($error === "coincidencia"): ?>

                        <div class="alert alert-error">
                            La nueva contraseña y su confirmación
                            no coinciden.
                        </div>

                    <?php elseif ($error === "igual"): ?>

                        <div class="alert alert-error">
                            La nueva contraseña no puede ser igual
                            a la actual.
                        </div>

                    <?php elseif ($error === "guardar"): ?>

                        <div class="alert alert-error">
                            No fue posible actualizar la contraseña.
                            Inténtalo nuevamente.
                        </div>

                    <?php endif; ?>

                    <form
                        class="form-card"
                        action="perfil_procesar.php"
                        method="POST"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo htmlspecialchars(
                                $token,
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                        >

                        <div class="form-group">

                            <label for="password_actual">
                                Contraseña actual
                            </label>

                            <input
                                id="password_actual"
                                type="password"
                                name="password_actual"
                                autocomplete="current-password"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="password_nueva">
                                Nueva contraseña
                            </label>

                            <input
                                id="password_nueva"
                                type="password"
                                name="password_nueva"
                                minlength="8"
                                maxlength="12"
                                autocomplete="new-password"
                                required
                            >

                            <small>
                                Debe tener entre 8 y 12 caracteres.
                            </small>

                        </div>

                        <div class="form-group">

                            <label for="password_confirmar">
                                Confirmar nueva contraseña
                            </label>

                            <input
                                id="password_confirmar"
                                type="password"
                                name="password_confirmar"
                                minlength="8"
                                maxlength="12"
                                autocomplete="new-password"
                                required
                            >

                        </div>

                        <div class="form-actions">

                            <button
                                class="btn btn-primary boton-principal-readpoint"
                                type="submit"
                            >
                                Guardar nueva contraseña
                            </button>

                        </div>

                    </form>

                <?php endif; ?>

            </div>

        </main>

    </div>

<?php elseif ($esEstudianteOProfesor): ?>

    <!-- Área de estudiante y profesor -->

    <div class="student-layout">

        <?php include __DIR__ . '/menu.php'; ?>

        <main class="student-main">

            <div class="content-card">

                <div class="page-header">

                    <h2>Mi cuenta</h2>

                </div>

                <?php if ($exito === "1"): ?>

                    <div class="alert alert-success">
                        Tu contraseña fue actualizada correctamente.
                    </div>

                <?php elseif ($error === "actual"): ?>

                    <div class="alert alert-error">
                        La contraseña actual no es correcta.
                    </div>

                <?php elseif ($error === "formato"): ?>

                    <div class="alert alert-error">
                        La nueva contraseña debe tener entre
                        8 y 12 caracteres.
                    </div>

                <?php elseif ($error === "coincidencia"): ?>

                    <div class="alert alert-error">
                        La nueva contraseña y su confirmación
                        no coinciden.
                    </div>

                <?php elseif ($error === "igual"): ?>

                    <div class="alert alert-error">
                        La nueva contraseña no puede ser igual
                        a la actual.
                    </div>

                <?php elseif ($error === "guardar"): ?>

                    <div class="alert alert-error">
                        No fue posible actualizar la contraseña.
                        Inténtalo nuevamente.
                    </div>

                <?php endif; ?>

                <?php if ($exito !== "1"): ?>

                    <form
                        class="form-card"
                        action="perfil_procesar.php"
                        method="POST"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo htmlspecialchars(
                                $token,
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                        >

                        <div class="form-group">

                            <label for="password_actual">
                                Contraseña actual
                            </label>

                            <input
                                id="password_actual"
                                type="password"
                                name="password_actual"
                                autocomplete="current-password"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="password_nueva">
                                Nueva contraseña
                            </label>

                            <input
                                id="password_nueva"
                                type="password"
                                name="password_nueva"
                                minlength="8"
                                maxlength="12"
                                autocomplete="new-password"
                                required
                            >

                            <small>
                                Debe tener entre 8 y 12 caracteres.
                            </small>

                        </div>

                        <div class="form-group">

                            <label for="password_confirmar">
                                Confirmar nueva contraseña
                            </label>

                            <input
                                id="password_confirmar"
                                type="password"
                                name="password_confirmar"
                                minlength="8"
                                maxlength="12"
                                autocomplete="new-password"
                                required
                            >

                        </div>

                        <div class="form-actions">

                            <a
                                class="btn btn-secondary"
                                href="catalogo.php"
                            >
                                Cancelar
                            </a>

                            <button
                                class="btn btn-primary boton-principal-readpoint"
                                type="submit"
                            >
                                Guardar nueva contraseña
                            </button>

                        </div>

                    </form>

                <?php else: ?>

                    <div class="form-actions">

                        <a
                            class="btn btn-primary boton-principal-readpoint"
                            href="catalogo.php"
                        >
                            Volver al catálogo
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </main>

    </div>

<?php else: ?>

    <!-- Área administrativa -->

    <div class="app-layout">

        <?php include __DIR__ . '/menu.php'; ?>

        <main class="main-content">

            <div class="content-card">

                <div class="page-header">

                    <h2>Mi cuenta</h2>

                </div>

                <?php if ($exito === "1"): ?>

                    <div class="alert alert-success">
                        Tu contraseña fue actualizada correctamente.
                    </div>

                <?php elseif ($error === "actual"): ?>

                    <div class="alert alert-error">
                        La contraseña actual no es correcta.
                    </div>

                <?php elseif ($error === "formato"): ?>

                    <div class="alert alert-error">
                        La nueva contraseña debe tener entre
                        8 y 12 caracteres.
                    </div>

                <?php elseif ($error === "coincidencia"): ?>

                    <div class="alert alert-error">
                        La nueva contraseña y su confirmación
                        no coinciden.
                    </div>

                <?php elseif ($error === "igual"): ?>

                    <div class="alert alert-error">
                        La nueva contraseña no puede ser igual
                        a la actual.
                    </div>

                <?php elseif ($error === "guardar"): ?>

                    <div class="alert alert-error">
                        No fue posible actualizar la contraseña.
                        Inténtalo nuevamente.
                    </div>

                <?php endif; ?>

                <?php if ($exito !== "1"): ?>

                    <form
                        class="form-card"
                        action="perfil_procesar.php"
                        method="POST"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo htmlspecialchars(
                                $token,
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                        >

                        <div class="form-group">

                            <label for="password_actual">
                                Contraseña actual
                            </label>

                            <input
                                id="password_actual"
                                type="password"
                                name="password_actual"
                                autocomplete="current-password"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="password_nueva">
                                Nueva contraseña
                            </label>

                            <input
                                id="password_nueva"
                                type="password"
                                name="password_nueva"
                                minlength="8"
                                maxlength="12"
                                autocomplete="new-password"
                                required
                            >

                            <small>
                                Debe tener entre 8 y 12 caracteres.
                            </small>

                        </div>

                        <div class="form-group">

                            <label for="password_confirmar">
                                Confirmar nueva contraseña
                            </label>

                            <input
                                id="password_confirmar"
                                type="password"
                                name="password_confirmar"
                                minlength="8"
                                maxlength="12"
                                autocomplete="new-password"
                                required
                            >

                        </div>

                        <div class="form-actions">

                            <a
                                class="btn btn-secondary"
                                href="dashboard.php"
                            >
                                Cancelar
                            </a>

                            <button
                                class="btn btn-primary boton-principal-readpoint"
                                type="submit"
                            >
                                Guardar nueva contraseña
                            </button>

                        </div>

                    </form>

                <?php else: ?>

                    <div class="form-actions">

                        <a
                            class="btn btn-primary boton-principal-readpoint"
                            href="dashboard.php"
                        >
                            Volver al panel
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </main>

    </div>

<?php endif; ?>

</body>

</html>