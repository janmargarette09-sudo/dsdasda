<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Schedule.php';
require_once __DIR__ . '/../../models/Subject.php';
requireAuth();

$id = (int)($_GET['id'] ?? 0);
$scheduleModel = new Schedule();
$subjectModel = new Subject();

$schedule = $scheduleModel->getById($id);
if (!$schedule) {
    setFlash('error', 'Schedule not found.');
    redirect('/modules/schedules/');
}

$subjects = $subjectModel->getAllSimple();
$errors = [];

if ($_POST) {
    $data = [
        'subject_id' => (int)($_POST['subject_id'] ?? 0),
        'day_of_week' => $_POST['day_of_week'] ?? '',
        'start_time' => $_POST['start_time'] ?? '',
        'end_time' => $_POST['end_time'] ?? '',
        'room' => trim($_POST['room'] ?? ''),
        'section' => trim($_POST['section'] ?? ''),
        'school_year' => trim($_POST['school_year'] ?? ''),
        'semester' => $_POST['semester'] ?? '1st',
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    if (empty($data['subject_id'])) $errors[] = 'Subject is required.';
    if (empty($data['day_of_week'])) $errors[] = 'Day is required.';
    if (empty($data['start_time'])) $errors[] = 'Start time is required.';
    if (empty($data['end_time'])) $errors[] = 'End time is required.';
    if ($data['start_time'] >= $data['end_time']) $errors[] = 'End time must be after start time.';

    if (empty($errors)) {
        try {
            $scheduleModel->update($id, $data);
            setFlash('success', 'Schedule updated successfully.');
            redirect('/modules/schedules/');
        } catch (Exception $e) {
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Edit Schedule';
require __DIR__ . '/../../includes/header.php';
?>

<div class="page-toolbar">
    <h2>Edit Schedule</h2>
    <a href="<?= BASE_URL ?>/modules/schedules/" class="btn btn-outline">← Back to Schedules</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card form-card">
    <form method="POST" class="form-grid">
        <div class="form-row">
            <div class="form-group">
                <label for="subject_id">Subject *</label>
                <select id="subject_id" name="subject_id" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($_POST['subject_id'] ?? $schedule['subject_id']) == $s['id'] ? 'selected' : '' ?>>
                            <?= sanitize($s['code']) ?> — <?= sanitize($s['name']) ?> (<?= $s['units'] ?? 3.0 ?>u)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="day_of_week">Day *</label>
                <select id="day_of_week" name="day_of_week" required>
                    <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
                        <option value="<?= $d ?>" <?= ($_POST['day_of_week'] ?? $schedule['day_of_week']) === $d ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="start_time">Start Time *</label>
                <input type="time" id="start_time" name="start_time" required
                       value="<?= htmlspecialchars($_POST['start_time'] ?? substr($schedule['start_time'], 0, 5)) ?>">
            </div>
            <div class="form-group">
                <label for="end_time">End Time *</label>
                <input type="time" id="end_time" name="end_time" required
                       value="<?= htmlspecialchars($_POST['end_time'] ?? substr($schedule['end_time'], 0, 5)) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="room">Room</label>
                <input type="text" id="room" name="room"
                       value="<?= htmlspecialchars($_POST['room'] ?? $schedule['room'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="section">Section</label>
                <input type="text" id="section" name="section"
                       value="<?= htmlspecialchars($_POST['section'] ?? $schedule['section'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="school_year">School Year</label>
                <input type="text" id="school_year" name="school_year" placeholder="2024-2025"
                       value="<?= htmlspecialchars($_POST['school_year'] ?? $schedule['school_year'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="semester">Semester</label>
                <select id="semester" name="semester">
                    <option value="1st" <?= ($_POST['semester'] ?? $schedule['semester']) === '1st' ? 'selected' : '' ?>>1st Semester</option>
                    <option value="2nd" <?= ($_POST['semester'] ?? $schedule['semester']) === '2nd' ? 'selected' : '' ?>>2nd Semester</option>
                    <option value="summer" <?= ($_POST['semester'] ?? $schedule['semester']) === 'summer' ? 'selected' : '' ?>>Summer</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?= ($_POST['is_active'] ?? $schedule['is_active']) ? 'checked' : '' ?>>
                Active
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Schedule</button>
            <a href="<?= BASE_URL ?>/modules/schedules/" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

