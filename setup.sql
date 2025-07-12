-- ============================================
-- BASE DE DATOS CHANGASYA - MARIADB COMPATIBLE
-- ============================================

USE changasya_db;
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;



-- ============================================
-- TABLA: usuarios
-- ============================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    ciudad VARCHAR(100),
    codigo_postal VARCHAR(10),
    tipo_usuario ENUM('cliente', 'proveedor', 'administrador') NOT NULL DEFAULT 'cliente',
    foto_perfil VARCHAR(255) DEFAULT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    email_verificado BOOLEAN DEFAULT FALSE,
    token_verificacion VARCHAR(255) DEFAULT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_conexion TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_tipo_usuario (tipo_usuario),
    INDEX idx_activo (activo),
    INDEX idx_ciudad (ciudad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: categorias
-- ============================================
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    icono VARCHAR(100) DEFAULT NULL,
    activa BOOLEAN DEFAULT TRUE,
    orden_visualizacion INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_activa (activa),
    INDEX idx_orden (orden_visualizacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: servicios
-- ============================================
CREATE TABLE servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proveedor_id INT NOT NULL,
    categoria_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    precio_desde DECIMAL(10,2),
    precio_hasta DECIMAL(10,2),
    tipo_precio ENUM('fijo', 'por_hora', 'por_proyecto', 'a_convenir') DEFAULT 'fijo',
    ubicacion_servicio VARCHAR(255),
    radio_cobertura INT DEFAULT 0,
    disponible BOOLEAN DEFAULT TRUE,
    destacado BOOLEAN DEFAULT FALSE,
    imagen_principal VARCHAR(255) DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    vistas INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (proveedor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
    
    INDEX idx_proveedor (proveedor_id),
    INDEX idx_categoria (categoria_id),
    INDEX idx_disponible (disponible),
    INDEX idx_activo (activo),
    INDEX idx_destacado (destacado),
    INDEX idx_precio (precio_desde, precio_hasta),
    INDEX idx_ubicacion (ubicacion_servicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: imagenes_servicios
-- ============================================
CREATE TABLE imagenes_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    servicio_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    es_principal BOOLEAN DEFAULT FALSE,
    orden_visualizacion INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE,
    
    INDEX idx_servicio (servicio_id),
    INDEX idx_principal (es_principal),
    INDEX idx_orden (orden_visualizacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: conversaciones
-- ============================================
CREATE TABLE conversaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario1_id INT NOT NULL,
    usuario2_id INT NOT NULL,
    servicio_id INT DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activa BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (usuario1_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario2_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_conversation (usuario1_id, usuario2_id, servicio_id),
    INDEX idx_usuarios (usuario1_id, usuario2_id),
    INDEX idx_servicio (servicio_id),
    INDEX idx_ultima_actividad (ultima_actividad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: mensajes
-- ============================================
CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversacion_id INT NOT NULL,
    emisor_id INT NOT NULL,
    receptor_id INT NOT NULL,
    contenido TEXT NOT NULL,
    leido BOOLEAN DEFAULT FALSE,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_lectura TIMESTAMP NULL,
    tipo_mensaje ENUM('texto', 'imagen', 'archivo') DEFAULT 'texto',
    archivo_adjunto VARCHAR(255) DEFAULT NULL,
    
    FOREIGN KEY (conversacion_id) REFERENCES conversaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (emisor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (receptor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    INDEX idx_conversacion (conversacion_id),
    INDEX idx_emisor (emisor_id),
    INDEX idx_receptor (receptor_id),
    INDEX idx_leido (leido),
    INDEX idx_fecha_envio (fecha_envio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: reservas
-- ============================================
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    servicio_id INT NOT NULL,
    cliente_id INT NOT NULL,
    proveedor_id INT NOT NULL,
    fecha_reserva DATE NOT NULL,
    hora_inicio TIME DEFAULT NULL,
    hora_fin TIME DEFAULT NULL,
    descripcion_trabajo TEXT,
    direccion_servicio TEXT,
    precio_acordado DECIMAL(10,2) DEFAULT NULL,
    estado ENUM('pendiente', 'confirmada', 'en_progreso', 'completada', 'cancelada', 'rechazada') DEFAULT 'pendiente',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_confirmacion TIMESTAMP NULL,
    fecha_completado TIMESTAMP NULL,
    motivo_cancelacion TEXT DEFAULT NULL,
    notas_proveedor TEXT DEFAULT NULL,
    notas_cliente TEXT DEFAULT NULL,
    
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (proveedor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    INDEX idx_servicio (servicio_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_proveedor (proveedor_id),
    INDEX idx_fecha_reserva (fecha_reserva),
    INDEX idx_estado (estado),
    INDEX idx_fecha_solicitud (fecha_solicitud)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: calificaciones
-- ============================================
CREATE TABLE calificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    servicio_id INT NOT NULL,
    reserva_id INT DEFAULT NULL,
    cliente_id INT NOT NULL,
    proveedor_id INT NOT NULL,
    puntuacion INT NOT NULL,
    comentario TEXT DEFAULT NULL,
    fecha_calificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    visible BOOLEAN DEFAULT TRUE,
    respuesta_proveedor TEXT DEFAULT NULL,
    fecha_respuesta TIMESTAMP NULL,
    
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (proveedor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_rating (reserva_id, cliente_id),
    INDEX idx_servicio (servicio_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_proveedor (proveedor_id),
    INDEX idx_puntuacion (puntuacion),
    INDEX idx_visible (visible),
    INDEX idx_fecha (fecha_calificacion),
    CONSTRAINT chk_puntuacion CHECK (puntuacion >= 1 AND puntuacion <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: disponibilidad_proveedores
-- ============================================
CREATE TABLE disponibilidad_proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proveedor_id INT NOT NULL,
    dia_semana ENUM('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (proveedor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    INDEX idx_proveedor (proveedor_id),
    INDEX idx_dia (dia_semana),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: dias_no_disponibles
-- ============================================
CREATE TABLE dias_no_disponibles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proveedor_id INT NOT NULL,
    fecha DATE NOT NULL,
    motivo VARCHAR(255) DEFAULT NULL,
    todo_el_dia BOOLEAN DEFAULT TRUE,
    hora_inicio TIME DEFAULT NULL,
    hora_fin TIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (proveedor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_unavailable_day (proveedor_id, fecha, hora_inicio, hora_fin),
    INDEX idx_proveedor (proveedor_id),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: notificaciones
-- ============================================
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('mensaje', 'reserva', 'calificacion', 'sistema') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_lectura TIMESTAMP NULL,
    enlace VARCHAR(500) DEFAULT NULL,
    datos_adicionales JSON DEFAULT NULL,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo (tipo),
    INDEX idx_leida (leida),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: configuracion_sistema
-- ============================================
CREATE TABLE configuracion_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT NOT NULL,
    descripcion TEXT DEFAULT NULL,
    tipo_dato ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: logs_actividad
-- ============================================
CREATE TABLE logs_actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT NULL,
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(50) DEFAULT NULL,
    registro_id INT DEFAULT NULL,
    datos_anteriores JSON DEFAULT NULL,
    datos_nuevos JSON DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_tabla (tabla_afectada),
    INDEX idx_fecha (fecha_accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CREAR USUARIO Y PRIVILEGIOS
-- ============================================
CREATE USER IF NOT EXISTS 'changasya_user'@'localhost' IDENTIFIED BY 'ChangasYa2024!';
CREATE USER IF NOT EXISTS 'changasya_user'@'%' IDENTIFIED BY 'ChangasYa2024!';

GRANT ALL PRIVILEGES ON changasya_db.* TO 'changasya_user'@'localhost';
GRANT ALL PRIVILEGES ON changasya_db.* TO 'changasya_user'@'%';

FLUSH PRIVILEGES;

-- ============================================
-- DATOS INICIALES
-- ============================================
INSERT IGNORE INTO categorias (nombre, descripcion, icono, orden_visualizacion) VALUES
('Plomería', 'Servicios de fontanería y plomería', 'fa-wrench', 1),
('Electricidad', 'Instalaciones y reparaciones eléctricas', 'fa-bolt', 2),
('Carpintería', 'Trabajos en madera y muebles', 'fa-hammer', 3),
('Limpieza', 'Servicios de limpieza doméstica y comercial', 'fa-broom', 4),
('Jardinería', 'Mantenimiento de jardines y espacios verdes', 'fa-leaf', 5),
('Pintura', 'Servicios de pintura interior y exterior', 'fa-paint-brush', 6),
('Tecnología', 'Reparación y soporte técnico', 'fa-laptop', 7),
('Belleza', 'Servicios de estética y belleza', 'fa-cut', 8),
('Educación', 'Clases particulares y tutorías', 'fa-graduation-cap', 9),
('Transporte', 'Servicios de transporte y mudanzas', 'fa-truck', 10);

INSERT IGNORE INTO usuarios (nombre, apellido, email, password, tipo_usuario, activo, email_verificado) VALUES
('Admin', 'ChangasYa', 'admin@changasya.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', TRUE, TRUE);

INSERT IGNORE INTO configuracion_sistema (clave, valor, descripcion, tipo_dato) VALUES
('sitio_nombre', 'ChangasYa', 'Nombre del sitio web', 'string'),
('sitio_email', 'contacto@changasya.com', 'Email de contacto del sitio', 'string'),
('max_imagenes_servicio', '5', 'Máximo número de imágenes por servicio', 'number'),
('tiempo_expiracion_reserva', '24', 'Horas para confirmar una reserva', 'number'),
('calificacion_obligatoria', 'false', 'Si la calificación es obligatoria después del servicio', 'boolean'),
('radio_busqueda_default', '10', 'Radio de búsqueda por defecto en kilómetros', 'number');