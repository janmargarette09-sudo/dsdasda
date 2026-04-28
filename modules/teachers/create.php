<?php
// modules/teachers/create.php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Teacher.php';
requireAuth();

$pageTitle = 'Add Teacher';
$extraCss = ['/assets/css/premium-forms.css'];
$extraJs = ['/assets/js/teachers.js'];

$teacherModel = new Teacher();
$error = '';
$success = '';

if ($_POST) {
    $data = [
        'employee_id' => trim($_POST['employee_id'] ?? ''),
        'first_name'  => trim($_POST['first_name'] ?? ''),
        'last_name'   => trim($_POST['last_name'] ?? ''),
        'email'       => trim($_POST['email'] ?? ''),
        'phone'       => trim($_POST['phone'] ?? ''),
        'department'  => trim($_POST['department'] ?? ''),
        'max_units'   => (float)($_POST['max_units'] ?? 24),
        'min_units'   => (float)($_POST['min_units'] ?? 12),
        'employment_type' => $_POST['employment_type'] ?? 'full_time',
        'status'      => $_POST['status'] ?? 'active'
    ];
    
    if (empty($data['employee_id']) || empty($data['first_name']) || empty($data['last_name'])) {
        $error = 'Employee ID, First Name, and Last Name are required.';
    } else {
        try {
            $teacherModel->create($data);
            setFlash('success', 'Teacher added successfully.');
            redirect('/modules/teachers/');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$departments = $teacherModel->getDepartments();

require __DIR__ . '/../../includes/header.php';
?>

<div class="form-page">
    <div class="form-page-header">
        <h2>Add New Teacher</h2>
        <p>Create a new faculty record in the system</p>
    </div>

    <?php if ($error): ?>
        <div class="flash flash-error"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <!-- Basic Information -->
        <div class="premium-card">
            <div class="card-top indigo"></div>
            <div class="card-head">
                <div class="head-icon">👤</div>
                <div class="head-text">
                    <h4>Basic Information</h4>
                    <p>Personal and employment details</p>
                </div>
            <div class="card-body">
                <div class="premium-row">
                    <div class="premium-group">
                        <label for="employee_id">Employee ID <span class="req">*</span></label>
                        <input type="text" id="employee_id" name="employee_id" required placeholder="e.g. EMP-2024-001" value="<?= sanitize($_POST['employee_id'] ?? '') ?>">
                    </div>
                    <div class="premium-group">
                        <label for="department">Department</label>
                        <select id="department" name="department">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= sanitize($d) ?>" <?= ($_POST['department'] ?? '') === $d ? 'selected' : '' ?>><?= sanitize($d) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <div class="premium-row">
                    <div class="premium-group">
                        <label for="first_name">First Name <span class="req">*</span></label>
                        <input type="text" id="first_name" name="first_name" required placeholder="e.g. Maria" value="<?= sanitize($_POST['first_name'] ?? '') ?>">
                    </div>
                    <div class="premium-group">
                        <label for="last_name">Last Name <span class="req">*</span></label>
                        <input type="text" id="last_name" name="last_name" required placeholder="e.g. Santos" value="<?= sanitize($_POST['last_name'] ?? '') ?>">
                    </div>
                <div class="premium-row">
                    <div class="premium-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="maria.santos@school.edu" value="<?= sanitize($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="premium-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" placeholder="+63 912 345 6789" value="<?= sanitize($_POST['phone'] ?? '') ?>">
                    </div>
            </div>

        <!-- Load Configuration -->
        <div class="premium-card">
            <div class="card-top amber"></div>
            <div class="card-head">
                <div class="head-icon">⚖️</div>
                <div class="head-text">
                    <h4>Load Configuration</h4>
                    <p>Teaching load limits and employment status</p>
                </div>
            <div class="card-body">
                <div class="premium-row three-col">
                    <div class="premium-group">
                        <label for="max_units">Max Units</label>
                        <input type="number" step="0.5" id="max_units" name="max_units" value="<?= (float)($_POST['max_units'] ?? 24) ?>" min="1">
                    </div>
                    <div class="premium-group">
                        <label for="min_units">Min Units</label>
                        <input type="number" step="0.5" id="min_units" name="min_units" value="<?= (float)($_POST['min_units'] ?? 12) ?>" min="1">
                    </div>
                    <div class="premium-group">
                        <label for="employment_type">Employment Type</label>
                        <select id="employment_type" name="employment_type">
                            <option value="full_time" <?= ($_POST['employment_type'] ?? 'full_time') === 'full_time' ? 'selected' : '' ?>>Full Time</option>
                            <option value="part_time" <?= ($_POST['employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                            <option value="contractual" <?= ($_POST['employment_type'] ?? '') === 'contractual' ? 'selected' : '' ?>>Contractual</option>
                        </select>
                    </div>
                <div class="premium-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active" <?= ($_POST['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="on_leave" <?= ($_POST['status'] ?? '') === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
                    </select>
                </div>
        </div>

        <div class="premium-actions">
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Save Teacher
            </button>
            <a href="<?= BASE_URL ?>/modules/teachers/" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
