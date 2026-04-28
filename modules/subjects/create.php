<?php
// modules/subjects/create.php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Subject.php';
requireAuth();

$pageTitle = 'Add Subject';
$extraCss = ['/assets/css/premium-forms.css'];

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

<div class="form-page">
    <div class="form-page-header">
        <h2>Add New Subject</h2>
        <p>Add a course to the curriculum catalog</p>
    </div>

    <?php if ($error): ?>
        <div class="flash flash-error"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <!-- Subject Details -->
        <div class="premium-card">
            <div class="card-top teal"></div>
            <div class="card-head">
                <div class="head-icon">📚</div>
                <div class="head-text">
                    <h4>Subject Details</h4>
                    <p>Course identification and description</p>
                </div>
            <div class="card-body">
                <div class="premium-row">
                    <div class="premium-group">
                        <label for="code">Subject Code <span class="req">*</span></label>
                        <input type="text" id="code" name="code" required placeholder="e.g. CS101" value="<?= sanitize($_POST['code'] ?? '') ?>">
                    </div>
                    <div class="premium-group">
                        <label for="name">Subject Name <span class="req">*</span></label>
                        <input type="text" id="name" name="name" required placeholder="e.g. Introduction to Programming" value="<?= sanitize($_POST['name'] ?? '') ?>">
                    </div>
                <div class="premium-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Brief course description..."><?= sanitize($_POST['description'] ?? '') ?></textarea>
                </div>
        </div>

        <!-- Course Configuration -->
        <div class="premium-card">
            <div class="card-top green"></div>
            <div class="card-head">
                <div class="head-icon">⚙️</div>
                <div class="head-text">
                    <h4>Course Configuration</h4>
                    <p>Units, hours, and academic placement</p>
                </div>
            <div class="card-body">
                <div class="premium-row three-col">
                    <div class="premium-group">
                        <label for="units">Units</label>
                        <input type="number" step="0.5" id="units" name="units" value="<?= (float)($_POST['units'] ?? 3) ?>" min="0.5">
                    </div>
                    <div class="premium-group">
                        <label for="lecture_hours">Lecture Hours</label>
                        <input type="number" step="0.5" id="lecture_hours" name="lecture_hours" value="<?= (float)($_POST['lecture_hours'] ?? 3) ?>" min="0">
                    </div>
                    <div class="premium-group">
                        <label for="lab_hours">Lab Hours</label>
                        <input type="number" step="0.5" id="lab_hours" name="lab_hours" value="<?= (float)($_POST['lab_hours'] ?? 0) ?>" min="0">
                    </div>
                <div class="premium-row three-col">
                    <div class="premium-group">
                        <label for="department">Department</label>
                        <select id="department" name="department">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= sanitize($d) ?>" <?= ($_POST['department'] ?? '') === $d ? 'selected' : '' ?>><?= sanitize($d) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="premium-group">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester">
                            <option value="1st" <?= ($_POST['semester'] ?? '1st') === '1st' ? 'selected' : '' ?>>1st Semester</option>
                            <option value="2nd" <?= ($_POST['semester'] ?? '') === '2nd' ? 'selected' : '' ?>>2nd Semester</option>
                            <option value="summer" <?= ($_POST['semester'] ?? '') === 'summer' ? 'selected' : '' ?>>Summer</option>
                        </select>
                    </div>
                    <div class="premium-group">
                        <label for="year_level">Year Level</label>
                        <select id="year_level" name="year_level">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= (int)($_POST['year_level'] ?? 1) === $i ? 'selected' : '' ?>>Year <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
            </div>

        <div class="premium-actions">
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Save Subject
            </button>
            <a href="<?= BASE_URL ?>/modules/subjects/" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
