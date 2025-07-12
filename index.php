<?php
// Archivo: index.php - Página principal básica de ChangasYa
// Configuración básica
define('SITE_NAME', 'ChangasYa');
define('SITE_DESCRIPTION', 'Plataforma de servicios y changas');
define('SITE_VERSION', '1.0.0');

// Verificar si existe la configuración
$configExists = file_exists('config/database.php');
$dbConnected = false;
$stats = ['usuarios' => 0, 'servicios' => 0, 'trabajos' => 0];

if ($configExists) {
    try {
        require_once 'config/database.php';
        $pdo = getDBConnection();
        if ($pdo) {
            $dbConnected = true;
            // Obtener estadísticas básicas
            $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE activo = TRUE");
            $stats['usuarios'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM servicios WHERE activo = TRUE");
            $stats['servicios'] = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM reservas");
            $stats['trabajos'] = $stmt->fetchColumn();
        }
    } catch (Exception $e) {
        $dbConnected = false;
    }
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
            padding: 2rem 0;
            text-align: center;
            color: white;
            margin-bottom: 2rem;
        }
        
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .nav-links {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
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
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .hero {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .stat {
            background: rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            border-radius: 15px;
            backdrop-filter: blur(5px);
            text-align: center;
            min-width: 120px;
        }
        
        .stat-number {
            display: block;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        
        .status-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
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
        
        /* Features */
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
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .feature-card h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            text-align: center;
            padding: 3rem 2rem;
            border-radius: 25px;
            margin: 2rem 0;
        }
        
        .cta-buttons {
            margin: 2rem 0;
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        /* Footer */
        .footer {
            background: rgba(0, 0, 0, 0.1);
            color: white;
            padding: 2rem 0;
            text-align: center;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-section h4 {
            margin-bottom: 1rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav {
                flex-direction: column;
                gap: 1rem;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .stats {
                flex-direction: column;
                align-items: center;
            }
            
            .section {
                padding: 2rem 1rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <div class="logo">🚀 <?php echo SITE_NAME; ?></div>
                <div class="nav-links">
                    <a href="login.php" class="btn btn-outline">Iniciar Sesión</a>
                    <a href="register.php" class="btn btn-primary">Registrarse</a>
                </div>
            </nav>
            
            <div class="hero">
                <h1 class="hero-title">Conectamos servicios con profesionales</h1>
                <p class="hero-subtitle">
                    La plataforma más completa para encontrar y ofrecer servicios de calidad. 
                    Desde reparaciones del hogar hasta servicios especializados.
                </p>
                <div class="stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo number_format($stats['usuarios']); ?></span>
                        <span class="stat-label">Usuarios</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo number_format($stats['servicios']); ?></span>
                        <span class="stat-label">Servicios</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo number_format($stats['trabajos']); ?></span>
                        <span class="stat-label">Trabajos</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            
            <!-- Status Section -->
            <section class="section">
                <div class="section-header">
                    <h2>📊 Estado del Sistema</h2>
                </div>
                <div class="status-grid">
                    <div class="status-card">
                        <div class="status-icon">🗄️</div>
                        <h3>Base de Datos</h3>
                        <p class="<?php echo $dbConnected ? 'success' : 'warning'; ?>">
                            <?php echo $dbConnected ? '✅ Conectada' : '⚠️ Configurando...'; ?>
                        </p>
                        <small>MariaDB/MySQL</small>
                    </div>
                    <div class="status-card">
                        <div class="status-icon">🌐</div>
                        <h3>Servidor Web</h3>
                        <p class="success">✅ Apache funcionando</p>
                        <small>PHP <?php echo phpversion(); ?></small>
                    </div>
                    <div class="status-card">
                        <div class="status-icon">⚙️</div>
                        <h3>Sistema</h3>
                        <p>IP: <?php echo $_SERVER['SERVER_ADDR'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost'; ?></p>
                        <small>Versión: <?php echo SITE_VERSION; ?></small>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section class="section">
                <div class="section-header">
                    <h2>¿Cómo funciona <?php echo SITE_NAME; ?>?</h2>
                    <p>Proceso simple y seguro en 3 pasos</p>
                </div>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🔍</div>
                        <h3>1. Busca o Publica</h3>
                        <p>Los clientes buscan servicios por categoría y ubicación. Los proveedores publican sus servicios con fotos y precios.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">💬</div>
                        <h3>2. Conecta y Coordina</h3>
                        <p>Sistema de mensajería integrado para coordinar detalles, precios y horarios de forma segura.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">✅</div>
                        <h3>3. Completa y Califica</h3>
                        <p>Una vez completado el servicio, ambas partes pueden calificar la experiencia para mantener la calidad.</p>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="cta-section">
                <h2>¿Listo para comenzar?</h2>
                <p>Únete a nuestra comunidad de servicios de calidad</p>
                <div class="cta-buttons">
                    <a href="register.php?tipo=cliente" class="btn btn-primary">
                        👤 Buscar Servicios
                    </a>
                    <a href="register.php?tipo=proveedor" class="btn btn-outline">
                        🔧 Ofrecer Servicios
                    </a>
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
                    <h4>SmartCodeUy</h4>
                    <p>Versión: <?php echo SITE_VERSION; ?></p>
                    <p>&copy; 2025 Todos los derechos reservados</p>
                    <p>Sistema de gestión de servicios</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Navegación suave
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Animaciones simples
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.status-card, .feature-card');
            
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

        // Contador animado
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/[^0-9]/g, ''));
                if (target > 0) {
                    let current = 0;
                    const increment = target / 50;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        counter.textContent = Math.floor(current).toLocaleString();
                    }, 30);
                }
            });
        }

        // Iniciar contador al cargar
        if (document.querySelector('.stats')) {
            animateCounters();
        }
    </script>
</body>
</html>