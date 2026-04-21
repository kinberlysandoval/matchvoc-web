<?php include 'conexion.php'; include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchVoc - Estadísticas</title>
    <link rel="stylesheet" href="estilos.css/estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:30px; }
        .stat-card-mini { background:#fff; border-radius:14px; padding:20px; text-align:center; box-shadow:0 2px 10px rgba(0,0,0,0.07); border-bottom:4px solid #A8C63D; }
        .stat-card-mini .num { font-size:32px; font-weight:700; color:#1B2E5E; }
        .stat-card-mini .lbl { font-size:12px; color:#888; margin-top:4px; font-weight:600; }
        .stat-card-mini .ico { font-size:28px; margin-bottom:6px; }
        .seccion-titulo { color:#1B2E5E; font-size:17px; font-weight:700; margin:30px 0 15px; border-left:4px solid #A8C63D; padding-left:12px; }
        .tabla-est { width:100%; border-collapse:collapse; font-size:13px; margin-bottom:30px; }
        .tabla-est th { background:#1B2E5E; color:#fff; padding:10px 12px; text-align:left; }
        .tabla-est td { padding:10px 12px; border-bottom:1px solid #f0f0f0; }
        .tabla-est tr:hover td { background:#f7f9ff; }
        .barra-progreso { background:#eee; border-radius:10px; height:10px; margin-top:4px; }
        .barra-fill { background:#A8C63D; border-radius:10px; height:10px; }
        .tag-sector { background:#e8f0fe; color:#1B2E5E; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .grafica-wrap { background:#fff; border-radius:14px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.07); margin-bottom:24px; }
        .grafica-wrap h3 { color:#1B2E5E; font-size:15px; margin:0 0 16px; }
        .grid-graficas { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:30px; }
        .ubicacion-card { background:#fff; border-radius:12px; padding:16px 20px; box-shadow:0 2px 8px rgba(0,0,0,0.07); margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; }
        .ubicacion-card strong { color:#1B2E5E; font-size:14px; }
        .ubicacion-card span { color:#888; font-size:12px; }
        .badge-verde { background:#e6f9e6; color:#267326; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        @media(max-width:900px){ .stats-grid-4{grid-template-columns:repeat(2,1fr);} .grid-graficas{grid-template-columns:1fr;} }
    </style>
</head>
<body>

<header class="header-principal">
    <h1>MATCHVOC</h1>
    <p>Panel Administrativo</p>
</header>

<?php
// ── DATOS GENERALES ──────────────────────────────────────
$total_alumnos      = mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM usuarios WHERE rol_id = 2"))[0];
$total_tarjetas     = mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM tarjetas"))[0];
$total_universidades= mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM universidades"))[0];
$total_sectores     = mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(DISTINCT sector) FROM tarjetas"))[0];
$total_carreras     = mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(DISTINCT carrera) FROM tarjetas"))[0];
$total_diagnosticos = mysqli_fetch_row(mysqli_query($conexion, "SELECT COUNT(*) FROM diagnosticos"))[0];

// ── TARJETAS POR SECTOR ──────────────────────────────────
$res_sectores = mysqli_query($conexion, "
    SELECT sector, COUNT(*) as total, COUNT(DISTINCT carrera) as carreras
    FROM tarjetas
    GROUP BY sector
    ORDER BY total DESC
");
$sectores_data = [];
while ($r = mysqli_fetch_assoc($res_sectores)) $sectores_data[] = $r;

// ── CARRERAS POR SECTOR (top 10) ─────────────────────────
$res_carreras = mysqli_query($conexion, "
    SELECT carrera, sector, COUNT(*) as total
    FROM tarjetas
    GROUP BY carrera, sector
    ORDER BY total DESC
    LIMIT 10
");
$carreras_data = [];
while ($r = mysqli_fetch_assoc($res_carreras)) $carreras_data[] = $r;
$max_carrera = $carreras_data[0]['total'] ?? 1;

// ── UNIVERSIDADES POR SECTOR ─────────────────────────────
$res_unis = mysqli_query($conexion, "
    SELECT sector_relacionado, COUNT(*) as total, GROUP_CONCAT(nombre SEPARATOR '||') as nombres
    FROM universidades
    GROUP BY sector_relacionado
    ORDER BY total DESC
");
$unis_sector = [];
while ($r = mysqli_fetch_assoc($res_unis)) $unis_sector[] = $r;

// ── UNIVERSIDADES POR LOCALIDAD ──────────────────────────
$res_loc = mysqli_query($conexion, "
    SELECT localidad, COUNT(*) as total
    FROM universidades
    WHERE localidad IS NOT NULL AND localidad != ''
    GROUP BY localidad
    ORDER BY total DESC
");
$localidades = [];
while ($r = mysqli_fetch_assoc($res_loc)) $localidades[] = $r;

// ── DIAGNÓSTICOS POR ÁREA ─────────────────────────────────
$res_diagnosticos = mysqli_query($conexion, "
    SELECT area_resultado_id, COUNT(*) as total
    FROM diagnosticos
    GROUP BY area_resultado_id
    ORDER BY total DESC
");
$diagnosticos_data = [];
while ($r = mysqli_fetch_assoc($res_diagnosticos)) $diagnosticos_data[] = $r;

// ── DATOS PARA GRÁFICAS ───────────────────────────────────
$lbl_sectores = array_column($sectores_data, 'sector');
$dat_sectores = array_column($sectores_data, 'total');

$lbl_carreras = array_column($carreras_data, 'carrera');
$dat_carreras = array_column($carreras_data, 'total');

$colores = ['#1B2E5E','#A8C63D','#26408B','#f0a500','#ff6b6b','#4ecdc4','#45b7d1','#96ceb4','#ffeaa7','#dfe6e9'];
?>

<div class="contenedor-layout">
    <nav class="menu-lateral">
        <h2>MENÚ</h2>
        <ul>
            <li><a href="index.php">🏠 Inicio</a></li>
            <li><a href="alumnos.php">👥 Alumnos</a></li>
            <li><a href="tarjetas.php">🃏 Tarjetas</a></li>
            <li><a href="preguntas.php">❓ Preguntas</a></li>
            <li><a href="diagnosticos.php">📊 Diagnósticos</a></li>
            <li><a href="universidades.php">🏫 Universidades</a></li>
            <li><a href="estadisticas.php" class="activo">📈 Estadísticas</a></li>
        </ul>
    </nav>

    <main class="contenido-derecha" style="align-items:flex-start; padding:30px;">
        <div style="width:100%;">

            <h2 style="color:#1B2E5E; margin:0 0 4px;">📈 Estadísticas Generales</h2>
            <p style="color:#888; font-size:13px; margin:0 0 24px;">Panorama completo del sistema MatchVoc.</p>

            <!-- PANEL DE BÚSQUEDA -->
            <div style="background:#fff; border-radius:14px; padding:20px 24px; box-shadow:0 2px 10px rgba(0,0,0,0.07); margin-bottom:28px; display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                <div style="position:relative; flex:1; min-width:200px;">
                    <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa;">🔍</span>
                    <input type="text" id="buscador" placeholder="Buscar sector, carrera, universidad..."
                        style="width:100%; padding:10px 12px 10px 36px; border:1px solid #e0e0e0; border-radius:8px; font-size:13px; font-family:Montserrat,sans-serif; box-sizing:border-box;">
                </div>

                <select id="filtroSector" style="padding:10px 14px; border:1px solid #e0e0e0; border-radius:8px; font-size:13px; font-family:Montserrat,sans-serif; color:#444; min-width:180px;">
                    <option value="">Todos los sectores</option>
                    <?php foreach ($sectores_data as $s): ?>
                    <option value="<?= htmlspecialchars($s['sector']) ?>"><?= htmlspecialchars($s['sector']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="filtroLocalidad" style="padding:10px 14px; border:1px solid #e0e0e0; border-radius:8px; font-size:13px; font-family:Montserrat,sans-serif; color:#444; min-width:160px;">
                    <option value="">Todas las localidades</option>
                    <?php foreach ($localidades as $loc): ?>
                    <option value="<?= htmlspecialchars($loc['localidad']) ?>"><?= htmlspecialchars($loc['localidad']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button onclick="filtrar()" style="background:#A8C63D; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; font-family:Montserrat,sans-serif;">Filtrar</button>
                <button onclick="limpiarFiltros()" style="background:none; border:1px solid #ddd; padding:10px 16px; border-radius:8px; font-size:13px; cursor:pointer; color:#888; font-family:Montserrat,sans-serif;">✕ Limpiar</button>

                <div id="resultado-busqueda" style="width:100%; font-size:12px; color:#888; display:none;">
                    Mostrando resultados para: <strong id="texto-filtro"></strong>
                </div>
            </div>

            <!-- TARJETAS GENERALES -->
            <div class="stats-grid-4">
                <div class="stat-card-mini">
                    <div class="ico">👥</div>
                    <div class="num"><?= $total_alumnos ?></div>
                    <div class="lbl">Alumnos registrados</div>
                </div>
                <div class="stat-card-mini">
                    <div class="ico">🃏</div>
                    <div class="num"><?= $total_tarjetas ?></div>
                    <div class="lbl">Tarjetas del test</div>
                </div>
                <div class="stat-card-mini">
                    <div class="ico">🎓</div>
                    <div class="num"><?= $total_carreras ?></div>
                    <div class="lbl">Carreras distintas</div>
                </div>
                <div class="stat-card-mini">
                    <div class="ico">🏫</div>
                    <div class="num"><?= $total_universidades ?></div>
                    <div class="lbl">Universidades</div>
                </div>
                <div class="stat-card-mini">
                    <div class="ico">🗂️</div>
                    <div class="num"><?= $total_sectores ?></div>
                    <div class="lbl">Sectores vocacionales</div>
                </div>
                <div class="stat-card-mini">
                    <div class="ico">📊</div>
                    <div class="num"><?= $total_diagnosticos ?></div>
                    <div class="lbl">Tests completados</div>
                </div>
            </div>

            <!-- GRÁFICAS -->
            <div class="grid-graficas">
                <div class="grafica-wrap">
                    <h3>🃏 Tarjetas por sector</h3>
                    <canvas id="graficaSectores" height="220"></canvas>
                </div>
                <div class="grafica-wrap">
                    <h3>🎓 Top 10 carreras con más tarjetas</h3>
                    <canvas id="graficaCarreras" height="220"></canvas>
                </div>
            </div>

            <!-- TABLA SECTORES -->
            <div class="seccion-titulo">🗂️ Desglose por sector vocacional</div>
            <div class="caja-blanca" style="padding:0; overflow:hidden; margin-bottom:30px;">
                <table class="tabla-est" id="tabla-sectores">
                    <thead>
                        <tr>
                            <th>Sector</th>
                            <th>Tarjetas</th>
                            <th>Carreras</th>
                            <th>Proporción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sectores_data as $s):
                            $pct = $total_tarjetas > 0 ? round(($s['total'] / $total_tarjetas) * 100) : 0;
                        ?>
                        <tr>
                            <td><span class="tag-sector"><?= htmlspecialchars($s['sector']) ?></span></td>
                            <td><strong><?= $s['total'] ?></strong></td>
                            <td><?= $s['carreras'] ?> carrera(s)</td>
                            <td style="min-width:140px;">
                                <?= $pct ?>%
                                <div class="barra-progreso"><div class="barra-fill" style="width:<?= $pct ?>%;"></div></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- TOP CARRERAS -->
            <div class="seccion-titulo">🎓 Top 10 carreras con más preguntas</div>
            <div class="caja-blanca" style="padding:0; overflow:hidden; margin-bottom:30px;">
                <table class="tabla-est" id="tabla-carreras">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Carrera</th>
                            <th>Sector</th>
                            <th>Tarjetas</th>
                            <th>Proporción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($carreras_data as $i => $c):
                            $pct = round(($c['total'] / $max_carrera) * 100);
                        ?>
                        <tr>
                            <td style="color:#aaa;"><?= $i+1 ?></td>
                            <td><strong><?= htmlspecialchars($c['carrera']) ?></strong></td>
                            <td><span class="tag-sector"><?= htmlspecialchars($c['sector']) ?></span></td>
                            <td><?= $c['total'] ?></td>
                            <td style="min-width:120px;">
                                <div class="barra-progreso"><div class="barra-fill" style="width:<?= $pct ?>%;"></div></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- UNIVERSIDADES POR SECTOR -->
            <div class="seccion-titulo">🏫 Universidades por sector</div>
            <div class="caja-blanca" style="padding:0; overflow:hidden; margin-bottom:30px;">
                <table class="tabla-est" id="tabla-unis">
                    <thead>
                        <tr>
                            <th>Sector</th>
                            <th>Universidades</th>
                            <th>Instituciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unis_sector as $u): ?>
                        <tr>
                            <td><span class="tag-sector"><?= htmlspecialchars($u['sector_relacionado']) ?></span></td>
                            <td><strong><?= $u['total'] ?></strong></td>
                            <td style="font-size:12px; color:#666;">
                                <?= htmlspecialchars(implode(', ', array_slice(explode('||', $u['nombres']), 0, 3))) ?>
                                <?= count(explode('||', $u['nombres'])) > 3 ? '...' : '' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- UBICACIONES -->
            <div class="seccion-titulo">📍 Universidades por ubicación</div>
            <?php foreach ($localidades as $loc): ?>
            <div class="ubicacion-card">
                <div>
                    <strong>📍 <?= htmlspecialchars($loc['localidad']) ?></strong>
                </div>
                <span class="badge-verde"><?= $loc['total'] ?> universidad(es)</span>
            </div>
            <?php endforeach; ?>

            <?php if (count($diagnosticos_data) > 0): ?>
            <!-- DIAGNÓSTICOS POR ÁREA -->
            <div class="seccion-titulo">🎯 Áreas más frecuentes en diagnósticos</div>
            <div class="caja-blanca" style="padding:0; overflow:hidden; margin-bottom:30px;">
                <table class="tabla-est" id="tabla-diagnosticos">
                    <thead>
                        <tr>
                            <th>Área resultado</th>
                            <th>Veces registrada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($diagnosticos_data as $r): ?>
                        <tr>
                            <td><span class="tag-sector"><?= htmlspecialchars($r['area_resultado_id']) ?></span></td>
                            <td><strong><?= $r['total'] ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<!-- TOOLBAR -->
<div class="toolbar">
    <button class="toolbar-btn" onclick="abrirPanel('perfil')" title="Perfil">👤</button>
    <button class="toolbar-btn" onclick="abrirPanel('notificaciones')" title="Notificaciones">
        🔔<span class="toolbar-badge" id="tb-badge" style="display:none;">0</span>
    </button>
    <button class="toolbar-btn" onclick="abrirPanel('accesos')" title="Accesos rápidos">⚡</button>
</div>

<div class="toolbar-panel" id="toolbar-panel">
    <div id="panel-perfil" style="display:none;">
        <div class="toolbar-panel-header">👤 Mi Perfil <button onclick="cerrarPanel()">✕</button></div>
        <div class="toolbar-panel-body">
            <div style="text-align:center; margin-bottom:20px;">
                <div style="font-size:50px;">👤</div>
                <div style="font-weight:700; color:#1B2E5E;"><?= $_SESSION['nombre'] ?? 'Administrador' ?></div>
            </div>
            <a href="login.php?logout=1" style="text-decoration:none;">
                <div class="toolbar-item-row" style="color:#ff4d4d;">🚪 Cerrar sesión</div>
            </a>
        </div>
    </div>
    <div id="panel-notificaciones" style="display:none;">
        <div class="toolbar-panel-header">🔔 Notificaciones <button onclick="cerrarPanel()">✕</button></div>
        <div class="toolbar-panel-body">
            <div id="notif-lista-panel"><p style="color:#aaa;text-align:center;">Cargando...</p></div>
        </div>
    </div>
    <div id="panel-accesos" style="display:none;">
        <div class="toolbar-panel-header">⚡ Accesos rápidos <button onclick="cerrarPanel()">✕</button></div>
        <div class="toolbar-panel-body">
            <a href="alumnos.php" style="text-decoration:none;color:inherit;"><div class="toolbar-item-row">👥 Alumnos</div></a>
            <a href="tarjetas.php" style="text-decoration:none;color:inherit;"><div class="toolbar-item-row">🃏 Tarjetas</div></a>
            <a href="preguntas.php" style="text-decoration:none;color:inherit;"><div class="toolbar-item-row">❓ Preguntas</div></a>
            <a href="diagnosticos.php" style="text-decoration:none;color:inherit;"><div class="toolbar-item-row">📊 Diagnósticos</div></a>
            <a href="universidades.php" style="text-decoration:none;color:inherit;"><div class="toolbar-item-row">🏫 Universidades</div></a>
        </div>
    </div>
</div>

<script>
// GRÁFICA SECTORES
new Chart(document.getElementById('graficaSectores').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($lbl_sectores) ?>,
        datasets: [{
            data: <?= json_encode($dat_sectores) ?>,
            backgroundColor: <?= json_encode($colores) ?>,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 12 } }
        }
    }
});

// GRÁFICA CARRERAS
new Chart(document.getElementById('graficaCarreras').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($lbl_carreras) ?>,
        datasets: [{
            label: 'Tarjetas',
            data: <?= json_encode($dat_carreras) ?>,
            backgroundColor: '#1B2E5E',
            borderColor: '#A8C63D',
            borderWidth: 2,
            borderRadius: 6
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// BUSCADOR Y FILTROS
function filtrar() {
    const texto = document.getElementById('buscador').value.toLowerCase();
    const sector = document.getElementById('filtroSector').value.toLowerCase();
    const localidad = document.getElementById('filtroLocalidad').value.toLowerCase();

    document.querySelectorAll('.tabla-est tbody tr').forEach(fila => {
        const contenido = fila.innerText.toLowerCase();
        const coincide = (!texto || contenido.includes(texto)) &&
                         (!sector || contenido.includes(sector)) &&
                         (!localidad || contenido.includes(localidad));
        fila.style.display = coincide ? '' : 'none';
    });

    document.querySelectorAll('.ubicacion-card').forEach(card => {
        const contenido = card.innerText.toLowerCase();
        const coincide = (!texto || contenido.includes(texto)) &&
                         (!localidad || contenido.includes(localidad));
        card.style.display = coincide ? '' : 'none';
    });

    const activos = [texto, sector, localidad].filter(Boolean);
    if (activos.length > 0) {
        document.getElementById('resultado-busqueda').style.display = 'block';
        document.getElementById('texto-filtro').textContent = activos.join(', ');
    } else {
        document.getElementById('resultado-busqueda').style.display = 'none';
    }
}

function limpiarFiltros() {
    document.getElementById('buscador').value = '';
    document.getElementById('filtroSector').value = '';
    document.getElementById('filtroLocalidad').value = '';
    document.querySelectorAll('.tabla-est tbody tr').forEach(f => f.style.display = '');
    document.querySelectorAll('.ubicacion-card').forEach(c => c.style.display = '');
    document.getElementById('resultado-busqueda').style.display = 'none';
}

document.getElementById('buscador').addEventListener('input', filtrar);
document.getElementById('filtroSector').addEventListener('change', filtrar);
document.getElementById('filtroLocalidad').addEventListener('change', filtrar);

// TOOLBAR
let panelActivo = null;
function abrirPanel(tipo) {
    const todos = ['perfil','notificaciones','accesos'];
    if (panelActivo === tipo) { cerrarPanel(); return; }
    todos.forEach(t => document.getElementById('panel-'+t).style.display='none');
    document.getElementById('panel-'+tipo).style.display='block';
    document.getElementById('toolbar-panel').classList.add('abierto');
    panelActivo = tipo;
    if (tipo === 'notificaciones') cargarNotificacionesPanel();
}
function cerrarPanel() {
    document.getElementById('toolbar-panel').classList.remove('abierto');
    panelActivo = null;
}
function cargarNotificacionesPanel() {
    fetch('notificaciones.php').then(r=>r.json()).then(data=>{
        const lista = document.getElementById('notif-lista-panel');
        const badge = document.getElementById('tb-badge');
        if (data.total > 0) {
            badge.style.display='flex'; badge.textContent=data.total;
            lista.innerHTML = data.items.map(n=>`
                <div class="toolbar-item-row">
                    <span style="font-size:18px;">${n.icono}</span>
                    <div><div>${n.mensaje}</div><div style="color:#aaa;font-size:11px;">${n.tiempo}</div></div>
                </div>`).join('');
        } else {
            badge.style.display='none';
            lista.innerHTML='<p style="color:#aaa;text-align:center;padding:20px;">✅ Sin notificaciones nuevas</p>';
        }
    });
}
cargarNotificacionesPanel();
setInterval(cargarNotificacionesPanel, 30000);
</script>

</body>
</html>
