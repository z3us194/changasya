<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Cerrar sesión
logoutUser();

// Redirigir al index
header('Location: index.php?logout=success');
exit;
?>