<?php include 'conexion.php'; include 'auth.php'; ?>
<?php
// ACCIONES CRUD
$mensaje = '';
$tipo_mensaje = '';

// AGREGAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $texto = mysqli_real_escape_string($conexion, trim($_POST['texto']));
    $sector = mysqli_real_escape_string($conexion, trim($_POST['sector']));
    $carrera = mysqli_real_escape_string($conexion, trim($_POST['carrera']));
    $imagen_url = mysqli_real_escape_string($conexion, trim($_POST['imagen_url']));

    if ($texto && $sector && $carrera) {
        $sql = "INSERT INTO tarjetas (texto, sector, carrera, imagen_url) VALUES ('$texto', '$sector', '$carrera', '$imagen_url')";
        if (mysqli_query($conexion, $sql)) {
            $mensaje = '✅ Pregunta agregada correctamente.';
            $tipo_mensaje = 'exito';
        } else {
            $mensaje = '❌ Error al agregar: ' . mysqli_error($conexion);
            $tipo_mensaje = 'error';
        }
    } else {
        $mensaje = '⚠️ Completa todos los campos obligatorios.';
        $tipo_mensaje = 'advertencia';
    }
}

// EDITAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id = (int)$_POST['id'];
    $texto = mysqli_real_escape_string($conexion, trim($_POST['texto']));
    $sector = mysqli_real_escape_string($conexion, trim($_POST['sector']));
    $carrera = mysqli_real_escape_string($conexion, trim($_POST['carrera']));
    $imagen_url = mysqli_real_escape_string($conexion, trim($_POST['imagen_url']));

    $sql = "UPDATE tarjetas SET texto='$texto', sector='$sector', carrera='$carrera', imagen_url='$imagen_url' WHERE id=$id";
    if (mysqli_query($conexion, $sql)) {
        $mensaje = '✅ Pregunta actualizada correctamente.';
        $tipo_mensaje = 'exito';
    } else {
        $mensaje = '❌ Error al editar: ' . mysqli_error($conexion);
        $tipo_mensaje = 'error';
    }
}

// ELIMINAR
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    if (mysqli_query($conexion, "DELETE FROM tarjetas WHERE id=$id")) {
        $mensaje = '✅ Pregunta eliminada.';
        $tipo_mensaje = 'exito';
    } else {
        $mensaje = '❌ Error al eliminar.';
        $tipo_mensaje = 'error';
    }
}

// OBTENER SECTORES Y CARRERAS ÚNICOS PARA LOS SELECTS
$sectores_res = mysqli_query($conexion, "SELECT DISTINCT sector FROM tarjetas ORDER BY sector");
$sectores_lista = [];
while ($s = mysqli_fetch_assoc($sectores_res)) $sectores_lista[] = $s['sector'];

$carreras_res = mysqli_query($conexion, "SELECT DISTINCT carrera FROM tarjetas ORDER BY carrera");
$carreras_lista = [];
while ($c = mysqli_fetch_assoc($carreras_res)) $carreras_lista[] = $c['carrera'];

// FILTROS
$filtro_sector = isset($_GET['sector']) ? mysqli_real_escape_string($conexion, $_GET['sector']) : '';
$filtro_carrera = isset($_GET['carrera']) ? mysqli_real_escape_string($conexion, $_GET['carrera']) : '';
$busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';

$where = "WHERE 1=1";
if ($filtro_sector) $where .= " AND sector = '$filtro_sector'";
if ($filtro_carrera) $where .= " AND carrera = '$filtro_carrera'";
if ($busqueda) $where .= " AND texto LIKE '%$busqueda%'";

$preguntas = mysqli_query($conexion, "SELECT * FROM tarjetas $where ORDER BY sector, carrera, id");
$total = mysqli_num_rows($preguntas);

