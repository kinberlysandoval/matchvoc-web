<?php include 'conexion.php'; include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchVoc - Resultados</title>
    <link rel="stylesheet" href="estilos.css/estilos.css">
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
            <li><a href="index.php">🏠 Inicio</a></li>
            <li><a href="alumnos.php">👥 Alumnos</a></li>
            <li><a href="tarjetas.php">🃏 Tarjetas</a></li>
            <li><a href="preguntas.php">❓ Preguntas</a></li>
            <li><a href="resultados.php" class="activo">📊 Resultados</a></li>
            <li><a href="universidades.php">🏫 Universidades</a></li>
            <li><a href="estadisticas.php">📈 Estadísticas</a></li>
        </ul>
    </nav>

    <main class="contenido-derecha" style="align-items: flex-start; padding: 30px;">
        <div style="width: 100%;">

            <h2 style="color: #1B2E5E; font-family: 'Montserrat', sans-serif; margin-bottom: 5px;">
                📊 Resultados Vocacionales
            </h2>
            <p style="color: #888; margin-bottom: 20px;">
                Resultados del test de orientación vocacional por alumno.
            </p>

            <?php
            $filtro_usuario = isset($_GET['usuario']) ? intval($_GET['usuario']) : null;

            $sql = "
                SELECT d.id, u.nombre, u.correo, 
                       a.nombre as area_resultado,
                       d.puntaje_total, d.fecha_realizado
                FROM diagnosticos d
                LEFT JOIN usuarios u ON d.usuario_id = u.id
                LEFT JOIN areas_vocacionales a ON d.area_resultado_id = a.id
            ";
            if ($filtro_usuario) {
                $sql .= " WHERE d.usuario_id = $filtro_usuario";
            }
            $sql .= " ORDER BY d.fecha_realizado DESC";

            $resultado = mysqli_query($conexion, $sql);
            $total = mysqli_num_rows($resultado);
            ?>

            <div style="margin-bottom: 15px; display:flex; gap:10px; align-items:center;">
                <span style="background:#1B2E5E; color:white; padding:6px 14px; border-radius:20px; font-size:13px; font-family:'Montserrat',sans-serif;">
                    Total: <?php echo $total; ?> resultados
                </span>
                <?php if ($filtro_usuario): ?>
                <a href="resultados.php" style="color:#1B2E5E; font-size:13px; font-family:'Montserrat',sans-serif;">
                    ← Ver todos
                </a>
                <?php endif; ?>
            </div>

            <?php if ($total > 0): ?>
            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Alumno</th>
                        <th>Correo</th>
                        <th>Área Vocacional</th>
                        <th>Puntaje</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><?php echo $fila['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($fila['nombre'] ?? 'Sin nombre'); ?></strong></td>
                        <td><?php echo htmlspecialchars($fila['correo'] ?? '-'); ?></td>
                        <td>
                            <span style="background:#CCFF00; color:#1A1A1A; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                <?php echo htmlspecialchars($fila['area_resultado'] ?? '-'); ?>
                            </span>
                        </td>
                        <td><strong><?php echo $fila['puntaje_total']; ?></strong></td>
                        <td style="font-size:12px; color:#888;"><?php echo $fila['fecha_realizado']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="caja-vacia">
                <p>📭 No hay resultados aún.</p>
                <p style="color:#aaa; font-size:13px;">Los resultados aparecerán cuando los alumnos completen el test en la app móvil.</p>
            </div>
            <?php endif; ?>

        </div>
    </main>
</div>

</body>
</html>
