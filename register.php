<?php
// Archivo: src/register.php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Redirigir si ya está logueado
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$formData = [];

// Procesar registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $formData = [
        'nombre' => sanitizeInput($_POST['nombre']),
        'apellido' => sanitizeInput($_POST['apellido']),
        'email' => sanitizeInput($_POST['email']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password'],
        'telefono' => sanitizeInput($_POST['telefono']),
        'tipo_usuario' => sanitizeInput($_POST['tipo_usuario'])
    ];
    
    // Validar datos
    $errors = validateRegistrationData($formData);
    
    if (empty($errors)) {
        $result = registerUser(
            $formData['nombre'],
            $formData['apellido'],
            $formData['email'],
            $formData['password'],
            $formData['telefono'],
            $formData['tipo_usuario']
        );
        
        if ($result['success']) {
            $success = 'Registro exitoso. ¡Bienvenido a ' . SITE_NAME . '!';
            
            // Auto-login después del registro
            $loginResult = loginUser($formData['email'], $formData['password']);
            if ($loginResult['success']) {
                header('Location: dashboard.php?welcome=1');
                exit;
            }
        } else {
            $error = $result['message'];
        }
    } else {
        $error = implode(', ', $errors);
    }
}

// Obtener categorías para mostrar ejemplos según tipo de usuario
try {
    $categorias = getCategorias(6);
} catch (Exception $e) {
    $categorias = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card register-card">
            <div class="auth-header">
                <h1 class="auth-logo">🚀 <?php echo SITE_NAME; ?></h1>
                <h2>Crear Cuenta</h2>
                <p>Únete a la comunidad de servicios</p>
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

            <form method="POST" class="auth-form register-form">
                <!-- Tipo de Usuario -->
                <div class="form-group">
                    <label for="tipo_usuario">¿Qué tipo de cuenta necesitas?</label>
                    <div class="user-type-selector">
                        <label class="user-type-option">
                            <input 
                                type="radio" 
                                name="tipo_usuario" 
                                value="cliente" 
                                <?php echo ($formData['tipo_usuario'] ?? 'cliente') === 'cliente' ? 'checked' : ''; ?>
                                required
                            >
                            <div class="user-type-card">
                                <span class="user-type-icon">👤</span>
                                <h3>Cliente</h3>
                                <p>Busco servicios y profesionales</p>
                            </div>
                        </label>
                        <label class="user-type-option">
                            <input 
                                type="radio" 
                                name="tipo_usuario" 
                                value="proveedor"
                                <?php echo ($formData['tipo_usuario'] ?? '') === 'proveedor' ? 'checked' : ''; ?>
                            >
                            <div class="user-type-card">
                                <span class="user-type-icon">🔧</span>
                                <h3>Proveedor</h3>
                                <p>Ofrezco mis servicios profesionales</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Datos Personales -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            required 
                            value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>"
                            placeholder="Tu nombre"
                        >
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido *</label>
                        <input 
                            type="text" 
                            id="apellido" 
                            name="apellido" 
                            required 
                            value="<?php echo htmlspecialchars($formData['apellido'] ?? ''); ?>"
                            placeholder="Tu apellido"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                        placeholder="tu@email.com"
                    >
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input 
                        type="tel" 
                        id="telefono" 
                        name="telefono" 
                        value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>"
                        placeholder="+598 99 123 456"
                    >
                </div>

                <!-- Contraseñas -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            minlength="6"
                            placeholder="Mínimo 6 caracteres"
                        >
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña *</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                            placeholder="Repite tu contraseña"
                        >
                    </div>
                </div>

                <!-- Términos y Condiciones -->
                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="terms" required>
                        <span class="checkmark"></span>
                        Acepto los <a href="#" class="link">términos y condiciones</a> y la 
                        <a href="#" class="link">política de privacidad</a>
                    </label>
                </div>

                <button type="submit" name="register" class="btn btn-primary btn-full">
                    ✨ Crear Mi Cuenta
                </button>
            </form>

            <div class="auth-links">
                <a href="login.php" class="auth-link">
                    ¿Ya tienes cuenta? <strong>Inicia sesión aquí</strong>
                </a>
                <a href="index.php" class="auth-link">
                    ← Volver al inicio
                </a>
            </div>
        </div>

        <div class="auth-info register-info">
            <div class="user-type-benefits">
                <div class="benefit-section cliente-benefits">
                    <h3>👤 Como Cliente podrás:</h3>
                    <ul>
                        <li>🔍 Buscar servicios por categoría y ubicación</li>
                        <li>⭐ Ver calificaciones y reseñas reales</li>
                        <li>💬 Chatear directamente con proveedores</li>
                        <li>📅 Programar citas y reservas fácilmente</li>
                        <li>📱 Recibir notificaciones del estado</li>
                        <li>💳 Gestionar pagos de forma segura</li>
                    </ul>
                </div>

                <div class="benefit-section proveedor-benefits" style="display: none;">
                    <h3>🔧 Como Proveedor podrás:</h3>
                    <ul>
                        <li>📝 Publicar tus servicios y tarifas</li>
                        <li>📸 Mostrar tu portafolio con imágenes</li>
                        <li>📅 Gestionar tu disponibilidad</li>
                        <li>💬 Comunicarte con clientes interesados</li>
                        <li>⭐ Recibir calificaciones y construir reputación</li>
                        <li>📊 Ver estadísticas de tu negocio</li>
                    </ul>
                </div>
            </div>

            <?php if (!empty($categorias)): ?>
            <div class="categories-preview">
                <h3>🛠️ Categorías Disponibles:</h3>
                <div class="categories-list">
                    <?php foreach ($categorias as $categoria): ?>
                    <span class="category-tag">
                        <?php if (strpos($categoria['icono'], 'fa-') === 0): ?>
                            <i class="fas <?php echo htmlspecialchars($categoria['icono']); ?>"></i>
                        <?php else: ?>
                            <?php echo htmlspecialchars($categoria['icono'] ?: '🔧'); ?>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="security-info">
                <h3>🛡️ Tu Seguridad es Nuestra Prioridad</h3>
                <ul>
                    <li>✅ Verificación de identidad de proveedores</li>
                    <li>✅ Sistema de calificaciones transparente</li>
                    <li>✅ Comunicación segura dentro de la plataforma</li>
                    <li>✅ Soporte 24/7 para resolver dudas</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Mostrar beneficios según tipo de usuario seleccionado
        document.querySelectorAll('input[name="tipo_usuario"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const clienteBenefits = document.querySelector('.cliente-benefits');
                const proveedorBenefits = document.querySelector('.proveedor-benefits');
                
                if (this.value === 'cliente') {
                    clienteBenefits.style.display = 'block';
                    proveedorBenefits.style.display = 'none';
                } else {
                    clienteBenefits.style.display = 'none';
                    proveedorBenefits.style.display = 'block';
                }
            });
        });

        // Validación de contraseñas
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });

        // Validación del formulario
        document.querySelector('.register-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
                return;
            }
        });

        // Formateo automático del teléfono
        document.getElementById('telefono').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.startsWith('598')) {
                    value = '+' + value;
                } else if (!value.startsWith('0')) {
                    value = '+598 ' + value;
                }
            }
            this.value = value;
        });
    </script>
</body>
</html>