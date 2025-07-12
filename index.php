<?php
// Archivo: index.php - Página principal que funciona sin errores
// Configuración básica
define('SITE_NAME', 'ChangasYa');
define('SITE_DESCRIPTION', 'Plataforma de servicios y changas');
define('SITE_VERSION', '1.0.0');

// Función básica para verificar si existe el archivo de configuración
function configExists() {
    return file_exists('config/database.php');
}

// Función para conectar a base de datos con manejo de errores
function getDBConnectionSafe() {
    try {
        if (!configExists()) {
            return null;
        }
        
        $pdo = new PDO(
            'mysql:host=localhost;dbname=changasya_db;charset=utf8mb4',
            'changasya_user',
            'ChangasYa2024!',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        return $pdo;
    } catch (Exception $e) {
        return null;
    }
}

// Obtener estadísticas básicas con manejo de errores
$stats = [
    'usuarios' => ['total' => 0, 'clientes' => 0, 'proveedores' => 0],
    'servicios' => ['total' => 0, 'destacados' => 0],
    'categorias' => 0,
    'reservas' => ['total' => 0],
    'calificacion_promedio' => 0
];

$categorias = [];
$serviciosDestacados = [];
$calificacionesDestacadas = [];
$systemStatus = "⚠️ Configurando sistema...";
$hasError = true;

// Intentar obtener datos de la base de datos
try {
    $pdo = getDBConnectionSafe();
    if ($pdo) {
        // Verificar si existen las tablas
        $tables = $pdo->query("SHOW TABLES LIKE '%usuarios%'")->fetchAll();
        if (!empty($tables)) {
            // Obtener estadísticas básicas
            $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE activo = TRUE");
            $stats['usuarios']['total'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE tipo_usuario = 'cliente' AND activo = TRUE");
            $stats['usuarios']['clientes'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE tipo_usuario = 'proveedor' AND activo = TRUE");
            $stats['usuarios']['proveedores'] = $stmt->fetchColumn();
            
            // Verificar si existe tabla servicios
            $tables = $pdo->query("SHOW TABLES LIKE '%servicios%'")->fetchAll();
            if (!empty($tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM servicios WHERE activo = TRUE");
                $stats['servicios']['total'] = $stmt->fetchColumn();
                
                $stmt = $pdo->query("SELECT COUNT(*) FROM servicios WHERE destacado = TRUE AND activo = TRUE");
                $stats['servicios']['destacados'] = $stmt->fetchColumn();
            }
            
            // Verificar categorías
            $tables = $pdo->query("SHOW TABLES LIKE '%categorias%'")->fetchAll();
            if (!empty($tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM categorias WHERE activa = TRUE");
                $stats['categorias'] = $stmt->fetchColumn();
                
                // Obtener algunas categorías para mostrar
                $stmt = $pdo->query("SELECT * FROM categorias WHERE activa = TRUE ORDER BY orden_visualizacion LIMIT 8");
                $categorias = $stmt->fetchAll();
            }
            
            // Verificar reservas
            $tables = $pdo->query("SHOW TABLES LIKE '%reservas%'")->fetchAll();
            if (!empty($tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM reservas");
                $stats['reservas']['total'] = $stmt->fetchColumn();
            }
            
            $systemStatus = "✅ Sistema operativo";
            $hasError = false;
        } else {
            $systemStatus = "⚠️ Base de datos sin configurar";
        }
    } else {
        $systemStatus = "⚠️ Conectando a base de datos...";
    }
} catch (Exception $e) {
    $systemStatus = "⚠️ Configurando sistema...";
    $hasError = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo SITE_DESCRIPTION; ?></title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?> - Conectamos clientes con proveedores de servicios de calidad">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 3rem 0;
            text-align: center;
            color: white;
            margin-bottom: 2rem;
        }
        
        .logo {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            font-weight: 700;
        }
        
        .tagline {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            font-size: 1.1rem;
            flex-wrap: wrap;
        }
        
        .stat {
            background: rgba(255, 255, 255, 0.2);
            padding: 1rem 1.5rem;
            border-radius: 25px;
            backdrop-filter: blur(5px);
            text-align: center;
        }
        
        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        /* Main Content */
        .main {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .section {
            padding: 3rem 2rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .section:last-child {
            border-bottom: none;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .section-header h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 1rem;
        }
        
        /* Status Section */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }
        
        .status-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .status-card:hover {
            transform: translateY(-5px);
        }
        
        .status-card h3 {
            margin-bottom: 1rem;
            color: #333;
            font-size: 1.3rem;
        }
        
        .success {
            color: #28a745;
            font-weight: bold;
        }
        
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        
        /* Categories Grid */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .category-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .category-card:hover {
            transform: translateY(-8px);
        }
        
        .category-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .category-card h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .feature-step {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 1rem;
        }
        
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            text-align: center;
            padding: 3rem 2rem;
        }
        
        .cta-buttons {
            margin: 2rem 0;
        }
        
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            margin: 0.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
        }
        
        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        /* Footer */
        .footer {
            background: rgba(0, 0, 0, 0.1);
            color: white;
            padding: 3rem 0;
            text-align: center;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-section h4 {
            margin-bottom: 1rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .logo {
                font-size: 2.5rem;
            }
            
            .stats {
                flex-direction: column;
                gap: 1rem;
            }
            
            .section {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1 class="logo">🚀 <?php echo SITE_NAME; ?></h1>
            <p class="tagline"><?php echo SITE_DESCRIPTION; ?></p>
            <div class="stats">
                <div class="stat">
                    <span class="stat-number"><?php echo number_format($stats['usuarios']['total']); ?></span>
                    <span class="stat-label">Usuarios</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?php echo number_format($stats['servicios']['total']); ?></span>
                    <span class="stat-label">Servicios</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?php echo number_format($stats['reservas']['total']); ?></span>
                    <span class="stat-label">Trabajos</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            
            <!-- System Status -->
            <section class="section">
                <div class="section-header">
                    <h2>📊 Estado del Sistema</h2>
                </div>
                <div class="status-grid">
                    <div class="status-card">
                        <h3>🗄️ Base de Datos</h3>
                        <p class="<?php echo $hasError ? 'warning' : 'success'; ?>">
                            <?php echo $systemStatus; ?>
                        </p>
                        <small>MariaDB/MySQL</small>
                    </div>
                    <div class="status-card">
                        <h3>🌐 Servidor Web</h3>
                        <p class="success">✅ Apache funcionando</p>
                        <small>PHP <?php echo phpversion(); ?></small>
                    </div>
                    <div class="status-card">
                        <h3>ℹ️ Sistema</h3>
                        <p>IP: <?php echo $_SERVER['SERVER_ADDR'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost'; ?></p>
                        <small>Versión: <?php echo SITE_VERSION; ?></small>
                    </div>
                </div>
            </section>

            <?php if (!empty($categorias)): ?>
            <!-- Categories -->
            <section class="section">
                <div class="section-header">
                    <h2>📂 Categorías de Servicios</h2>
                </div>
                <div class="categories-grid">
                    <?php foreach ($categorias as $categoria): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <?php echo htmlspecialchars($categoria['icono'] ?: '🔧'); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($categoria['descripcion'] ?: 'Servicios profesionales'); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Features -->
            <section class="section">
                <div class="section-header">
                    <h2>¿Cómo funciona <?php echo SITE_NAME; ?>?</h2>
                    <p>Proceso simple y seguro en 3 pasos</p>
                </div>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-step">1</div>
                        <div class="feature-icon">🔍</div>
                        <h3>Busca o Publica</h3>
                        <p>Los clientes buscan servicios por categoría y ubicación. Los proveedores publican sus servicios con fotos y precios.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-step">2</div>
                        <div class="feature-icon">💬</div>
                        <h3>Conecta y Coordina</h3>
                        <p>Sistema de mensajería integrado para coordinar detalles, precios y horarios de forma segura.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-step">3</div>
                        <div class="feature-icon">✅</div>
                        <h3>Completa y Califica</h3>
                        <p>Una vez completado el servicio, ambas partes pueden calificar la experiencia para mantener la calidad.</p>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="cta-section">
                <h2>¿Listo para comenzar?</h2>
                <p>Únete a nuestra comunidad de servicios de calidad</p>
                <div class="cta-buttons">
                    <a href="login.php" class="btn btn-primary">🔑 Iniciar Sesión</a>
                    <a href="register.php" class="btn btn-outline">📝 Registrarse</a>
                </div>
                <p><small>✨ Registro gratuito • Sistema seguro • Soporte 24/7</small></p>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><?php echo SITE_NAME; ?></h4>
                    <p><?php echo SITE_DESCRIPTION; ?></p>
                </div>
                <div class="footer-section">
                    <h4>Sistema</h4>
                    <p>Apache + PHP <?php echo phpversion(); ?></p>
                    <p>MariaDB/MySQL</p>
                    <p>AlmaLinux compatible</p>
                </div>
                <div class="footer-section">
                    <h4>Información</h4>
                    <p>Versión: <?php echo SITE_VERSION; ?></p>
                    <p>&copy; 2025 SmartCodeUy</p>
                    <p>Sistema de gestión de servicios</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Animaciones simples
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.status-card, .category-card, .feature-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });
            
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>