<?php
// modules/subjects/create.php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Subject.php';
requireAuth();

$pageTitle = 'Add Subject';

$subjectModel = new Subject();
$error = '';

if ($_POST) {
    $data = [
        'code' => trim($_POST['code'] ?? ''),
        'name' => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'units' => (float)($_POST['units'] ?? 3),
        'lecture_hours' => (float)($_POST['lecture_hours'] ?? 3),
        'lab_hours' => (float)($_POST['lab_hours'] ?? 0),
        'department' => trim($_POST['department'] ?? ''),
        'semester' => $_POST['semester'] ?? '1st',
        'year_level' => (int)($_POST['year_level'] ?? 1),
        'is_active' => 1
    ];
    
    if (empty($data['code']) || empty($data['name'])) {
        $error = 'Subject Code and Name are required.';
    } else {
        try {
            $subjectModel->create($data);
            setFlash('success', 'Subject added successfully.');
            redirect('/modules/subjects/');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$departments = $subjectModel->getDepartments();

require __DIR__ . '/../../includes/header.php';
?>

<div class="form-wrapper wide">
    <!-- Breadcrumb Header -->
    <div class="page-header">
        <nav class="breadcrumb">
            <a href="<?= BASE_URL ?>/modules/dashboard/">Dashboard</a>
            <span class="breadcrumb-sep">/</span>
            <a href="<?= BASE_URL ?>/modules/subjects/">Subjects</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">Add New</span>
        </nav>
        <h1 class="page-heading">Add New Subject</h1>
        <p class="page-subheading">Add a course to the curriculum catalog</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="create-form">
        <!-- Subject Details -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="section-icon icon-teal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div class="section-title">
                    <h3>Subject Details</h3>
                    <p>Course identification and description</p>
                </div>
            </div>
            <div class="form-section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="code">Subject Code <span class="required">*</span></label>
                        <input type="text" id="code" name="code" required placeholder="e.g. CS101" value="<?= sanitize($_POST['code'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="name">Subject Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required placeholder="e.g. Introduction to Programming" value="<?= sanitize($_POST['name'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Brief course description..."><?= sanitize($_POST['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Course Configuration -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="section-icon icon-green">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                </div>
                <div class="section-title">
                    <h3>Course Configuration</h3>
                    <p>Units, hours, and academic placement</p>
                </div>
            </div>
            <div class="form-section-body">
                <div class="form-grid three-col">
                    <div class="form-group">
                        <label for="units">Units</label>
                        <input type="number" step="0.5" id="units" name="units" value="<?= (float)($_POST['units'] ?? 3) ?>" min="0.5">
                    </div>
                    <div class="form-group">
                        <label for="lecture_hours">Lecture Hours</label>
                        <input type="number" step="0.5" id="lecture_hours" name="lecture_hours" value="<?= (float)($_POST['lecture_hours'] ?? 3) ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label for="lab_hours">Lab Hours</label>
                        <input type="number" step="0.5" id="lab_hours" name="lab_hours" value="<?= (float)($_POST['lab_hours'] ?? 0) ?>" min="0">
                    </div>
                </div>
                <div class="form-grid three-col">
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= sanitize($d) ?>" <?= ($_POST['department'] ?? '') === $d ? 'selected' : '' ?>><?= sanitize($d) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester">
                            <option value="1st" <?= ($_POST['semester'] ?? '1st') === '1st' ? 'selected' : '' ?>>1st Semester</option>
                            <option value="2nd" <?= ($_POST['semester'] ?? '') === '2nd' ? 'selected' : '' ?>>2nd Semester</option>
                            <option value="summer" <?= ($_POST['semester'] ?? '') === 'summer' ? 'selected' : '' ?>>Summer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="year_level">Year Level</label>
                        <select id="year_level" name="year_level">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= (int)($_POST['year_level'] ?? 1) === $i ? 'selected' : '' ?>>Year <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions-bar">
            <button type="submit" class="btn btn-primary btn-lg">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Save Subject
            </button>
            <a href="<?= BASE_URL ?>/modules/subjects/" class="btn btn-ghost btn-lg">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

