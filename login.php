<?php
include 'conexion.php';
session_start();

$modo = isset($_GET['modo']) ? $_GET['modo'] : 'login';
$error = '';
$exito = '';

// LOGIN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'login') {
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $password = mysqli_real_escape_string($conexion, $_POST['password']);
    $sql = "SELECT * FROM usuarios WHERE (nombre='$usuario' OR correo='$usuario') AND password='$password' AND rol_id=1";
    $resultado = mysqli_query($conexion, $sql);
    if (mysqli_num_rows($resultado) > 0) {
        $user = mysqli_fetch_assoc($resultado);
        $_SESSION['admin'] = $user['nombre'];
        header('Location: index.php');
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
        $modo = 'login';
    }
}

// REGISTRO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'registro') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $password = mysqli_real_escape_string($conexion, $_POST['password']);
    $confirmar = $_POST['confirmar'];

    if ($password !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
        $modo = 'registro';
    } else {
        $check = mysqli_query($conexion, "SELECT id FROM usuarios WHERE correo='$correo'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Este correo ya está registrado.";
            $modo = 'registro';
        } else {
            $sql = "INSERT INTO usuarios (nombre, correo, password, rol_id, activo) VALUES ('$nombre', '$correo', '$password', 1, 1)";
            if (mysqli_query($conexion, $sql)) {
                $exito = "¡Cuenta creada! Ya puedes iniciar sesión.";
                $modo = 'login';
            } else {
                $error = "Error al crear la cuenta.";
                $modo = 'registro';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchVoc - <?php echo $modo == 'login' ? 'Iniciar Sesión' : 'Registro'; ?></title>
    <link rel="stylesheet" href="estilos.css/estilos.css">
</head>
<body class="login-page" style="padding:0; margin:0;">

<div class="login-split">

    <!-- LADO IZQUIERDO - IMAGEN -->
    <div class="login-imagen">
        <div class="login-overlay">
            <h1>MATCHVOC</h1>
            <p>Guiando el talento hacia el futuro</p>
        </div>
        <img src="IMAGEN1.jpeg" alt="MatchVoc">
    </div>

    <!-- LADO DERECHO - FORMULARIO -->
    <div class="login-formulario">
        <div class="login-form-inner">

            <?php if ($modo == 'login'): ?>
            <!-- ===== LOGIN ===== -->
            <div style="margin-bottom: 25px;">
                <h2 style="color:#1B2E5E; font-size:1.8em; font-weight:700; margin:0;">Bienvenido</h2>
                <p style="color:#888; margin:5px 0 0; font-size:0.9em;">Ingresa tus credenciales para continuar</p>
            </div>

            <?php if ($error): ?>
            <div class="alerta-error">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($exito): ?>
            <div class="alerta-exito">✅ <?php echo $exito; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="accion" value="login">
                <div class="input-group">
                    <label>👤 Usuario o correo</label>
                    <input type="text" name="usuario" placeholder="Ingresa tu usuario o correo" required>
                </div>
                <div class="input-group">
                    <label>🔒 Contraseña</label>
                    <input type="password" name="password" placeholder="Ingresa tu contraseña" required>
                </div>
                <button type="submit" class="btn-entrar">Entrar →</button>
            </form>

            <!-- BOTÓN GOOGLE -->
            <div style="margin: 20px 0; text-align:center; color:#aaa; font-size:13px;">— o continúa con —</div>
            <button class="btn-google" onclick="alert('Google OAuth próximamente disponible')">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="20">
                Continuar con Google
            </button>

            <p style="text-align:center; margin-top:20px; font-size:0.85em; color:#888;">
                ¿No tienes cuenta? 
                <a href="login.php?modo=registro" style="color:#1B2E5E; font-weight:700;">Regístrate</a>
            </p>

            <?php else: ?>
            <!-- ===== REGISTRO ===== -->
            <div style="margin-bottom: 25px;">
                <h2 style="color:#1B2E5E; font-size:1.8em; font-weight:700; margin:0;">Crear cuenta</h2>
                <p style="color:#888; margin:5px 0 0; font-size:0.9em;">Registro de administrador MatchVoc</p>
            </div>

            <?php if ($error): ?>
            <div class="alerta-error">❌ <?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="accion" value="registro">
                <div class="input-group">
                    <label>👤 Nombre completo</label>
                    <input type="text" name="nombre" placeholder="Tu nombre completo" required>
                </div>
                <div class="input-group">
                    <label>📧 Correo electrónico</label>
                    <input type="email" name="correo" placeholder="tu@correo.com" required>
                </div>
                <div class="input-group">
                    <label>🔒 Contraseña</label>
                    <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
                </div>
                <div class="input-group">
                    <label>🔒 Confirmar contraseña</label>
                    <input type="password" name="confirmar" placeholder="Repite tu contraseña" required>
                </div>
                <button type="submit" class="btn-entrar">Crear cuenta →</button>
            </form>

            <p style="text-align:center; margin-top:20px; font-size:0.85em; color:#888;">
                ¿Ya tienes cuenta? 
                <a href="login.php" style="color:#1B2E5E; font-weight:700;">Inicia sesión</a>
            </p>

            <?php endif; ?>

            <p style="text-align:center; margin-top:15px; color:#ccc; font-size:0.75em;">
                MatchVoc · Bachillerato Digital 73
            </p>
        </div>
    </div>

</div>

</body>
</html>