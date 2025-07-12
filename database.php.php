<?php
// Configuración de base de datos ChangasYa - Versión Avanzada
// Compatible con la estructura completa de BD

define('DB_HOST', 'localhost');
define('DB_NAME', 'changasya_db');
define('DB_USER', 'changasya_user');
define('DB_PASSWORD', 'ChangasYa2024!');
define('DB_CHARSET', 'utf8mb4');

// Configuración del sitio
define('SITE_NAME', 'ChangasYa');
define('SITE_DESCRIPTION', 'Plataforma completa de servicios y changas');
define('SITE_VERSION', '2.0.0');
define('ADMIN_EMAIL', 'admin@changasya.com');

// Configuración de URLs dinámicas
function getBaseURL() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $protocol . $host . $uri;
}

define('BASE_URL', getBaseURL());

// Configuración de archivos y uploads
define('UPLOAD_PATH', __DIR__ . '/../../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_IMAGES_PER_SERVICE', 5);

// Configuración de paginación
define('SERVICES_PER_PAGE', 12);
define('MESSAGES_PER_PAGE', 20);
define('RESERVAS_PER_PAGE', 15);

// Configuración de sesiones
define('SESSION_LIFETIME', 3600);
define('SESSION_NAME', 'changasya_session');

// Tipos de usuario
define('USER_TYPE_CLIENT', 'cliente');
define('USER_TYPE_PROVIDER', 'proveedor');
define('USER_TYPE_ADMIN', 'administrador');

// Estados de reserva
define('RESERVA_PENDIENTE', 'pendiente');
define('RESERVA_CONFIRMADA', 'confirmada');
define('RESERVA_EN_PROGRESO', 'en_progreso');
define('RESERVA_COMPLETADA', 'completada');
define('RESERVA_CANCELADA', 'cancelada');
define('RESERVA_RECHAZADA', 'rechazada');

// Función principal para conectar a la base de datos
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                DB_USER,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión ChangasYa: " . $e->getMessage());
            die('Error de conexión a la base de datos. Por favor, intente más tarde.');
        }
    }
    
    return $pdo;
}

// Función para obtener configuración del sistema
function getSystemConfig($key, $default = null) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT valor FROM configuracion_sistema WHERE clave = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : $default;
    } catch (Exception $e) {
        return $default;
    }
}

