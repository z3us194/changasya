<?php
// Archivo: src/login.php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Redirigir si ya está logueado
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } else {
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            // Redirigir según tipo de usuario
            switch ($_SESSION['user_type']) {
                case 'administrador':
                    header('Location: admin/dashboard.php');
                    break;
                case 'proveedor':
                    header('Location: proveedor/dashboard.php');
                    break;
                default:
                    header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

// Verificación de email si viene token
if (isset($_GET['verify']) && !empty($_GET['token'])) {
    $result = verifyEmail($_GET['token']);
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-logo">🚀 <?php echo SITE_NAME; ?></h1>
                <h2>Iniciar Sesión</h2>
                <p>Accede a tu cuenta para gestionar servicios</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">❌</span>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">✅</span>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        placeholder="tu@email.com"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="Tu contraseña"
                    >
                </div>

                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember" value="1">
                        <span class="checkmark"></span>
                        Recordarme
                    </label>
                </div>

                <button type="submit" name="login" class="btn btn-primary btn-full">
                    🔑 Iniciar Sesión
                </button>
            </form>

            <div class="auth-divider">
                <span>o</span>
            </div>

            <div class="auth-demo">
                <h3>🧪 Cuentas de Demostración</h3>
                <div class="demo-accounts">
                    <div class="demo-account">
                        <strong>👑 Administrador</strong>
                        <p>Email: admin@changasya.com</p>
                        <p>Pass: admin123</p>
                    </div>
                    <div class="demo-account">
                        <strong>🔧 Proveedor</strong>
                        <p>Email: proveedor@demo.com</p>
                        <p>Pass: demo123</p>
                    </div>
                    <div class="demo-account">
                        <strong>👤 Cliente</strong>
                        <p>Email: cliente@demo.com</p>
                        <p>Pass: demo123</p>
                    </div>
                </div>
            </div>

            <div class="auth-links">
                <a href="register.php" class="auth-link">
                    ¿No tienes cuenta? <strong>Regístrate aquí</strong>
                </a>
                <a href="forgot-password.php" class="auth-link">
                    ¿Olvidaste tu contraseña?
                </a>
                <a href="index.php" class="auth-link">
                    ← Volver al inicio
                </a>
            </div>
        </div>

        <div class="auth-info">
            <h3>✨ ¿Por qué usar <?php echo SITE_NAME; ?>?</h3>
            <div class="features-list">
                <div class="feature-item">
                    <span class="feature-icon">🛡️</span>
                    <div>
                        <strong>Seguro y Confiable</strong>
                        <p>Todos los usuarios son verificados</p>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">💬</span>
                    <div>
                        <strong>Comunicación Directa</strong>
                        <p>Chat integrado con proveedores</p>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">⭐</span>
                    <div>
                        <strong>Calidad Garantizada</strong>
                        <p>Sistema de calificaciones y reseñas</p>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">📅</span>
                    <div>
                        <strong>Gestión Fácil</strong>
                        <p>Reservas y citas simplificadas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-fill demo accounts
        document.querySelectorAll('.demo-account').forEach(account => {
            account.addEventListener('click', function() {
                const email = this.querySelector('p').textContent.replace('Email: ', '');
                const pass = this.querySelectorAll('p')[1].textContent.replace('Pass: ', '');
                
                document.getElementById('email').value = email;
                document.getElementById('password').value = pass;
            });
        });

        // Form validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Por favor completa todos los campos');
            }
        });
    </script>
</body>
</html>