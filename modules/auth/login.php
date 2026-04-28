<?php
// modules/auth/login.php — Modern Professional Login
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/auth.php';

if (isLoggedIn()) {
    redirect('/modules/dashboard/');
}

$error = '';
if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCsrfToken($csrf_token)) {
        $error = 'Invalid security token.';
    } elseif (loginUser($username, $password)) {
        redirect('/modules/dashboard/');
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Load System — Sign In</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
</head>
<body class="login-page-modern">
    <div class="login-modern-card">
        <div class="login-modern-logo">📚</div>
        <h1>Teacher Load Assignment</h1>
        <p class="subtitle">Sign in to manage faculty workloads</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom:1.25rem;padding:0.875rem 1rem;border-radius:var(--radius-md);background:var(--danger-light);color:var(--danger);border:1px solid #fecaca;font-weight:500;display:flex;align-items:center;gap:0.5rem;">
                <span>⚠️</span> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus
                       placeholder="Enter your username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required
                       placeholder="Enter your password">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                Sign In — TeacherLoad
            </button>
        </form>
</body>
</html>
