<?php
// config/session.php
// Secure session management with auth guard

require_once __DIR__ . '/constants.php';

session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// CSRF Token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Authentication guard
function requireAuth($roles = ['chair', 'admin']) {
    if (!isLoggedIn()) {
        redirect('/modules/auth/login.php');
        exit;
    }
    
    if (!empty($roles) && !hasRole($roles)) {
        die('Access denied. Insufficient permissions.');
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['username']) && 
           !empty($_SESSION['user_id']);
}

// Get current user data
function currentUser() {
    if (!isLoggedIn()) return null;
    
    static $user = null;
    if ($user === null && isset($_SESSION['user_data'])) {
        $user = $_SESSION['user_data'];
    }
    return $user;
}

// Check if user has specific role(s)
function hasRole($roles) {
    $user = currentUser();
    if (!$user) return false;
    
    if (is_string($roles)) $roles = [$roles];
    return in_array($user['role'], $roles);
}

// Secure redirect helper
function redirect($url, $permanent = false) {
    $url = BASE_URL . '/' . ltrim($url, '/');
    header('Location: ' . $url, true, $permanent ? 301 : 302);
    exit;
}

// Regenerate session ID (security)
function regenerateSession() {
    session_regenerate_id(true);
    $_SESSION['last_activity'] = time();
}

// Check session timeout
function checkSessionTimeout() {
    if (!isLoggedIn()) return false;
    
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        logout();
        return false;
    }
    regenerateSession();
    return true;
}

// Logout function
function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    redirect('/modules/auth/login.php');
}

// Default session values
if (!isset($_SESSION['current_semester'])) {
    $_SESSION['current_semester'] = '1st';
}
if (!isset($_SESSION['current_school_year'])) {
    $_SESSION['current_school_year'] = '2024-2025';
}

// Verify CSRF token
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
