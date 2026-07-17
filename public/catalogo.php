<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION["rol"] !== "estudiante") {
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . '/../app/Core/NoCache.php';
NoCache::aplicar();

require_once __DIR__ . '/../app/Models/LibroModel.php';
require_once __DIR__ . '/../app/Models/CategoriaModel.php';

function escapar(?string $valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

$busqueda = trim($_GET["busqueda"] ?? "");
$categoriaId = (int)($_GET["categoria_id"] ?? 0);

$libroModel = new LibroModel();
$categoriaModel = new CategoriaModel();

$libros = $libroModel->listarCatalogo(
    $busqueda,
    $categoriaId
);

$categorias = $categoriaModel->obtenerTodas();
$resumen = $libroModel->obtenerResumenCatalogo();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Catálogo - Biblioteca Digital</title>
    <link
    rel="stylesheet"
    href="assets/css/style.css"
>

<link
    rel="stylesheet"
    href="assets/css/student.css?v=1"
>
</head>

<body class="student-body">

<div class="student-layout">

    <?php include __DIR__ . '/menu_estudiante.php'; ?>

    <main class="student-main">

        <section class="student-hero">

            <div>
                <span class="student-eyebrow">
                    Biblioteca Digital
                </span>

                <h1>
                    Encuentra tu próxima lectura
                </h1>

                <p>
                    Bienvenido,
                    <strong>
                        <?php echo escapar($_SESSION["usuario"]); ?>
                    </strong>.
                    Explora los libros disponibles en nuestra biblioteca.
                </p>
            </div>

            <div class="catalog-summary">

                <div>
                    <strong>
                        <?php echo (int)$resumen["total_titulos"]; ?>
                    </strong>

                    <span>Títulos registrados</span>
                </div>

                <div>
                    <strong>
                        <?php echo (int)$resumen["total_disponibles"]; ?>
                    </strong>

                    <span>Unidades disponibles</span>
                </div>

            </div>

        </section>

        <section class="catalog-toolbar">

            <form
                action="catalogo.php"
                method="GET"
                class="catalog-search-form"
            >

                <div class="catalog-search-box">

                    <span>⌕</span>

                    <input
                        type="text"
                        name="busqueda"
                        placeholder="Buscar por título, autor o categoría..."
                        value="<?php echo escapar($busqueda); ?>"
                    >

                </div>

                <select name="categoria_id">

                    <option value="0">
                        Todas las categorías
                    </option>

                    <?php foreach ($categorias as $categoria): ?>

                        <option
                            value="<?php echo (int)$categoria["id"]; ?>"
                            <?php echo $categoriaId === (int)$categoria["id"]
                                ? "selected"
                                : ""; ?>
                        >
                            <?php echo escapar($categoria["nombre"]); ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <button
                    type="submit"
                    class="catalog-search-button"
                >
                    Buscar
                </button>

                <?php if ($busqueda !== "" || $categoriaId > 0): ?>

                    <a
                        href="catalogo.php"
                        class="catalog-clear-button"
                    >
                        Limpiar
                    </a>

                <?php endif; ?>

            </form>

        </section>

        <section class="catalog-heading">

            <div>
                <h2>Catálogo de libros</h2>

                <p>
                    <?php echo count($libros); ?>
                    resultado<?php echo count($libros) === 1 ? "" : "s"; ?>
                </p>
            </div>

        </section>

        <?php if (empty($libros)): ?>

            <section class="student-empty-state">

                <div class="empty-icon">⌕</div>

                <h2>No encontramos libros</h2>

                <p>
                    Intenta buscar con otro título, autor o categoría.
                </p>

                <a
                    href="catalogo.php"
                    class="student-primary-button"
                >
                    Ver todo el catálogo
                </a>

            </section>

        <?php else: ?>

            <section class="book-grid">

                <?php foreach ($libros as $libro): ?>

                    <?php
                    $origen = $libro["origen"] ?? "propio";
                    $tipoAcceso = $libro["tipo_acceso"] ?? "gratuito";
                    $disponibles = (int)($libro["unidades_disponibles"] ?? 0);

                    $descripcion = trim(
                        (string)($libro["descripcion"] ?? "")
                    );

                    if (strlen($descripcion) > 115) {
                        $descripcion = substr($descripcion, 0, 115) . "...";
                    }
                    ?>

                    <article class="book-card">

                        <a
                            href="libro_detalle.php?id=<?php echo (int)$libro["id"]; ?>"
                            class="book-image-container"
                        >

                            <?php if (!empty($libro["thumbnail"])): ?>

                                <img
                                    src="../uploads/thumbnails/<?php echo rawurlencode($libro["thumbnail"]); ?>"
                                    alt="Portada de <?php echo escapar($libro["titulo"]); ?>"
                                    class="catalog-book-image"
                                >

                            <?php else: ?>

                                <div class="book-placeholder">
                                    <span>LIBRO</span>
                                </div>

                            <?php endif; ?>

                            <?php if ($origen === "externo"): ?>

                                <span class="book-origin-label external">
                                    Biblioteca externa
                                </span>

                            <?php elseif ($tipoAcceso === "pago"): ?>

                                <span class="book-origin-label paid">
                                    De pago
                                </span>

                            <?php else: ?>

                                <span class="book-origin-label free">
                                    Gratuito
                                </span>

                            <?php endif; ?>

                        </a>

                        <div class="book-card-content">

                            <span class="book-category">
                                <?php echo escapar($libro["categoria_nombre"]); ?>
                            </span>

                            <h3>
                                <a href="libro_detalle.php?id=<?php echo (int)$libro["id"]; ?>">
                                    <?php echo escapar($libro["titulo"]); ?>
                                </a>
                            </h3>

                            <p class="book-author">
                                <?php echo escapar(
                                    $libro["autor"] ?? "Autor no especificado"
                                ); ?>
                            </p>

                            <?php if ($descripcion !== ""): ?>

                                <p class="book-description">
                                    <?php echo escapar($descripcion); ?>
                                </p>

                            <?php endif; ?>

                            <div class="book-card-footer">

                                <div class="book-availability">

                                    <?php if ($origen === "externo"): ?>

                                        <span class="availability-dot external"></span>
                                        Disponible externamente

                                    <?php elseif ($disponibles > 0): ?>

                                        <span class="availability-dot available"></span>

                                        <?php echo $disponibles; ?>
                                        disponible<?php echo $disponibles === 1 ? "" : "s"; ?>

                                    <?php else: ?>

                                        <span class="availability-dot unavailable"></span>
                                        Sin unidades

                                    <?php endif; ?>

                                </div>

                                <a
                                    href="libro_detalle.php?id=<?php echo (int)$libro["id"]; ?>"
                                    class="book-details-link"
                                >
                                    Ver detalles
                                </a>

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