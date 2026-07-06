<?php
// Este archivo se incluye en cada vista protegida.
// Requiere que ya se haya iniciado sesión antes de incluirlo.
$rolActual = $_SESSION["rol"] ?? "";
?>
<nav>
    <a href="dashboard.php">Inicio</a>

    <?php if ($rolActual === "admin"): ?>
        | <a href="usuarios.php">Usuarios</a>
        | <a href="estudiantes.php">Estudiantes</a>
        | <a href="carreras.php">Carreras</a>
    <?php endif; ?>

    <?php if ($rolActual === "estudiante"): ?>
        | <a href="reservas.php">Mis Reservas</a>
        | <a href="catalogo.php">Catálogo de Libros</a>
    <?php endif; ?>

    | <a href="logout.php">Cerrar sesión</a>
</nav>
<hr>