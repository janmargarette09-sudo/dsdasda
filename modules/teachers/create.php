<?php

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Teacher.php';
requireAuth();

$pageTitle = 'Add Teacher';
$extraJs = ['/assets/js/teachers.js'];

$teacherModel = new Teacher();
$error = '';
$success = '';

// STEP 1: Process form submission (runs when user clicks "Save")
if ($_POST) {
    // Collect and clean all form data
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
    
    // STEP 2: Validate required fields
    if (empty($data['employee_id']) || empty($data['first_name']) || empty($data['last_name'])) {
        $error = 'Employee ID, First Name, and Last Name are required.';
    } else {
        // STEP 3: Save to database
        try {
            $teacherModel->create($data);
            setFlash('success', 'Teacher added successfully.');
            redirect('/modules/teachers/');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Get list of departments for the dropdown
$departments = $teacherModel->getDepartments();

// Include the shared header (creates sidebar, topbar, etc.)
require __DIR__ . '/../../includes/header.php';
?>

<!--
    MAIN CONTENT AREA
    Everything inside here appears in the white content area of the dashboard.
    The sidebar and topbar are already created by header.php.
-->
<div class="form-wrapper wide">

    <!--
        BREADCRUMB HEADER
        This helps users understand WHERE they are in the app.
        It's like a trail of breadcrumbs in a forest - shows the path back.
        Dashboard → Teachers → Add New
    -->
    <div class="page-header">
        <nav class="breadcrumb">
            <a href="<?= BASE_URL ?>/modules/dashboard/">Dashboard</a>
            <span class="breadcrumb-sep">/</span>
            <a href="<?= BASE_URL ?>/modules/teachers/">Teachers</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">Add New</span>
        </nav>
        <h1 class="page-heading">Add New Teacher</h1>
        <p class="page-subheading">Create a new faculty record in the system</p>
    </div>

    <!--
        ERROR MESSAGE
        If validation failed, show a red box with the error.
        The "alert" classes make it look like a warning box.
    -->
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <!--
        THE ACTUAL FORM
        method="POST" means data is sent securely (not visible in URL).
        Each input needs a "name" attribute - that's the key PHP uses
        to access the data in $_POST.
    -->
    <form method="POST" class="create-form">

        <!--
            SECTION 1: Basic Information
            .form-section = white card with border and shadow
            .form-section-header = top gray bar with icon + title
            .form-section-body = area containing the actual fields
            
            Why use sections? It groups related fields visually,
            making the form easier to scan and understand.
        -->
        <div class="form-section">
            <div class="form-section-header">
                <!--
                    SECTION ICON
                    .section-icon = square container (36x36px)
                    .icon-indigo = gradient background color
                    The SVG inside is a "user" icon drawn with code.
                    Using SVG instead of emoji = looks professional on all devices.
                -->
                <div class="section-icon icon-indigo">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="section-title">
                    <h3>Basic Information</h3>
                    <p>Personal and employment details</p>
                </div>
            </div>
            <div class="form-section-body">
                <!--
                    GRID ROW 1: Employee ID + Department
                    .form-grid creates a 2-column layout.
                    On small screens (phones), it becomes 1 column.
                    This keeps related fields side by side for easy scanning.
                -->
                <div class="form-grid">
                    <div class="form-group">
                        <label for="employee_id">Employee ID <span class="required">*</span></label>
                        <input type="text" id="employee_id" name="employee_id" required placeholder="e.g. EMP-2024-001" value="<?= sanitize($_POST['employee_id'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= sanitize($d) ?>" <?= ($_POST['department'] ?? '') === $d ? 'selected' : '' ?>><?= sanitize($d) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <!-- GRID ROW 2: First Name + Last Name (logical pairing) -->
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" required placeholder="e.g. Maria" value="<?= sanitize($_POST['first_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" required placeholder="e.g. Santos" value="<?= sanitize($_POST['last_name'] ?? '') ?>">
                    </div>
                </div>
                <!-- GRID ROW 3: Email + Phone (contact info together) -->
                <div class="form-grid">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="maria.santos@school.edu" value="<?= sanitize($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" placeholder="+63 912 345 6789" value="<?= sanitize($_POST['phone'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!--
            SECTION 2: Load Configuration
            Different icon color (amber) helps visually separate sections.
            .form-grid.three-col = 3 columns for small related fields.
            This is more compact than stacking them vertically.
        -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="section-icon icon-amber">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-6M6 20V10M18 20V4"/></svg>
                </div>
                <div class="section-title">
                    <h3>Load Configuration</h3>
                    <p>Teaching load limits and employment status</p>
                </div>
            </div>
            <div class="form-section-body">
                <!--
                    THREE-COLUMN GRID
                    Perfect for small number inputs side by side.
                    Max Units | Min Units | Employment Type
                -->
                <div class="form-grid three-col">
                    <div class="form-group">
                        <label for="max_units">Max Units</label>
                        <input type="number" step="0.5" id="max_units" name="max_units" value="<?= (float)($_POST['max_units'] ?? 24) ?>" min="1">
                    </div>
                    <div class="form-group">
                        <label for="min_units">Min Units</label>
                        <input type="number" step="0.5" id="min_units" name="min_units" value="<?= (float)($_POST['min_units'] ?? 12) ?>" min="1">
                    </div>
                    <div class="form-group">
                        <label for="employment_type">Employment Type</label>
                        <select id="employment_type" name="employment_type">
                            <option value="full_time" <?= ($_POST['employment_type'] ?? 'full_time') === 'full_time' ? 'selected' : '' ?>>Full Time</option>
                            <option value="part_time" <?= ($_POST['employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                            <option value="contractual" <?= ($_POST['employment_type'] ?? '') === 'contractual' ? 'selected' : '' ?>>Contractual</option>
                        </select>
                    </div>
                </div>
                <!-- Single field row: Status doesn't need a partner -->
                <div class="form-grid">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active" <?= ($_POST['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="on_leave" <?= ($_POST['status'] ?? '') === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!--
            FORM ACTIONS BAR
            This is the final row with Save and Cancel buttons.
            .btn-primary = filled gradient button (main action)
            .btn-ghost = transparent button (secondary action)
            The SVG inside the Save button is a "plus" icon.
        -->
        <div class="form-actions-bar">
            <button type="submit" class="btn btn-primary btn-lg">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Save Teacher
            </button>
            <a href="<?= BASE_URL ?>/modules/teachers/" class="btn btn-ghost btn-lg">Cancel</a>
        </div>
    </form>
</div>

<!-- Include the shared footer (closes HTML tags, loads scripts) -->
<?php require __DIR__ . '/../../includes/footer.php'; ?>

