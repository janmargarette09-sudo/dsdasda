<?php
// includes/auth.php
// Authentication business logic

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authenticate user
     */
    public function login($username, $password) {
        $stmt = $this->db->prepare("
            SELECT id, username, password_hash, full_name, email, role, is_active 
            FROM users 
            WHERE username = ? AND is_active = 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_data'] = $user;
            $_SESSION['last_activity'] = time();
            $_SESSION['login_time'] = time();
            
            $this->logActivity('login', $user['id']);
            regenerateSession();
            
            return true;
        }
        return false;
    }
    
    /**
     * Create new user (admin only)
     */
    public function createUser($username, $password, $full_name, $email, $role = 'chair') {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (username, password_hash, full_name, email, role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $username, $password_hash, $full_name, $email, $role
        ]);
    }
    
    /**
     * Change password
     */
    public function changePassword($user_id, $current_password, $new_password) {
        $stmt = $this->db->prepare("
            SELECT password_hash FROM users WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($current_password, $user['password_hash'])) {
            return false;
        }
        
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$new_hash, $user_id]);
    }
    
    /**
     * Log user activity
     */
    private function logActivity($action, $user_id) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $this->db->prepare("
            INSERT INTO audit_log (user_id, action, ip_address, user_agent) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $action, $ip, substr($user_agent, 0, 500)]);
    }
}

// Global auth helper functions
function loginUser($username, $password) {
    static $auth = null;
    if ($auth === null) {
        $auth = new Auth();
    }
    return $auth->login($username, $password);
}

function getAuthInstance() {
    static $auth = null;
    if ($auth === null) {
        $auth = new Auth();
    }
    return $auth;
}
