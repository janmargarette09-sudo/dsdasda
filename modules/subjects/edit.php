<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Subject.php';
requireAuth();

$pageTitle = 'Edit Subject';
$extraCss = ['/assets/css/components.css'];

$model = new Subject();
$id = (int)($_GET['id'] ?? 0);
$subject = $model->getById($id);

if (!$subject) {
    setFlash('error', 'Subject not found.');
    redirect('index.php');
}

$allSubjects = $model->getAllSimple();
$currentPrereqIds = array_column($subject['prerequisites'] ?? [], 'id');
$error = '';

if ($_POST) {
    try {
        $data = [
            'code' => trim($_POST['code']),
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'units' => (float)($_POST['units'] ?? 3),
            'lecture_hours' => (float)($_POST['lecture_hours'] ?? 3),
            'lab_hours' => (float)($_POST['lab_hours'] ?? 0),
            'department' => trim($_POST['department'] ?? ''),
            'semester' => $_POST['semester'] ?? '1st',
            'year_level' => (int)($_POST['year_level'] ?? 1),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'prerequisite_ids' => $_POST['prerequisite_ids'] ?? []
        ];
        $model->update($id, $data);
        setFlash('success', 'Subject updated successfully.');
        redirect('index.php');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<?php if ($error): ?>
    <div class="alert alert-error"><?= sanitize($error) ?></div>
<?php endif; ?>

<div class="card form-card">
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Subject Code *</label>
                <input type="text" name="code" required class="form-control" value="<?= sanitize($_POST['code'] ?? $subject['code']) ?>">
            </div>
            <div class="form-group">
                <label>Subject Name *</label>
                <input type="text" name="name" required class="form-control" value="<?= sanitize($_POST['name'] ?? $subject['name']) ?>">
            </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" class="form-control"><?= sanitize($_POST['description'] ?? $subject['description']) ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Units *</label>
                <input type="number" name="units" step="0.5" required class="form-control" value="<?= $_POST['units'] ?? $subject['units'] ?>">
            </div>
            <div class="form-group">
                <label>Lecture Hours</label>
                <input type="number" name="lecture_hours" step="0.5" class="form-control" value="<?= $_POST['lecture_hours'] ?? $subject['lecture_hours'] ?>">
            </div>
            <div class="form-group">
                <label>Lab Hours</label>
                <input type="number" name="lab_hours" step="0.5" class="form-control" value="<?= $_POST['lab_hours'] ?? $subject['lab_hours'] ?>">
            </div>

        <div class="form-row">
            <div class="form-group">
                <label>Department</label>
                <input type="text" name="department" class="form-control" value="<?= sanitize($_POST['department'] ?? $subject['department']) ?>">
            </div>
            <div class="form-group">
                <label>Semester</label>
                <select name="semester" class="form-control">
                    <option value="1st" <?= ($_POST['semester'] ?? $subject['semester']) === '1st' ? 'selected' : '' ?>>1st</option>
                    <option value="2nd" <?= ($_POST['semester'] ?? $subject['semester']) === '2nd' ? 'selected' : '' ?>>2nd</option>
                    <option value="summer" <?= ($_POST['semester'] ?? $subject['semester']) === 'summer' ? 'selected' : '' ?>>Summer</option>
                </select>
            </div>
            <div class="form-group">
                <label>Year Level</label>
                <select name="year_level" class="form-control">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= ($_POST['year_level'] ?? $subject['year_level']) == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>

        <div class="form-group">
            <label>Prerequisites</label>
            <select name="prerequisite_ids[]" multiple class="form-control" size="5">
                <?php foreach ($allSubjects as $s): ?>
                    <?php if ($s['id'] == $id) continue; ?>
                    <option value="<?= $s['id'] ?>" <?= in_array($s['id'], $currentPrereqIds) ? 'selected' : '' ?>>
                        <?= sanitize($s['code']) ?> — <?= sanitize($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" <?= ($subject['is_active'] ?? 1) ? 'checked' : '' ?>>
                Active
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Subject</button>
            <a href="index.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
