<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/UsuarioController.php';
require_once __DIR__ . '/../app/Models/UsuarioModel.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$token = Csrf::generarToken();
$error = $_GET["error"] ?? "";
$id = $_GET["id"] ?? null;

$usuarioActual = null;

if ($id !== null) {

    $modelo = new UsuarioModel();

    $usuarioActual = $modelo->obtenerPorId((int)$id);

    if ($usuarioActual === null) {
        header("Location: usuarios.php");
        exit;
    }

}

$esEdicion = $usuarioActual !== null;
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
        <?php echo $esEdicion
            ? "Editar usuario | ReadPoint"
            : "Nuevo usuario | ReadPoint"; ?>
    </title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/usuario-form.css?v=1">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-formulario-usuario">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>
                    <?php echo $esEdicion
                        ? "Editar usuario"
                        : "Nuevo usuario"; ?>
                </h1>

                <p>
                    <?php echo $esEdicion
                        ? "Modifica los datos de acceso y el rol del usuario."
                        : "Crea una nueva cuenta para acceder a ReadPoint."; ?>
                </p>
            </div>

            <a
                class="boton-volver-usuarios"
                href="usuarios.php"
            >
                Volver a usuarios
            </a>

        </section>

        <?php if ($error === "usuario"): ?>

            <div class="alert alert-error">
                El nombre de usuario debe tener entre 3 y 50 caracteres.
            </div>

        <?php elseif ($error === "password"): ?>

            <div class="alert alert-error">
                La contraseña debe tener entre 8 y 12 caracteres.
            </div>

        <?php elseif ($error === "rol"): ?>

            <div class="alert alert-error">
                Rol inválido.
            </div>

        <?php elseif ($error === "duplicado"): ?>

            <div class="alert alert-error">
                Ese nombre de usuario ya existe.
            </div>

        <?php endif; ?>

        <section class="panel-formulario-usuario">

            <form
                class="formulario-usuario"
                action="usuario_procesar.php"
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

                <?php if ($esEdicion): ?>

                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo htmlspecialchars(
                            $usuarioActual["id"],
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>"
                    >

                <?php endif; ?>

                <div class="grupo-campo-usuario">

                    <label for="usuario">
                        Usuario
                    </label>

                    <input
                        id="usuario"
                        type="text"
                        name="usuario"
                        required
                        minlength="3"
                        maxlength="50"
                        placeholder="Ej. juan.perez"
                        value="<?php echo $esEdicion
                            ? htmlspecialchars(
                                $usuarioActual["usuario"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                            : ""; ?>"
                    >

                    <small>
                        Debe tener entre 3 y 50 caracteres.
                    </small>

                </div>

                <div class="grupo-campo-usuario">

                    <label for="password">
                        Contraseña
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        minlength="8"
                        maxlength="12"
                        placeholder="Debe tener entre 8 y 12 caracteres"
                        <?php echo $esEdicion
                            ? ""
                            : "required"; ?>
                    >

                    <small>
                        <?php echo $esEdicion
                            ? "Déjala en blanco para conservar la contraseña actual."
                            : "La contraseña debe tener entre 8 y 12 caracteres."; ?>
                    </small>

                </div>

                <div class="grupo-campo-usuario">

                    <label for="rol">
                        Rol
                    </label>

                    <select
                        id="rol"
                        name="rol"
                        required
                    >

                        <option value="">
                            Seleccione un rol
                        </option>

                        <option
                            value="admin"
                            <?php echo (
                                $esEdicion &&
                                $usuarioActual["rol"] === "admin"
                            )
                                ? "selected"
                                : ""; ?>
                        >
                            Administrador
                        </option>

                        <option
                            value="estudiante"
                            <?php echo (
                                $esEdicion &&
                                $usuarioActual["rol"] === "estudiante"
                            )
                                ? "selected"
                                : ""; ?>
                        >
                            Estudiante
                        </option>

                        <option
                            value="profesor"
                            <?php echo (
                                $esEdicion &&
                                $usuarioActual["rol"] === "profesor"
                            )
                                ? "selected"
                                : ""; ?>
                        >
                            Profesor
                        </option>

                    </select>

                    <small>
                        Define qué funciones podrá utilizar esta cuenta.
                    </small>

                </div>

                <div class="acciones-formulario-usuario">

                    <a
                        class="boton-cancelar-usuario"
                        href="usuarios.php"
                    >
                        Cancelar
                    </a>

                    <button
                        class="boton-guardar-usuario"
                        type="submit"
                    >
                        <?php echo $esEdicion
                            ? "Guardar cambios"
                            : "Crear usuario"; ?>
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>