// Función para obtener estadísticas del sistema
function getSystemStats() {
    try {
        $pdo = getDBConnection();
        
        $stats = [];
        
        // Total de usuarios por tipo
        $stmt = $pdo->query("
            SELECT tipo_usuario, COUNT(*) as total 
            FROM usuarios 
            WHERE activo = TRUE 
            GROUP BY tipo_usuario
        ");
        $userStats = $stmt->fetchAll();
        
        $stats['usuarios'] = [
            'total' => 0,
            'clientes' => 0,
            'proveedores' => 0,
            'administradores' => 0
        ];
        
        foreach ($userStats as $userStat) {
            $stats['usuarios'][$userStat['tipo_usuario'] . 's'] = (int)$userStat['total'];
            $stats['usuarios']['total'] += (int)$userStat['total'];
        }
        
        // Total de servicios
        $stmt = $pdo->query("SELECT COUNT(*) FROM servicios WHERE activo = TRUE AND disponible = TRUE");
        $stats['servicios'] = [
            'total' => (int)$stmt->fetchColumn(),
            'destacados' => 0
        ];
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM servicios WHERE activo = TRUE AND disponible = TRUE AND destacado = TRUE");
        $stats['servicios']['destacados'] = (int)$stmt->fetchColumn();
        
        // Total de categorías
        $stmt = $pdo->query("SELECT COUNT(*) FROM categorias WHERE activa = TRUE");
        $stats['categorias'] = (int)$stmt->fetchColumn();
        
        // Total de reservas por estado
        $stmt = $pdo->query("
            SELECT estado, COUNT(*) as total 
            FROM reservas 
            GROUP BY estado
        ");
        $reservaStats = $stmt->fetchAll();
        
        $stats['reservas'] = [
            'total' => 0,
            'pendientes' => 0,
            'confirmadas' => 0,
            'completadas' => 0
        ];
        
        foreach ($reservaStats as $reservaStat) {
            $stats['reservas'][$reservaStat['estado'] . 's'] = (int)$reservaStat['total'];
            $stats['reservas']['total'] += (int)$reservaStat['total'];
        }
        
        // Promedio de calificaciones
        $stmt = $pdo->query("SELECT AVG(puntuacion) as promedio FROM calificaciones WHERE visible = TRUE");
        $stats['calificacion_promedio'] = round((float)$stmt->fetchColumn(), 1);
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Error obteniendo estadísticas: " . $e->getMessage());
        return [
            'usuarios' => ['total' => 0, 'clientes' => 0, 'proveedores' => 0, 'administradores' => 0],
            'servicios' => ['total' => 0, 'destacados' => 0],
            'categorias' => 0,
            'reservas' => ['total' => 0, 'pendientes' => 0, 'confirmadas' => 0, 'completadas' => 0],
            'calificacion_promedio' => 0
        ];
    }
}

// Función para obtener categorías activas
function getCategorias($limit = null) {
    try {
        $pdo = getDBConnection();
        $sql = "SELECT * FROM categorias WHERE activa = TRUE ORDER BY orden_visualizacion ASC, nombre ASC";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error obteniendo categorías: " . $e->getMessage());
        return [];
    }
}

// Función para obtener servicios
function getServicios($filters = [], $limit = 12, $offset = 0) {
    try {
        $pdo = getDBConnection();
        
        $sql = "
            SELECT s.*, 
                   c.nombre as categoria_nombre,
                   c.icono as categoria_icono,
                   u.nombre as proveedor_nombre,
                   u.apellido as proveedor_apellido,
                   u.foto_perfil as proveedor_foto,
                   AVG(cal.puntuacion) as calificacion_promedio,
                   COUNT(cal.id) as total_calificaciones
            FROM servicios s
            LEFT JOIN categorias c ON s.categoria_id = c.id
            LEFT JOIN usuarios u ON s.proveedor_id = u.id
            LEFT JOIN calificaciones cal ON s.id = cal.servicio_id AND cal.visible = TRUE
            WHERE s.activo = TRUE AND s.disponible = TRUE
        ";
        
        $params = [];
        
        // Filtros
        if (!empty($filters['categoria_id'])) {
            $sql .= " AND s.categoria_id = ?";
            $params[] = $filters['categoria_id'];
        }
        
        if (!empty($filters['ubicacion'])) {
            $sql .= " AND s.ubicacion_servicio LIKE ?";
            $params[] = '%' . $filters['ubicacion'] . '%';
        }
        
        if (!empty($filters['precio_max'])) {
            $sql .= " AND s.precio_desde <= ?";
            $params[] = $filters['precio_max'];
        }
        
        if (!empty($filters['destacados'])) {
            $sql .= " AND s.destacado = TRUE";
        }
        
        $sql .= " GROUP BY s.id";
        $sql .= " ORDER BY s.destacado DESC, s.fecha_creacion DESC";
        $sql .= " LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Error obteniendo servicios: " . $e->getMessage());
        return [];
    }
}

// Función para obtener un servicio por ID
function getServicioById($id) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT s.*, 
                   c.nombre as categoria_nombre,
                   c.icono as categoria_icono,
                   u.nombre as proveedor_nombre,
                   u.apellido as proveedor_apellido,
                   u.telefono as proveedor_telefono,
                   u.email as proveedor_email,
                   u.foto_perfil as proveedor_foto,
                   u.descripcion as proveedor_descripcion,
                   AVG(cal.puntuacion) as calificacion_promedio,
                   COUNT(cal.id) as total_calificaciones
            FROM servicios s
            LEFT JOIN categorias c ON s.categoria_id = c.id
            LEFT JOIN usuarios u ON s.proveedor_id = u.id
            LEFT JOIN calificaciones cal ON s.id = cal.servicio_id AND cal.visible = TRUE
            WHERE s.id = ? AND s.activo = TRUE
            GROUP BY s.id
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error obteniendo servicio: " . $e->getMessage());
        return null;
    }
}

// Función para registrar actividad
function logActivity($usuario_id, $accion, $tabla = null, $registro_id = null, $datos_anteriores = null, $datos_nuevos = null) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO logs_actividad 
            (usuario_id, accion, tabla_afectada, registro_id, datos_anteriores, datos_nuevos, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt->execute([
            $usuario_id,
            $accion,
            $tabla,
            $registro_id,
            $datos_anteriores ? json_encode($datos_anteriores) : null,
            $datos_nuevos ? json_encode($datos_nuevos) : null,
            $ip,
            $userAgent
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error registrando actividad: " . $e->getMessage());
        return false;
    }
}

// Funciones de utilidad
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function formatPrice($price, $type = 'fijo') {
    if ($price === null) return 'A convenir';
    
    $formatted = '$' . number_format($price, 0, ',', '.');
    
    switch ($type) {
        case 'por_hora':
            return $formatted . '/hora';
        case 'por_proyecto':
            return 'Desde ' . $formatted;
        case 'a_convenir':
            return 'A convenir';
        default:
            return $formatted;
    }
}

function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'hace unos segundos';
    if ($time < 3600) return 'hace ' . floor($time/60) . ' min';
    if ($time < 86400) return 'hace ' . floor($time/3600) . ' h';
    if ($time < 2592000) return 'hace ' . floor($time/86400) . ' días';
    if ($time < 31104000) return 'hace ' . floor($time/2592000) . ' meses';
    return 'hace ' . floor($time/31104000) . ' años';
}

function generateStars($rating, $maxStars = 5) {
    $stars = '';
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    
    for ($i = 1; $i <= $maxStars; $i++) {
        if ($i <= $fullStars) {
            $stars .= '⭐';
        } elseif ($i == $fullStars + 1 && $hasHalfStar) {
            $stars .= '⭐';
        } else {
            $stars .= '☆';
        }
    }
    
    return $stars;
}

// Función para generar URL amigable
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s]/', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    return trim($text, '-');
}

// Inicializar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Función para obtener el usuario actual
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND activo = TRUE");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

// Función para verificar permisos
function hasPermission($required_type) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    if ($user['tipo_usuario'] === 'administrador') return true;
    
    return $user['tipo_usuario'] === $required_type;
}
?>