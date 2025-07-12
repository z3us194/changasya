<?php
// Archivo: src/includes/auth.php
// Sistema de autenticación para ChangasYa

require_once __DIR__ . '/../config/database.php';

// Función para registrar nuevo usuario
function registerUser($nombre, $apellido, $email, $password, $telefono = null, $tipo_usuario = 'cliente') {
    try {
        $pdo = getDBConnection();
        
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'El email ya está registrado'];
        }
        
        // Hash de la contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Generar token de verificación
        $verificationToken = bin2hex(random_bytes(32));
        
        // Insertar usuario
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, apellido, email, password, telefono, tipo_usuario, token_verificacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $nombre, $apellido, $email, $hashedPassword, $telefono, $tipo_usuario, $verificationToken
        ]);
        
        if ($result) {
            $userId = $pdo->lastInsertId();
            
            // Registrar actividad
            logActivity($userId, 'registro_usuario', 'usuarios', $userId);
            
            return [
                'success' => true, 
                'message' => 'Usuario registrado exitosamente',
                'user_id' => $userId,
                'verification_token' => $verificationToken
            ];
        }
        
        return ['success' => false, 'message' => 'Error al registrar usuario'];
        
    } catch (Exception $e) {
        error_log("Error en registro: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error interno del servidor'];
    }
}

// Función para login de usuario
function loginUser($email, $password) {
    try {
        $pdo = getDBConnection();
        
        // Buscar usuario por email
        $stmt = $pdo->prepare("
            SELECT id, nombre, apellido, email, password, tipo_usuario, activo, email_verificado 
            FROM usuarios 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email no encontrado'];
        }
        
        if (!$user['activo']) {
            return ['success' => false, 'message' => 'Cuenta desactivada'];
        }
        
        // Verificar contraseña
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Contraseña incorrecta'];
        }
        
        // Iniciar sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_lastname'] = $user['apellido'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user['tipo_usuario'];
        $_SESSION['email_verified'] = $user['email_verificado'];
        $_SESSION['logged_in'] = true;
        
        // Actualizar última conexión
        $stmt = $pdo->prepare("UPDATE usuarios SET ultima_conexion = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Registrar actividad
        logActivity($user['id'], 'login', 'usuarios', $user['id']);
        
        return [
            'success' => true, 
            'message' => 'Login exitoso',
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellido' => $user['apellido'],
                'email' => $user['email'],
                'tipo_usuario' => $user['tipo_usuario'],
                'email_verificado' => $user['email_verificado']
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Error en login: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error interno del servidor'];
    }
}

// Función para logout
function logoutUser() {
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'logout', 'usuarios', $_SESSION['user_id']);
    }
    
    $_SESSION = array();
    session_destroy();
    
    return ['success' => true, 'message' => 'Sesión cerrada'];
}

// Función para verificar email
function verifyEmail($token) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT id FROM usuarios 
            WHERE token_verificacion = ? AND email_verificado = FALSE
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Token inválido o email ya verificado'];
        }
        
        // Marcar email como verificado
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET email_verificado = TRUE, token_verificacion = NULL 
            WHERE id = ?
        ");
        $result = $stmt->execute([$user['id']]);
        
        if ($result) {
            logActivity($user['id'], 'email_verificado', 'usuarios', $user['id']);
            return ['success' => true, 'message' => 'Email verificado exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al verificar email'];
        
    } catch (Exception $e) {
        error_log("Error en verificación: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error interno del servidor'];
    }
}

// Función para validar datos de registro
function validateRegistrationData($data) {
    $errors = [];
    
    // Validar nombre
    if (empty($data['nombre']) || strlen($data['nombre']) < 2) {
        $errors[] = "El nombre debe tener al menos 2 caracteres";
    }
    
    // Validar apellido
    if (empty($data['apellido']) || strlen($data['apellido']) < 2) {
        $errors[] = "El apellido debe tener al menos 2 caracteres";
    }
    
    // Validar email
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }
    
    // Validar contraseña
    if (empty($data['password']) || strlen($data['password']) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres";
    }
    
    // Validar confirmación de contraseña
    if (empty($data['confirm_password']) || $data['password'] !== $data['confirm_password']) {
        $errors[] = "Las contraseñas no coinciden";
    }
    
    // Validar teléfono (opcional)
    if (!empty($data['telefono']) && !preg_match('/^[\+]?[0-9\s\-\(\)]{8,20}$/', $data['telefono'])) {
        $errors[] = "Formato de teléfono inválido";
    }
    
    // Validar tipo de usuario
    $validTypes = ['cliente', 'proveedor'];
    if (empty($data['tipo_usuario']) || !in_array($data['tipo_usuario'], $validTypes)) {
        $errors[] = "Tipo de usuario inválido";
    }
    
    return $errors;
}

// Función para obtener perfil de usuario
function getUserProfile($userId) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT id, nombre, apellido, email, telefono, direccion, ciudad, 
                   codigo_postal, tipo_usuario, foto_perfil, descripcion, 
                   activo, email_verificado, fecha_registro, ultima_conexion
            FROM usuarios 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        
        return $stmt->fetch();
        
    } catch (Exception $e) {
        error_log("Error obteniendo perfil: " . $e->getMessage());
        return null;
    }
}

// Función para requerir login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Función para requerir tipo de usuario específico
function requireUserType($requiredType) {
    requireLogin();
    
    if (!hasPermission($requiredType)) {
        header('Location: index.php?error=no_permission');
        exit;
    }
}

// Middleware para proteger páginas
function protectPage($requiredType = null) {
    requireLogin();
    
    if ($requiredType && !hasPermission($requiredType)) {
        header('Location: index.php?error=no_permission');
        exit;
    }
}
?>