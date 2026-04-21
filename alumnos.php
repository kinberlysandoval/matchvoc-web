<?php include 'conexion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchVoc - Alumnos</title>
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
            <li><a href="alumnos.php" class="activo">👥 Alumnos</a></li>
            <li><a href="tarjetas.php">🃏 Tarjetas</a></li>
            <li><a href="preguntas.php">❓ Preguntas</a></li>
            <li><a href="resultados.php">📊 Resultados</a></li>
            <li><a href="universidades.php">🏫 Universidades</a></li>
            <li><a href="estadisticas.php">📈 Estadísticas</a></li>
        </ul>
    </nav>

    <main class="contenido-derecha" style="align-items: flex-start; padding: 30px;">
        <div style="width: 100%;">

            <h2 style="color: #2A5CFF; font-family: 'Montserrat', sans-serif; margin-bottom: 5px;">
                👥 Gestión de Alumnos
            </h2>
            <p style="color: #888; margin-bottom: 20px;">
                Lista de alumnos registrados desde la app móvil.
            </p>

            <?php
            // Filtro por discapacidad
            $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
            $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';

            $where = "u.rol_id = 2";
            if ($filtro != 'todos') {
                $where .= " AND u.discapacidad = '$filtro'";
            }
            if ($busqueda != '') {
                $where .= " AND (u.nombre LIKE '%$busqueda%' OR u.correo LIKE '%$busqueda%')";
            }

            $resultado = mysqli_query($conexion, "
                SELECT u.*, r.nombre as rol 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id
                WHERE $where
                ORDER BY u.fecha_registro DESC
            ");
            $total = mysqli_num_rows($resultado);

            // Conteos por discapacidad
            $conteos = mysqli_query($conexion, "SELECT discapacidad, COUNT(*) as total FROM usuarios WHERE rol_id = 2 GROUP BY discapacidad");
            $stats = ['visual' => 0, 'auditiva' => 0, 'motriz' => 0, 'ninguna' => 0];
            while ($c = mysqli_fetch_assoc($conteos)) {
                $key = $c['discapacidad'] ?? 'ninguna';
                if (isset($stats[$key])) $stats[$key] = $c['total'];
            }
            ?>

            <!-- TARJETAS DE ESTADÍSTICAS -->
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:24px;">
                <div style="background:#E6F1FB; border-radius:12px; padding:14px; text-align:center;">
                    <div style="font-size:22px; font-weight:700; color:#185FA5;"><?php echo $stats['visual']; ?></div>
                    <div style="font-size:12px; color:#185FA5; margin-top:2px;">👁️ Discapacidad visual</div>
                </div>
                <div style="background:#EAF3DE; border-radius:12px; padding:14px; text-align:center;">
                    <div style="font-size:22px; font-weight:700; color:#3B6D11;"><?php echo $stats['auditiva']; ?></div>
                    <div style="font-size:12px; color:#3B6D11; margin-top:2px;">👂 Discapacidad auditiva</div>
                </div>
                <div style="background:#FAEEDA; border-radius:12px; padding:14px; text-align:center;">
                    <div style="font-size:22px; font-weight:700; color:#854F0B;"><?php echo $stats['motriz']; ?></div>
                    <div style="font-size:12px; color:#854F0B; margin-top:2px;">✋ Discapacidad motriz</div>
                </div>
                <div style="background:#F1EFE8; border-radius:12px; padding:14px; text-align:center;">
                    <div style="font-size:22px; font-weight:700; color:#5F5E5A;"><?php echo $stats['ninguna']; ?></div>
                    <div style="font-size:12px; color:#5F5E5A; margin-top:2px;">✅ Sin discapacidad</div>
                </div>
            </div>

            <!-- FILTROS Y BÚSQUEDA -->
            <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; align-items:center;">
                <input type="text" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="🔍 Buscar alumno..." style="padding:8px 14px; border-radius:20px; border:1px solid #ddd; font-size:13px; min-width:200px;">
                
                <?php
                $filtros = [
                    'todos'    => '👥 Todos',
                    'visual'   => '👁️ Visual',
                    'auditiva' => '👂 Auditiva',
                    'motriz'   => '✋ Motriz',
                    'ninguna'  => '✅ Sin discapacidad',
                ];
                foreach ($filtros as $val => $label):
                    $activo = ($filtro == $val) ? 'background:#1A365D; color:white;' : 'background:#f0f0f0; color:#555;';
                ?>
                <button type="submit" name="filtro" value="<?php echo $val; ?>" style="<?php echo $activo; ?> padding:7px 14px; border-radius:20px; border:none; font-size:12px; cursor:pointer;">
                    <?php echo $label; ?>
                </button>
                <?php endforeach; ?>
            </form>

            <!-- CONTADOR -->
            <div style="margin-bottom:16px;">
                <span style="background:#2A5CFF; color:white; padding:6px 14px; border-radius:20px; font-size:13px; font-family:'Montserrat',sans-serif;">
                    👥 Mostrando: <?php echo $total; ?> alumno(s)
                </span>
            </div>

            <!-- TABLA -->
            <?php if ($total > 0): ?>
            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Necesidad especial</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($alumno = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><?php echo $alumno['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($alumno['nombre']); ?></strong></td>
                        <td><?php echo htmlspecialchars($alumno['correo']); ?></td>
                        <td>
                            <?php
                            $disc = $alumno['discapacidad'] ?? 'ninguna';
                            $badges = [
                                'visual'   => ['bg'=>'#E6F1FB', 'color'=>'#0C447C', 'label'=>'👁️ Visual'],
                                'auditiva' => ['bg'=>'#EAF3DE', 'color'=>'#27500A', 'label'=>'👂 Auditiva'],
                                'motriz'   => ['bg'=>'#FAEEDA', 'color'=>'#633806', 'label'=>'✋ Motriz'],
                                'ninguna'  => ['bg'=>'#F1EFE8', 'color'=>'#5F5E5A', 'label'=>'✅ Ninguna'],
                            ];
                            $b = $badges[$disc] ?? $badges['ninguna'];
                            ?>
                            <span style="background:<?php echo $b['bg']; ?>; color:<?php echo $b['color']; ?>; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                <?php echo $b['label']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($alumno['activo'] == 1): ?>
                            <span style="background:#CCFF00; color:#1A1A1A; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600;">✅ Activo</span>
                            <?php else: ?>
                            <span style="background:#fdeaea; color:#c0392b; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600;">❌ Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px; color:#888;"><?php echo $alumno['fecha_registro']; ?></td>
                        <td>
                            <a href="resultados.php?usuario=<?php echo $alumno['id']; ?>" style="color:#2A5CFF; text-decoration:none; font-size:13px; font-weight:600;">
                                📊 Ver resultados
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="caja-vacia">
                <p>🔭 No se encontraron alumnos con ese criterio.</p>
                <p style="color:#aaa; font-size:13px; margin-top:8px;">Intenta con otro filtro o nombre.</p>
            </div>
            <?php endif; ?>

        </div>
    </main>
</div>

</body>
</html>
