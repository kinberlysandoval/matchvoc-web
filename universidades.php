<?php include 'conexion.php'; include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchVoc - Universidades</title>
    <link rel="stylesheet" href="estilos.css/estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .preg-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
        .btn-agregar { background:#1B2E5E; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:14px; }
        .btn-agregar:hover { background:#26408B; }
        .filtros-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; align-items:center; }
        .filtros-bar input, .filtros-bar select { padding:8px 12px; border:1px solid #ddd; border-radius:8px; font-size:13px; font-family:'Montserrat',sans-serif; }
        .filtros-bar button { padding:8px 16px; border:none; border-radius:8px; background:#A8C63D; color:#1B2E5E; font-weight:700; cursor:pointer; font-size:13px; }
        .badge-total { background:#1B2E5E; color:#fff; padding:4px 12px; border-radius:20px; font-size:13px; font-weight:600; }
        .tabla-preguntas { width:100%; border-collapse:collapse; font-size:13px; }
        .tabla-preguntas th { background:#1B2E5E; color:#fff; padding:12px 10px; text-align:left; }
        .tabla-preguntas td { padding:10px; border-bottom:1px solid #f0f0f0; vertical-align:top; }
        .tabla-preguntas tr:hover td { background:#f7f9ff; }
        .tag-sector { background:#e8f0fe; color:#1B2E5E; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .btn-editar { background:#f0a500; color:#fff; border:none; padding:5px 12px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:600; }
        .btn-eliminar { background:#ff4d4d; color:#fff; border:none; padding:5px 12px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:600; }
        .btn-editar:hover { background:#d48f00; }
        .btn-eliminar:hover { background:#cc0000; }
        .modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center; }
        .modal-overlay.activo { display:flex; }
        .modal-caja { background:#fff; border-radius:16px; padding:30px; width:100%; max-width:580px; max-height:90vh; overflow-y:auto; box-shadow:0 10px 40px rgba(0,0,0,0.2); }
        .modal-caja h3 { color:#1B2E5E; margin-bottom:20px; font-size:18px; }
        .form-grupo { margin-bottom:16px; }
        .form-grupo label { display:block; font-size:13px; font-weight:600; color:#444; margin-bottom:6px; }
        .form-grupo input, .form-grupo textarea, .form-grupo select { width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-size:13px; font-family:'Montserrat',sans-serif; box-sizing:border-box; }
        .form-grupo textarea { height:80px; resize:vertical; }
        .form-acciones { display:flex; gap:10px; justify-content:flex-end; margin-top:10px; }
        .btn-guardar { background:#1B2E5E; color:#fff; border:none; padding:10px 24px; border-radius:8px; font-weight:700; cursor:pointer; }
        .btn-cancelar { background:#eee; color:#444; border:none; padding:10px 24px; border-radius:8px; font-weight:600; cursor:pointer; }
        .alerta { padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:13px; font-weight:600; }
        .alerta.exito { background:#e6f9e6; color:#267326; border:1px solid #b3e6b3; }
        .alerta.error { background:#ffe6e6; color:#cc0000; border:1px solid #ffb3b3; }
        .sin-resultados { text-align:center; padding:40px; color:#aaa; font-size:14px; }
        .link-web { color:#1B2E5E; font-size:12px; }
    </style>
</head>
<body>

<header class="header-principal">
    <h1>MATCHVOC</h1>
    <p>Panel Administrativo</p>
</header>

<?php
$mensaje = '';
$tipo_mensaje = '';

// AGREGAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $localidad = mysqli_real_escape_string($conexion, trim($_POST['localidad']));
    $sitio_web = mysqli_real_escape_string($conexion, trim($_POST['sitio_web']));
    $oferta = mysqli_real_escape_string($conexion, trim($_POST['oferta_educativa']));
    $logo = mysqli_real_escape_string($conexion, trim($_POST['logo_url']));
    $sector = mysqli_real_escape_string($conexion, trim($_POST['sector_relacionado']));

    if ($nombre) {
        $sql = "INSERT INTO universidades (nombre, localidad, sitio_web, oferta_educativa, logo_url, sector_relacionado) VALUES ('$nombre','$localidad','$sitio_web','$oferta','$logo','$sector')";
        if (mysqli_query($conexion, $sql)) {
            $mensaje = '✅ Universidad agregada correctamente.';
            $tipo_mensaje = 'exito';
        } else {
            $mensaje = '❌ Error: ' . mysqli_error($conexion);
            $tipo_mensaje = 'error';
        }
    }
}

// EDITAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id = (int)$_POST['id'];
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $localidad = mysqli_real_escape_string($conexion, trim($_POST['localidad']));
    $sitio_web = mysqli_real_escape_string($conexion, trim($_POST['sitio_web']));
    $oferta = mysqli_real_escape_string($conexion, trim($_POST['oferta_educativa']));
    $logo = mysqli_real_escape_string($conexion, trim($_POST['logo_url']));
    $sector = mysqli_real_escape_string($conexion, trim($_POST['sector_relacionado']));

    $sql = "UPDATE universidades SET nombre='$nombre', localidad='$localidad', sitio_web='$sitio_web', oferta_educativa='$oferta', logo_url='$logo', sector_relacionado='$sector' WHERE id=$id";
    if (mysqli_query($conexion, $sql)) {
        $mensaje = '✅ Universidad actualizada.';
        $tipo_mensaje = 'exito';
    } else {
        $mensaje = '❌ Error: ' . mysqli_error($conexion);
        $tipo_mensaje = 'error';
    }
}

// ELIMINAR
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    mysqli_query($conexion, "DELETE FROM universidades WHERE id=$id");
    $mensaje = '✅ Universidad eliminada.';
    $tipo_mensaje = 'exito';
}

// FILTROS
$busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';
$filtro_sector = isset($_GET['sector']) ? mysqli_real_escape_string($conexion, $_GET['sector']) : '';

$sectores_res = mysqli_query($conexion, "SELECT DISTINCT sector_relacionado FROM universidades ORDER BY sector_relacionado");
$sectores_lista = [];
while ($s = mysqli_fetch_assoc($sectores_res)) $sectores_lista[] = $s['sector_relacionado'];

$where = "WHERE 1=1";
if ($busqueda) $where .= " AND (nombre LIKE '%$busqueda%' OR localidad LIKE '%$busqueda%' OR oferta_educativa LIKE '%$busqueda%')";
if ($filtro_sector) $where .= " AND sector_relacionado='$filtro_sector'";

$universidades = mysqli_query($conexion, "SELECT * FROM universidades $where ORDER BY nombre");
$total = mysqli_num_rows($universidades);
?>

<div class="contenedor-layout">
    <nav class="menu-lateral">
        <h2>MENÚ</h2>
        <ul>
            <li><a href="index.php">🏠 Inicio</a></li>
            <li><a href="alumnos.php">👥 Alumnos</a></li>
            <li><a href="tarjetas.php">🃏 Tarjetas</a></li>
            <li><a href="preguntas.php">❓ Preguntas</a></li>
            <li><a href="resultados.php">📊 Resultados</a></li>
            <li><a href="universidades.php" class="activo">🏫 Universidades</a></li>
            <li><a href="estadisticas.php">📈 Estadísticas</a></li>
        </ul>
    </nav>

    <main class="contenido-derecha" style="align-items:flex-start; padding:30px;">
        <div style="width:100%;">

            <?php if ($mensaje): ?>
                <div class="alerta <?= $tipo_mensaje ?>"><?= $mensaje ?></div>
            <?php endif; ?>

            <div class="preg-header">
                <div>
                    <h2 style="color:#1B2E5E; margin:0;">🏫 Catálogo de Universidades</h2>
                    <p style="color:#888; font-size:13px; margin:4px 0 0;">Universidades disponibles para los alumnos según su perfil vocacional.</p>
                </div>
                <button class="btn-agregar" onclick="abrirModal()">+ Nueva Universidad</button>
            </div>

            <!-- FILTROS -->
            <form method="GET" action="universidades.php">
                <div class="filtros-bar">
                    <input type="text" name="buscar" placeholder="🔍 Buscar universidad..." value="<?= htmlspecialchars($busqueda) ?>">
                    <select name="sector">
                        <option value="">Todos los sectores</option>
                        <?php foreach ($sectores_lista as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" <?= $filtro_sector===$s?'selected':'' ?>><?= htmlspecialchars($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Filtrar</button>
                    <a href="universidades.php" style="font-size:12px; color:#888; text-decoration:none;">✕ Limpiar</a>
                    <span class="badge-total"><?= $total ?> universidad(es)</span>
                </div>
            </form>

            <!-- TABLA -->
            <div class="caja-blanca" style="padding:0; overflow:hidden;">
                <?php if ($total > 0): ?>
                <table class="tabla-preguntas">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Universidad</th>
                            <th>Localidad</th>
                            <th>Oferta Educativa</th>
                            <th>Sector</th>
                            <th>Sitio Web</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = mysqli_fetch_assoc($universidades)): ?>
                        <tr>
                            <td style="color:#aaa; font-size:12px;"><?= $u['id'] ?></td>
                            <td><strong><?= htmlspecialchars($u['nombre']) ?></strong></td>
                            <td style="font-size:12px; color:#666;"><?= htmlspecialchars($u['localidad']) ?></td>
                            <td style="font-size:12px; max-width:200px;"><?= htmlspecialchars($u['oferta_educativa']) ?></td>
                            <td><span class="tag-sector"><?= htmlspecialchars($u['sector_relacionado']) ?></span></td>
                            <td>
                                <?php if ($u['sitio_web']): ?>
                                    <a href="<?= htmlspecialchars($u['sitio_web']) ?>" target="_blank" class="link-web">🔗 Ver sitio</a>
                                <?php endif; ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <button class="btn-editar" onclick="abrirEditar(
                                    <?= $u['id'] ?>,
                                    <?= htmlspecialchars(json_encode($u['nombre'])) ?>,
                                    <?= htmlspecialchars(json_encode($u['localidad'])) ?>,
                                    <?= htmlspecialchars(json_encode($u['sitio_web'])) ?>,
                                    <?= htmlspecialchars(json_encode($u['oferta_educativa'])) ?>,
                                    <?= htmlspecialchars(json_encode($u['logo_url'])) ?>,
                                    <?= htmlspecialchars(json_encode($u['sector_relacionado'])) ?>
                                )">✏️ Editar</button>
                                <a href="universidades.php?eliminar=<?= $u['id'] ?>" onclick="return confirm('¿Eliminar esta universidad?')">
                                    <button class="btn-eliminar">🗑️ Eliminar</button>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="sin-resultados"><div style="font-size:40px;">🏫</div><p>No se encontraron universidades.</p></div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<!-- MODAL AGREGAR -->
<div class="modal-overlay" id="modal-agregar">
    <div class="modal-caja">
        <h3>➕ Nueva Universidad</h3>
        <form method="POST" action="universidades.php">
            <input type="hidden" name="accion" value="agregar">
            <div class="form-grupo">
                <label>Nombre *</label>
                <input type="text" name="nombre" placeholder="Ej: BUAP - Complejo Regional Norte" required>
            </div>
            <div class="form-grupo">
                <label>Localidad</label>
                <input type="text" name="localidad" placeholder="Ej: Puebla, Pue.">
            </div>
            <div class="form-grupo">
                <label>Sitio Web</label>
                <input type="text" name="sitio_web" placeholder="https://www.ejemplo.edu.mx">
            </div>
            <div class="form-grupo">
                <label>Oferta Educativa</label>
                <textarea name="oferta_educativa" placeholder="Ej: Ingeniería en Software, Medicina, Derecho..."></textarea>
            </div>
            <div class="form-grupo">
                <label>Sector Relacionado</label>
                <input type="text" name="sector_relacionado" placeholder="Ej: Ingenierías y Tecnología" list="lista-sectores">
                <datalist id="lista-sectores">
                    <?php foreach ($sectores_lista as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="form-grupo">
                <label>URL del Logo (opcional)</label>
                <input type="text" name="logo_url" placeholder="https://...">
            </div>
            <div class="form-acciones">
                <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-guardar">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal-overlay" id="modal-editar">
    <div class="modal-caja">
        <h3>✏️ Editar Universidad</h3>
        <form method="POST" action="universidades.php">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="editar-id">
            <div class="form-grupo">
                <label>Nombre *</label>
                <input type="text" name="nombre" id="editar-nombre" required>
            </div>
            <div class="form-grupo">
                <label>Localidad</label>
                <input type="text" name="localidad" id="editar-localidad">
            </div>
            <div class="form-grupo">
                <label>Sitio Web</label>
                <input type="text" name="sitio_web" id="editar-web">
            </div>
            <div class="form-grupo">
                <label>Oferta Educativa</label>
                <textarea name="oferta_educativa" id="editar-oferta"></textarea>
            </div>
            <div class="form-grupo">
                <label>Sector Relacionado</label>
                <input type="text" name="sector_relacionado" id="editar-sector" list="lista-sectores">
            </div>
            <div class="form-grupo">
                <label>URL del Logo</label>
                <input type="text" name="logo_url" id="editar-logo">
            </div>
            <div class="form-acciones">
                <button type="button" class="btn-cancelar" onclick="cerrarModalEditar()">Cancelar</button>
                <button type="submit" class="btn-guardar">Actualizar</button>
            </div>
        </form>
    </div>
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
            <div id="notif-lista-panel"><p style="color:#aaa; text-align:center;">Cargando...</p></div>
        </div>
    </div>
    <div id="panel-accesos" style="display:none;">
        <div class="toolbar-panel-header">⚡ Accesos rápidos <button onclick="cerrarPanel()">✕</button></div>
        <div class="toolbar-panel-body">
            <a href="alumnos.php" style="text-decoration:none; color:inherit;"><div class="toolbar-item-row">👥 Alumnos</div></a>
            <a href="tarjetas.php" style="text-decoration:none; color:inherit;"><div class="toolbar-item-row">🃏 Tarjetas</div></a>
            <a href="preguntas.php" style="text-decoration:none; color:inherit;"><div class="toolbar-item-row">❓ Preguntas</div></a>
            <a href="resultados.php" style="text-decoration:none; color:inherit;"><div class="toolbar-item-row">📊 Resultados</div></a>
            <a href="universidades.php" style="text-decoration:none; color:inherit;"><div class="toolbar-item-row">🏫 Universidades</div></a>
        </div>
    </div>
</div>

<script>
function abrirModal() { document.getElementById('modal-agregar').classList.add('activo'); }
function cerrarModal() { document.getElementById('modal-agregar').classList.remove('activo'); }
function abrirEditar(id, nombre, localidad, web, oferta, logo, sector) {
    document.getElementById('editar-id').value = id;
    document.getElementById('editar-nombre').value = nombre;
    document.getElementById('editar-localidad').value = localidad;
    document.getElementById('editar-web').value = web;
    document.getElementById('editar-oferta').value = oferta;
    document.getElementById('editar-logo').value = logo;
    document.getElementById('editar-sector').value = sector;
    document.getElementById('modal-editar').classList.add('activo');
}
function cerrarModalEditar() { document.getElementById('modal-editar').classList.remove('activo'); }
document.getElementById('modal-agregar').addEventListener('click', function(e) { if(e.target===this) cerrarModal(); });
document.getElementById('modal-editar').addEventListener('click', function(e) { if(e.target===this) cerrarModalEditar(); });

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