// PREGUNTA A EDITAR
$editar = null;
if (isset($_GET['editar'])) {
    $id_editar = (int)$_GET['editar'];
    $res = mysqli_query($conexion, "SELECT * FROM tarjetas WHERE id=$id_editar");
    $editar = mysqli_fetch_assoc($res);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchVoc - Preguntas</title>
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
        .tag-sector { background:#e8f0fe; color:#1B2E5E; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; }
        .tag-carrera { background:#f0fae8; color:#4a7c00; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; }
        .btn-editar { background:#f0a500; color:#fff; border:none; padding:5px 12px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:600; }
        .btn-eliminar { background:#ff4d4d; color:#fff; border:none; padding:5px 12px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:600; }
        .btn-editar:hover { background:#d48f00; }
        .btn-eliminar:hover { background:#cc0000; }
        .modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center; }
        .modal-overlay.activo { display:flex; }
        .modal-caja { background:#fff; border-radius:16px; padding:30px; width:100%; max-width:550px; max-height:90vh; overflow-y:auto; box-shadow:0 10px 40px rgba(0,0,0,0.2); }
        .modal-caja h3 { color:#1B2E5E; margin-bottom:20px; font-size:18px; }
        .form-grupo { margin-bottom:16px; }
        .form-grupo label { display:block; font-size:13px; font-weight:600; color:#444; margin-bottom:6px; }
        .form-grupo input, .form-grupo textarea, .form-grupo select { width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-size:13px; font-family:'Montserrat',sans-serif; box-sizing:border-box; }
        .form-grupo textarea { height:90px; resize:vertical; }
        .form-acciones { display:flex; gap:10px; justify-content:flex-end; margin-top:10px; }
        .btn-guardar { background:#1B2E5E; color:#fff; border:none; padding:10px 24px; border-radius:8px; font-weight:700; cursor:pointer; }
        .btn-cancelar { background:#eee; color:#444; border:none; padding:10px 24px; border-radius:8px; font-weight:600; cursor:pointer; }
        .alerta { padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:13px; font-weight:600; }
        .alerta.exito { background:#e6f9e6; color:#267326; border:1px solid #b3e6b3; }
        .alerta.error { background:#ffe6e6; color:#cc0000; border:1px solid #ffb3b3; }
        .alerta.advertencia { background:#fff8e1; color:#a07000; border:1px solid #ffe082; }
        .sin-resultados { text-align:center; padding:40px; color:#aaa; font-size:14px; }
    </style>
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
            <li><a href="preguntas.php" class="activo">❓ Preguntas</a></li>
            <li><a href="resultados.php">📊 Resultados</a></li>
            <li><a href="universidades.php">🏫 Universidades</a></li>
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
                    <h2 style="color:#1B2E5E; margin:0;">❓ Gestión de Preguntas</h2>
                    <p style="color:#888; font-size:13px; margin:4px 0 0;">Preguntas del test vocacional de la app móvil.</p>
                </div>
                <button class="btn-agregar" onclick="abrirModal()">➕ Nueva pregunta</button>
            </div>

            <!-- FILTROS -->
            <form method="GET" action="preguntas.php">
                <div class="filtros-bar">
                    <input type="text" name="buscar" placeholder="🔍 Buscar pregunta..." value="<?= htmlspecialchars($busqueda) ?>">
                    <select name="sector">
                        <option value="">Todos los sectores</option>
                        <?php foreach ($sectores_lista as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" <?= $filtro_sector === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="carrera">
                        <option value="">Todas las carreras</option>
                        <?php foreach ($carreras_lista as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= $filtro_carrera === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Filtrar</button>
                    <a href="preguntas.php" style="font-size:12px; color:#888; text-decoration:none;">✕ Limpiar</a>
                    <span class="badge-total"><?= $total ?> pregunta(s)</span>
                </div>
            </form>

            <!-- TABLA -->
            <div class="caja-blanca" style="padding:0; overflow:hidden;">
                <?php if ($total > 0): ?>
                <table class="tabla-preguntas">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pregunta</th>
                            <th>Sector</th>
                            <th>Carrera</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($p = mysqli_fetch_assoc($preguntas)): ?>
                        <tr>
                            <td style="color:#aaa; font-size:12px;"><?= $p['id'] ?></td>
                            <td><?= htmlspecialchars($p['texto']) ?></td>
                            <td><span class="tag-sector"><?= htmlspecialchars($p['sector']) ?></span></td>
                            <td><span class="tag-carrera"><?= htmlspecialchars($p['carrera']) ?></span></td>
                            <td style="white-space:nowrap;">
                                <button class="btn-editar" onclick="abrirEditar(<?= $p['id'] ?>, <?= htmlspecialchars(json_encode($p['texto'])) ?>, <?= htmlspecialchars(json_encode($p['sector'])) ?>, <?= htmlspecialchars(json_encode($p['carrera'])) ?>, <?= htmlspecialchars(json_encode($p['imagen_url'])) ?>)">✏️ Editar</button>
                                <a href="preguntas.php?eliminar=<?= $p['id'] ?>" onclick="return confirm('¿Eliminar esta pregunta?')">
                                    <button class="btn-eliminar">🗑️ Eliminar</button>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="sin-resultados">
                        <div style="font-size:40px;">❓</div>
                        <p>No se encontraron preguntas.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<!-- MODAL AGREGAR -->
<div class="modal-overlay" id="modal-agregar">
    <div class="modal-caja">
        <h3>➕ Nueva Pregunta</h3>
        <form method="POST" action="preguntas.php">
            <input type="hidden" name="accion" value="agregar">
            <div class="form-grupo">
                <label>Texto de la pregunta *</label>
                <textarea name="texto" placeholder="¿Te gustaría...?" required></textarea>
            </div>
            <div class="form-grupo">
                <label>Sector *</label>
                <input type="text" name="sector" placeholder="Ej: Ingenierías y Tecnología" list="lista-sectores" required>
                <datalist id="lista-sectores">
                    <?php foreach ($sectores_lista as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="form-grupo">
                <label>Carrera *</label>
                <input type="text" name="carrera" placeholder="Ej: Ingeniería de Software" list="lista-carreras" required>
                <datalist id="lista-carreras">
                    <?php foreach ($carreras_lista as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="form-grupo">
                <label>URL de imagen (opcional)</label>
                <input type="text" name="imagen_url" placeholder="https://...">
            </div>
            <div class="form-acciones">
                <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-guardar">Guardar pregunta</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal-overlay" id="modal-editar">
    <div class="modal-caja">
        <h3>✏️ Editar Pregunta</h3>
        <form method="POST" action="preguntas.php">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="editar-id">
            <div class="form-grupo">
                <label>Texto de la pregunta *</label>
                <textarea name="texto" id="editar-texto" required></textarea>
            </div>
            <div class="form-grupo">
                <label>Sector *</label>
                <input type="text" name="sector" id="editar-sector" list="lista-sectores" required>
            </div>
            <div class="form-grupo">
                <label>Carrera *</label>
                <input type="text" name="carrera" id="editar-carrera" list="lista-carreras" required>
            </div>
            <div class="form-grupo">
                <label>URL de imagen (opcional)</label>
                <input type="text" name="imagen_url" id="editar-imagen">
            </div>
            <div class="form-acciones">
                <button type="button" class="btn-cancelar" onclick="cerrarModalEditar()">Cancelar</button>
                <button type="submit" class="btn-guardar">Actualizar pregunta</button>
            </div>
        </form>
    </div>
</div>

<!-- BARRA LATERAL DE HERRAMIENTAS -->
<div class="toolbar">
    <button class="toolbar-btn" onclick="abrirPanel('perfil')" title="Perfil">👤</button>
    <button class="toolbar-btn" id="tb-notif" onclick="abrirPanel('notificaciones')" title="Notificaciones">
        🔔
        <span class="toolbar-badge" id="tb-badge" style="display:none;">0</span>
    </button>
    <button class="toolbar-btn" onclick="abrirPanel('configuracion')" title="Configuración">⚙️</button>
    <button class="toolbar-btn" onclick="abrirPanel('accesos')" title="Accesos rápidos">⚡</button>
</div>

<div class="toolbar-panel" id="toolbar-panel">
    <div id="panel-perfil" style="display:none;">
        <div class="toolbar-panel-header">👤 Mi Perfil <button onclick="cerrarPanel()">✕</button></div>
        <div class="toolbar-panel-body">
            <div style="text-align:center; margin-bottom:20px;">
                <div style="font-size:50px;">👤</div>
                <div style="font-weight:700; color:#1B2E5E;"><?= $_SESSION['nombre'] ?? 'Administrador' ?></div>
                <div style="color:#888; font-size:13px;">Panel Administrativo</div>
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
    <div id="panel-configuracion" style="display:none;">
        <div class="toolbar-panel-header">⚙️ Configuración <button onclick="cerrarPanel()">✕</button></div>
        <div class="toolbar-panel-body">
            <div class="toolbar-item-row">🎨 Tema del panel</div>
            <div class="toolbar-item-row">🔔 Notificaciones</div>
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
            <a href="estadisticas.php" style="text-decoration:none; color:inherit;"><div class="toolbar-item-row">📈 Estadísticas</div></a>
        </div>
    </div>
</div>

<script>
// MODALES
function abrirModal() {
    document.getElementById('modal-agregar').classList.add('activo');
}
function cerrarModal() {
    document.getElementById('modal-agregar').classList.remove('activo');
}
function abrirEditar(id, texto, sector, carrera, imagen) {
    document.getElementById('editar-id').value = id;
    document.getElementById('editar-texto').value = texto;
    document.getElementById('editar-sector').value = sector;
    document.getElementById('editar-carrera').value = carrera;
    document.getElementById('editar-imagen').value = imagen;
    document.getElementById('modal-editar').classList.add('activo');
}
function cerrarModalEditar() {
    document.getElementById('modal-editar').classList.remove('activo');
}

// Cerrar modal al hacer clic fuera
document.getElementById('modal-agregar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
document.getElementById('modal-editar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalEditar();
});

// TOOLBAR
let panelActivo = null;
function abrirPanel(tipo) {
    const panel = document.getElementById('toolbar-panel');
    const todos = ['perfil','notificaciones','configuracion','accesos'];
    if (panelActivo === tipo) { cerrarPanel(); return; }
    todos.forEach(t => document.getElementById('panel-' + t).style.display = 'none');
    document.getElementById('panel-' + tipo).style.display = 'block';
    panel.classList.add('abierto');
    panelActivo = tipo;
    if (tipo === 'notificaciones') cargarNotificacionesPanel();
}
function cerrarPanel() {
    document.getElementById('toolbar-panel').classList.remove('abierto');
    panelActivo = null;
}
function cargarNotificacionesPanel() {
    fetch('notificaciones.php')
        .then(r => r.json())
        .then(data => {
            const lista = document.getElementById('notif-lista-panel');
            const badge = document.getElementById('tb-badge');
            if (data.total > 0) {
                badge.style.display = 'flex';
                badge.textContent = data.total;
                lista.innerHTML = data.items.map(n => `
                    <div class="toolbar-item-row">
                        <span style="font-size:18px;">${n.icono}</span>
                        <div><div>${n.mensaje}</div><div style="color:#aaa;font-size:11px;margin-top:3px;">${n.tiempo}</div></div>
                    </div>`).join('');
            } else {
                badge.style.display = 'none';
                lista.innerHTML = '<p style="color:#aaa;text-align:center;padding:20px;">✅ Sin notificaciones nuevas</p>';
            }
        });
}
cargarNotificacionesPanel();
setInterval(cargarNotificacionesPanel, 30000);
</script>

</body>
</html>
