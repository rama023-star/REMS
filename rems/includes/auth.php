<?php
// Authentication Functions

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require login - redirect to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('index.php');
    }
}

/**
 * Login user
 */
function login($email, $password) {
    $stmt = db()->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Update last login
        $updateStmt = db()->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id");
        $updateStmt->bindParam(':id', $user['user_id']);
        $updateStmt->execute();
        
        return true;
    }
    
    return false;
}

/**
 * Register new user
 */
function register($name, $email, $password, $role = 'staff') {
    // Check if email already exists
    $stmt = db()->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        return false; // Email already exists
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = db()->prepare("INSERT INTO users (fullname, email, password, role) VALUES (:name, :email, :password, :role)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':role', $role);
    
    return $stmt->execute();
}

/**
 * Logout user
 */
function logout() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = db()->prepare("SELECT * FROM users WHERE user_id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Check if user has role
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Require admin role
 */
function requireAdmin() {
    if (!hasRole('admin')) {
        redirect('dashboard.php');
    }
}

/**
 * Get user initials
 */
function getUserInitials($name) {
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return substr($initials, 0, 2);
}
?>
