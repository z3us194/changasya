<?php
// Archivo: src/index.php - Página principal de bienvenida
require_once 'config/database.php';

// Redirigir a dashboard si ya está logueado
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Obtener estadísticas del sistema para mostrar
try {
    $stats = getSystemStats();
    $categorias = getCategorias(8);
    $serviciosDestacados = getServicios(['destacados' => true], 6);
    $serviciosRecientes = getServicios([], 6);
    
    // Obtener algunas calificaciones destacadas
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT c.puntuacion, c.comentario, c.fecha_calificacion,
               u.nombre as cliente_nombre, u.apellido as cliente_apellido,
               s.titulo as servicio_titulo,
               p.nombre as proveedor_nombre, p.apellido as proveedor_apellido
        FROM calificaciones c
        LEFT JOIN usuarios u ON c.cliente_id = u.id
        LEFT JOIN servicios s ON c.servicio_id = s.id
        LEFT JOIN usuarios p ON c.proveedor_id = p.id
        WHERE c.visible = TRUE AND c.comentario IS NOT NULL AND c.puntuacion >= 4
        ORDER BY c.fecha_calificacion DESC
        LIMIT 4
    ");
    $calificacionesDestacadas = $stmt->fetchAll();
    
    $systemStatus = "✅ Sistema operativo";
    $hasError = false;
    
} catch (Exception $e) {
    $systemStatus = "⚠️ Verificando sistema...";
    $hasError = true;
    $stats = [
        'usuarios' => ['total' => 0, 'clientes' => 0, 'proveedores' => 0],
        'servicios' => ['total' => 0, 'destacados' => 0],
        'categorias' => 0,
        'reservas' => ['total' => 0],
        'calificacion_promedio' => 0
    ];
    $categorias = [];
    $serviciosDestacados = [];
    $serviciosRecientes = [];
    $calificacionesDestacadas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo SITE_DESCRIPTION; ?></title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?> - Conectamos clientes con proveedores de servicios de calidad en toda la región">
    <meta name="keywords" content="servicios, changas, trabajos, profesionales, hogar, reparaciones">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/welcome.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <div class="nav-content">
                <div class="nav-brand">
                    <h1 class="nav-logo">🚀 <?php echo SITE_NAME; ?></h1>
                </div>
                <div class="nav-links">
                    <a href="#inicio" class="nav-link">Inicio</a>
                    <a href="#servicios" class="nav-link">Servicios</a>
                    <a href="#como-funciona" class="nav-link">¿Cómo funciona?</a>
                    <a href="#testimonios" class="nav-link">Testimonios</a>
                </div>
                <div class="nav-actions">
                    <a href="login.php" class="btn btn-outline">Iniciar Sesión</a>
                    <a href="register.php" class="btn btn-primary">Registrarse</a>
                </div>
                <button class="nav-mobile-toggle" onclick="toggleMobileNav()">☰</button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="hero-section">
        <div class="hero-background"></div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        Conectamos personas que <span class="text-gradient">necesitan servicios</span> 
                        con <span class="text-gradient">profesionales de confianza</span>
                    </h1>
                    <p class="hero-subtitle">
                        La plataforma más completa para encontrar y ofrecer servicios de calidad. 
                        Desde reparaciones del hogar hasta servicios especializados.
                    </p>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <span class="stat-number"><?php echo number_format($stats['usuarios']['total']); ?></span>
                            <span class="stat-label">Usuarios Registrados</span>
                        </div>
                        <div class="hero-stat">
                            <span class="stat-number"><?php echo number_format($stats['servicios']['total']); ?></span>
                            <span class="stat-label">Servicios Disponibles</span>
                        </div>
                        <div class="hero-stat">
                            <span class="stat-number"><?php echo number_format($stats['reservas']['total']); ?></span>
                            <span class="stat-label">Trabajos Realizados</span>
                        </div>
                        <?php if ($stats['calificacion_promedio'] > 0): ?>
                        <div class="hero-stat">
                            <span class="stat-number"><?php echo $stats['calificacion_promedio']; ?>⭐</span>
                            <span class="stat-label">Calificación Promedio</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="hero-actions">
                        <a href="register.php?tipo=cliente" class="btn btn-primary btn-large">
                            👤 Buscar Servicios
                        </a>
                        <a href="register.php?tipo=proveedor" class="btn btn-secondary btn-large">
                            🔧 Ofrecer Servicios
                        </a>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-card">
                        <div class="system-status">
                            <h3>📊 Estado del Sistema</h3>
                            <div class="status-indicator <?php echo $hasError ? 'warning' : 'success'; ?>">
                                <?php echo $systemStatus; ?>
                            </div>
                            <div class="status-details">
                                <p>🗄️ Base de datos: 12 tablas relacionadas</p>
                                <p>⚙️ Servidor: Apache + PHP <?php echo phpversion(); ?></p>
                                <p>🖥️ Sistema: AlmaLinux compatible</p>
                                <p>🔒 Seguridad: Encriptación completa</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="como-funciona" class="features-section">
        <div class="container">
            <div class="section-header">
                <h2>¿Cómo funciona <?php echo SITE_NAME; ?>?</h2>
                <p>Un proceso simple y seguro en 3 pasos</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-step">1</div>
                    <div class="feature-icon">🔍</div>
                    <h3>Busca o Publica</h3>
                    <p>Los clientes buscan servicios por categoría y ubicación. Los proveedores publican sus servicios con fotos y precios.</p>
                    <ul class="feature-list">
                        <li>Filtros inteligentes de búsqueda</li>
                        <li>Perfiles verificados</li>
                        <li>Galería de imágenes</li>
                        <li>Precios transparentes</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <div class="feature-step">2</div>
                    <div class="feature-icon">💬</div>
                    <h3>Conecta y Coordina</h3>
                    <p>Sistema de mensajería integrado para coordinar detalles, precios y horarios de forma segura.</p>
                    <ul class="feature-list">
                        <li>Chat en tiempo real</li>
                        <li>Intercambio de archivos</li>
                        <li>Notificaciones automáticas</li>
                        <li>Historial de conversaciones</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <div class="feature-step">3</div>
                    <div class="feature-icon">✅</div>
                    <h3>Completa y Califica</h3>
                    <p>Una vez completado el servicio, ambas partes pueden calificar la experiencia para mantener la calidad.</p>
                    <ul class="feature-list">
                        <li>Sistema de calificaciones 1-5 estrellas</li>
                        <li>Comentarios detallados</li>
                        <li>Historial de trabajos</li>
                        <li>Construcción de reputación</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <?php if (!empty($categorias)): ?>
    <section id="servicios" class="categories-section">
        <div class="container">
            <div class="section-header">
                <h2>Categorías de Servicios</h2>
                <p>Encuentra exactamente lo que necesitas</p>
            </div>
            
            <div class="categories-grid">
                <?php foreach ($categorias as $categoria): ?>
                <div class="category-card">
                    <div class="category-icon">
                        <?php if (strpos($categoria['icono'], 'fa-') === 0): ?>
                            <i class="fas <?php echo htmlspecialchars($categoria['icono']); ?>"></i>
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($categoria['icono'] ?: '🔧'); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($categoria['descripcion']); ?></p>
                    <a href="register.php?categoria=<?php echo $categoria['id']; ?>" class="category-link">
                        Ver servicios →
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Services Section -->
    <?php if (!empty($serviciosDestacados)): ?>
    <section class="featured-services-section">
        <div class="container">
            <div class="section-header">
                <h2>⭐ Servicios Destacados</h2>
                <p>Los mejores proveedores con las mejores calificaciones</p>
            </div>
            
            <div class="services-grid">
                <?php foreach ($serviciosDestacados as $servicio): ?>
                <div class="service-card">
                    <div class="service-badges">
                        <span class="featured-badge">⭐ Destacado</span>
                        <span class="category-badge">
                            <?php echo htmlspecialchars($servicio['categoria_icono'] ?: '🔧'); ?>
                            <?php echo htmlspecialchars($servicio['categoria_nombre']); ?>
                        </span>
                    </div>
                    <h3 class="service-title"><?php echo htmlspecialchars($servicio['titulo']); ?></h3>
                    <p class="service-description"><?php echo htmlspecialchars(substr($servicio['descripcion'], 0, 120)) . '...'; ?></p>
                    <div class="service-price">
                        <span class="price"><?php echo formatPrice($servicio['precio_desde'], $servicio['tipo_precio']); ?></span>
                        <?php if ($servicio['precio_hasta'] && $servicio['precio_hasta'] > $servicio['precio_desde']): ?>
                            <span class="price-range"> - <?php echo formatPrice($servicio['precio_hasta']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="service-provider">
                        <div class="provider-info">
                            <span class="provider-name">
                                👤 <?php echo htmlspecialchars($servicio['proveedor_nombre'] . ' ' . $servicio['proveedor_apellido']); ?>
                            </span>
                            <?php if ($servicio['calificacion_promedio']): ?>
                            <div class="rating">
                                <?php echo generateStars($servicio['calificacion_promedio']); ?>
                                <span class="rating-number">(<?php echo round($servicio['calificacion_promedio'], 1); ?>)</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="service-footer">
                        <span class="location">📍 <?php echo htmlspecialchars($servicio['ubicacion_servicio'] ?: 'Ubicación no especificada'); ?></span>
                        <a href="register.php?servicio=<?php echo $servicio['id']; ?>" class="btn btn-primary btn-sm">
                            Contactar
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Testimonials Section -->
    <?php if (!empty($calificacionesDestacadas)): ?>
    <section id="testimonios" class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2>💬 Lo que dicen nuestros usuarios</h2>
                <p>Experiencias reales de nuestra comunidad</p>
            </div>
            
            <div class="testimonials-grid">
                <?php foreach ($calificacionesDestacadas as $calificacion): ?>
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-rating">
                            <?php echo generateStars($calificacion['puntuacion']); ?>
                        </div>
                        <span class="testimonial-date"><?php echo timeAgo($calificacion['fecha_calificacion']); ?></span>
                    </div>
                    <blockquote class="testimonial-content">
                        "<?php echo htmlspecialchars($calificacion['comentario']); ?>"
                    </blockquote>
                    <div class="testimonial-footer">
                        <div class="testimonial-client">
                            <strong><?php echo htmlspecialchars($calificacion['cliente_nombre'] . ' ' . $calificacion['cliente_apellido']); ?></strong>
                            <span class="service-info">sobre "<?php echo htmlspecialchars($calificacion['servicio_titulo']); ?>"</span>
                        </div>
                        <div class="testimonial-provider">
                            <span>por <?php echo htmlspecialchars($calificacion['proveedor_nombre'] . ' ' . $calificacion['proveedor_apellido']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Technology Section -->
    <section class="technology-section">
        <div class="container">
            <div class="section-header">
                <h2>🛠️ Tecnología de Vanguardia</h2>
                <p>Sistema robusto desarrollado con las mejores prácticas</p>
            </div>
            
            <div class="tech-features">
                <div class="tech-grid">
                    <div class="tech-card">
                        <div class="tech-icon">🗄️</div>
                        <h3>Base de Datos Avanzada</h3>
                        <div class="tech-details">
                            <span class="tech-item">12 tablas relacionadas</span>
                            <span class="tech-item">Índices optimizados</span>
                            <span class="tech-item">Backup automático</span>
                            <span class="tech-item">MariaDB/MySQL</span>
                        </div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">🔒</div>
                        <h3>Seguridad Integral</h3>
                        <div class="tech-details">
                            <span class="tech-item">Encriptación de contraseñas</span>
                            <span class="tech-item">Verificación de email</span>
                            <span class="tech-item">Logs de actividad</span>
                            <span class="tech-item">Protección SQL injection</span>
                        </div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">⚡</div>
                        <h3>Rendimiento Optimizado</h3>
                        <div class="tech-details">
                            <span class="tech-item">Queries optimizadas</span>
                            <span class="tech-item">Cache inteligente</span>
                            <span class="tech-item">CDN para imágenes</span>
                            <span class="tech-item">Respuesta < 200ms</span>
                        </div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">📱</div>
                        <h3>Diseño Responsive</h3>
                        <div class="tech-details">
                            <span class="tech-item">Mobile-first design</span>
                            <span class="tech-item">Compatible todos los dispositivos</span>
                            <span class="tech-item">PWA ready</span>
                            <span class="tech-item">Touch optimizado</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>¿Listo para comenzar?</h2>
                <p>Únete a nuestra comunidad y descubre un mundo de servicios de calidad</p>
                <div class="cta-buttons">
                    <a href="register.php?tipo=cliente" class="btn btn-primary btn-large">
                        👤 Buscar Servicios
                    </a>
                    <a href="register.php?tipo=proveedor" class="btn btn-outline btn-large">
                        🔧 Ofrecer Servicios
                    </a>
                </div>
                <p class="cta-note">
                    ✨ Registro gratuito • Sin comisiones ocultas • Soporte 24/7
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><?php echo SITE_NAME; ?></h4>
                    <p><?php echo SITE_DESCRIPTION; ?></p>
                    <div class="footer-stats">
                        <span><?php echo number_format($stats['usuarios']['total']); ?> usuarios</span>
                        <span><?php echo number_format($stats['servicios']['total']); ?> servicios</span>
                        <span><?php echo number_format($stats['reservas']['total']); ?> trabajos</span>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Para Clientes</h4>
                    <ul class="footer-links">
                        <li><a href="register.php?tipo=cliente">Buscar servicios</a></li>
                        <li><a href="#categorias">Ver categorías</a></li>
                        <li><a href="#como-funciona">Cómo funciona</a></li>
                        <li><a href="#testimonios">Testimonios</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Para Proveedores</h4>
                    <ul class="footer-links">
                        <li><a href="register.php?tipo=proveedor">Ofrecer servicios</a></li>
                        <li><a href="#ventajas">Ventajas</a></li>
                        <li><a href="#precios">Planes y precios</a></li>
                        <li><a href="#recursos">Recursos</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Soporte</h4>
                    <ul class="footer-links">
                        <li><a href="ayuda.php">Centro de ayuda</a></li>
                        <li><a href="contacto.php">Contacto</a></li>
                        <li><a href="terminos.php">Términos y condiciones</a></li>
                        <li><a href="privacidad.php">Política de privacidad</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-tech">
                    <span>🖥️ Apache + PHP <?php echo phpversion(); ?></span>
                    <span>🗄️ MariaDB</span>
                    <span>🐧 AlmaLinux</span>
                    <span>⚙️ v<?php echo SITE_VERSION; ?></span>
                </div>
                <div class="footer-copyright">
                    <p>&copy; 2025 <strong>SmartCodeUy</strong> - Todos los derechos reservados</p>
                    <p>Sistema completo de gestión de servicios</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/welcome.js"></script>
    <script>
        // Navegación suave
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Toggle mobile navigation
        function toggleMobileNav() {
            const nav = document.querySelector('.nav-links');
            nav.classList.toggle('mobile-open');
        }

        // Animaciones en scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        // Observar elementos para animación
        document.querySelectorAll('.feature-card, .category-card, .service-card, .testimonial-card, .tech-card').forEach(el => {
            observer.observe(el);
        });

        // Contador animado en hero stats
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
                        const formatted = Math.floor(current).toLocaleString();
                        counter.textContent = counter.textContent.replace(/[0-9,]+/, formatted);
                    }, 30);
                }
            });
        }

        // Iniciar animación de contadores cuando la sección sea visible
        const heroObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    heroObserver.unobserve(entry.target);
                }
            });
        });

        const heroStats = document.querySelector('.hero-stats');
        if (heroStats) {
            heroObserver.observe(heroStats);
        }
    </script>
</body>
</html>