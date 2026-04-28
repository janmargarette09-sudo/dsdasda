<?php
// modules/settings/index.php — Professional Settings
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../models/User.php';
requireAuth();

$db = Database::getInstance();
$auth = getAuthInstance();
$userModel = new User();
$errors = [];
$success = '';

// Handle Profile Update
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'profile') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($full_name && $email) {
        $userModel->update($_SESSION['user_id'], [
            'full_name' => $full_name,
            'email' => $email
        ]);
        $_SESSION['user_data']['full_name'] = $full_name;
        $_SESSION['user_data']['email'] = $email;
        $success = 'Profile updated successfully.';
    } else {
        $errors[] = 'Full name and email are required.';
    }
}

// Handle Password Change
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'password') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$current || !$new || !$confirm) {
        $errors[] = 'All password fields are required.';
    } elseif ($new !== $confirm) {
        $errors[] = 'New passwords do not match.';
    } elseif (strlen($new) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    } elseif (!$auth->changePassword($_SESSION['user_id'], $current, $new)) {
        $errors[] = 'Current password is incorrect.';
    } else {
        $success = 'Password changed successfully.';
    }
}

// Handle System Preferences
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'preferences') {
    $sy = trim($_POST['school_year'] ?? '');
    $sem = $_POST['semester'] ?? '';
    if ($sy && $sem) {
        $_SESSION['current_school_year'] = $sy;
        $_SESSION['current_semester'] = $sem;
        $success = 'System preferences updated.';
    } else {
        $errors[] = 'School year and semester are required.';
    }
}

// Handle User Management (admin only)
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'toggle_user' && hasRole('admin')) {
    $uid = (int)($_POST['user_id'] ?? 0);
    $active = (int)($_POST['is_active'] ?? 1);
    if ($uid) {
        $userModel->update($uid, ['is_active' => $active ? 1 : 0]);
        $success = 'User status updated.';
    }
}

if ($_POST && isset($_POST['action']) && $_POST['action'] === 'reset_password' && hasRole('admin')) {
    $uid = (int)($_POST['user_id'] ?? 0);
    $newPass = $_POST['new_password'] ?? '';
    if ($uid && $newPass && strlen($newPass) >= 6) {
        $userModel->update($uid, ['password' => $newPass]);
        $success = 'Password reset successfully.';
    } else {
        $errors[] = 'Valid password (min 6 chars) required.';
    }
}

$currentUser = $userModel->getById($_SESSION['user_id']);
$allUsers = hasRole('admin') ? $userModel->getAll() : [];

$pageTitle = 'Settings';
$extraCss = [];

require __DIR__ . '/../../includes/header.php';
?>

<div class="page-toolbar">
    <h2>Settings</h2>
</div>

<?php if ($success): ?>
    <div class="flash flash-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="flash flash-error"><?= htmlspecialchars(implode(' ', $errors)) ?></div>
<?php endif; ?>

<div class="form-wrapper">
    <!-- Profile -->
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon">👤</div>
            <div>
                <h3>My Profile</h3>
                <p>Your account information</p>
            </div>
        <div class="form-section-body">
            <form method="POST">
                <input type="hidden" name="action" value="profile">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?= htmlspecialchars($currentUser['username'] ?? '') ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" value="<?= ucfirst(htmlspecialchars($currentUser['role'] ?? '')) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($currentUser['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" required>
                    </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>

    <!-- Change Password -->
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon">🔒</div>
            <div>
                <h3>Change Password</h3>
                <p>Update your account security</p>
            </div>
        <div class="form-section-body">
            <form method="POST">
                <input type="hidden" name="action" value="password">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required placeholder="Enter current password">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required minlength="6" placeholder="Min 6 characters">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required minlength="6" placeholder="Re-enter new password">
                    </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>

    <!-- System Preferences -->
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon">⚙️</div>
            <div>
                <h3>System Preferences</h3>
                <p>Current academic period settings</p>
            </div>
        <div class="form-section-body">
            <form method="POST">
                <input type="hidden" name="action" value="preferences">
                <div class="form-grid">
                    <div class="form-group">
                        <label>School Year</label>
                        <input type="text" name="school_year" value="<?= htmlspecialchars($_SESSION['current_school_year'] ?? '2024-2025') ?>" required placeholder="e.g. 2024-2025">
                    </div>
                    <div class="form-group">
                        <label>Semester</label>
                        <select name="semester">
                            <option value="1st" <?= ($_SESSION['current_semester'] ?? '') === '1st' ? 'selected' : '' ?>>1st Semester</option>
                            <option value="2nd" <?= ($_SESSION['current_semester'] ?? '') === '2nd' ? 'selected' : '' ?>>2nd Semester</option>
                            <option value="summer" <?= ($_SESSION['current_semester'] ?? '') === 'summer' ? 'selected' : '' ?>>Summer</option>
                        </select>
                    </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Preferences</button>
                </div>
            </form>
        </div>

    <!-- User Management (Admin Only) -->
    <?php if (hasRole('admin')): ?>
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon">👥</div>
            <div>
                <h3>User Management</h3>
                <p>Manage system accounts and access</p>
            </div>
        <div class="form-section-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allUsers as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= ucfirst($u['role']) ?></td>
                            <td>
                                <span class="badge badge-<?= $u['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td><?= formatDate($u['created_at'], 'M j, Y') ?></td>
                            <td class="actions">
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="toggle_user">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="is_active" value="<?= $u['is_active'] ? 0 : 1 ?>">
                                    <button type="submit" class="btn btn-sm btn-<?= $u['is_active'] ? 'secondary' : 'success' ?>">
                                        <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;margin-left:0.5rem;">
                                    <input type="hidden" name="action" value="reset_password">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="text" name="new_password" placeholder="New pass" required minlength="6" style="width:110px;padding:0.35rem 0.5rem;border:2px solid var(--border-light);border-radius:var(--radius-sm);font-size:0.8rem;">
                                    <button type="submit" class="btn btn-sm btn-warning">Reset</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
