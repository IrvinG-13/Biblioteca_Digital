<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Controllers/UsuarioController.php';
require_once __DIR__ . '/../app/Core/Csrf.php';

$controller = new UsuarioController();
$datos = $controller->listar();

$token = Csrf::generarToken();
$exito = $_GET["exito"] ?? "";
$error = $_GET["error"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Usuarios | ReadPoint</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=3">
    <link rel="stylesheet" href="assets/css/usuarios.css?v=1">
</head>

<body>

<div class="app-layout">

    <?php require_once __DIR__ . "/menu.php"; ?>

    <main class="main-content">

        <section class="encabezado-usuarios">

            <div>
                <span class="etiqueta-pagina">
                    Administración
                </span>

                <h1>Gestión de usuarios</h1>

                <p>
                    Consulta, edita, bloquea o elimina los usuarios registrados.
                </p>
            </div>

            <a
                class="boton-nuevo-usuario"
                href="usuario_form.php"
            >
                Nuevo usuario
            </a>

        </section>

        <?php if ($exito === "1"): ?>
            <div class="alert alert-success">
                Operación realizada con éxito.
            </div>
        <?php elseif ($error === "automodificacion"): ?>
            <div class="alert alert-error">
                No puedes bloquear ni eliminar tu propio usuario.
            </div>
        <?php endif; ?>

        <section class="panel-usuarios">

            <form
                class="barra-busqueda-usuarios"
                action="usuarios.php"
                method="GET"
            >
                <input
                    class="campo-busqueda-usuarios"
                    type="text"
                    name="busqueda"
                    placeholder="Buscar por nombre de usuario..."
                    value="<?php echo htmlspecialchars(
                        $datos["busqueda"],
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>"
                >

                <button
                    class="boton-buscar"
                    type="submit"
                >
                    Buscar
                </button>

                <a
                    class="boton-limpiar"
                    href="usuarios.php"
                >
                    Limpiar
                </a>
            </form>

            <div class="contenedor-tabla-usuarios">

                <table class="tabla-usuarios">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Intentos fallidos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($datos["usuarios"])): ?>

                            <tr>
                                <td
                                    class="estado-vacio-tabla"
                                    colspan="6"
                                >
                                    No se encontraron usuarios.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($datos["usuarios"] as $u): ?>

                                <tr>

                                    <td>
                                        <?php echo htmlspecialchars($u["id"]); ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?php echo htmlspecialchars($u["usuario"]); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <span class="etiqueta-rol">
                                            <?php echo htmlspecialchars($u["rol"]); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if ((int)$u["bloqueado"] === 1): ?>

                                            <span class="estado-usuario estado-bloqueado">
                                                Bloqueado
                                            </span>

                                        <?php else: ?>

                                            <span class="estado-usuario estado-activo">
                                                Activo
                                            </span>

                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars(
                                            $u["intentos_fallidos"]
                                        ); ?>
                                    </td>

                                    <td>

                                        <div class="acciones-usuario">

                                            <a
                                                class="accion-editar"
                                                href="usuario_form.php?id=<?php echo urlencode(
                                                    $u["id"]
                                                ); ?>"
                                            >
                                                Editar
                                            </a>

                                            <form
                                                action="usuario_estado.php"
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

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?php echo htmlspecialchars(
                                                        $u["id"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ); ?>"
                                                >

                                                <?php if ((int)$u["bloqueado"] === 1): ?>

                                                    <input
                                                        type="hidden"
                                                        name="bloqueado"
                                                        value="0"
                                                    >

                                                    <button
                                                        class="accion-estado"
                                                        type="submit"
                                                    >
                                                        Reactivar
                                                    </button>

                                                <?php else: ?>

                                                    <input
                                                        type="hidden"
                                                        name="bloqueado"
                                                        value="1"
                                                    >

                                                    <button
                                                        class="accion-estado"
                                                        type="submit"
                                                    >
                                                        Bloquear
                                                    </button>

                                                <?php endif; ?>
                                            </form>

                                            <form
                                                action="usuario_eliminar.php"
                                                method="POST"
                                                onsubmit="return confirm(
                                                    '¿Seguro que deseas eliminar este usuario? Esta acción no se puede deshacer.'
                                                );"
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

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?php echo htmlspecialchars(
                                                        $u["id"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ); ?>"
                                                >

                                                <button
                                                    class="accion-eliminar"
                                                    type="submit"
                                                >
                                                    Eliminar
                                                </button>
                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <nav class="paginacion-usuarios">

                <?php for (
                    $i = 1;
                    $i <= $datos["totalPaginas"];
                    $i++
                ): ?>

                    <?php if ($i === $datos["paginaActual"]): ?>

                        <span class="pagina-actual">
                            <?php echo $i; ?>
                        </span>

                    <?php else: ?>

                        <a href="usuarios.php?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode(
                            $datos["busqueda"]
                        ); ?>">
                            <?php echo $i; ?>
                        </a>

                    <?php endif; ?>

                <?php endfor; ?>

            </nav>

        </section>

    </main>

</div>

</body>
</html>