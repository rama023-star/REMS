<?php
// REMS Configuration File

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

// Start session
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'rems_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'REMS');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/rem.s/rems');

// Timezone
date_default_timezone_set('America/New_York');

// Include database connection
require_once __DIR__ . '/db.php';

// Include functions
require_once __DIR__ . '/functions.php';

// Include authentication
require_once __DIR__ . '/auth.php';
?>
