<?php include 'conexion.php'; include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchVoc - Administrador</title>
    <link rel="stylesheet" href="estilos.css/estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="header-principal">
    <h1>MATCHVOC</h1>
    <p>Panel Administrativo</p>
</header>

<div class="contenedor-layout">

    <nav class="menu-lateral">
        <h2>MENÚ</h2>
        <ul>
            <li><a href="index.php" class="activo">🏠 Inicio</a></li>
            <li><a href="alumnos.php">👥 Alumnos</a></li>
            <li><a href="tarjetas.php">🃏 Tarjetas</a></li>
            <li><a href="preguntas.php">❓ Preguntas</a></li>
            <li><a href="resultados.php">📊 Resultados</a></li>
            <li><a href="universidades.php">🏫 Universidades</a></li>
            <li><a href="estadisticas.php">📈 Estadísticas</a></li>
        </ul>
    </nav>

    <main class="contenido-derecha">
        <div class="caja-blanca">
            <h2>¡Bienvenido a MatchVoc!</h2>
            <p>En este espacio, nos dedicamos con esmero a la Orientación Vocacional de los alumnos del <strong>Bachillerato Digital 73</strong>.</p>
            <p>Nuestro objetivo es brindarte las herramientas necesarias para llevar un control eficiente y profesional de tus estudiantes.</p>
            <p class="frase">"Guiando el talento hacia el futuro."</p>
        </div>
    </main>

</div>

</body>
</html>