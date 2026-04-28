<?php
// modules/schedules/create.php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Subject.php';
require_once __DIR__ . '/../../models/Schedule.php';
requireAuth();

$pageTitle = 'Add Schedule';
$extraCss = ['/assets/css/premium-forms.css'];

$subjectModel = new Subject();
$scheduleModel = new Schedule();
$error = '';

if ($_POST) {
    $data = [
        'subject_id' => (int)($_POST['subject_id'] ?? 0),
        'day_of_week' => $_POST['day_of_week'] ?? '',
        'start_time'  => $_POST['start_time'] ?? '',
        'end_time'    => $_POST['end_time'] ?? '',
        'room'        => trim($_POST['room'] ?? ''),
        'section'     => trim($_POST['section'] ?? ''),
        'school_year' => trim($_POST['school_year'] ?? ($_SESSION['current_school_year'] ?? '2024-2025')),
        'semester'    => $_POST['semester'] ?? ($_SESSION['current_semester'] ?? '1st'),
        'is_active'   => 1
    ];
    
    if ($data['subject_id'] <= 0 || empty($data['day_of_week']) || empty($data['start_time']) || empty($data['end_time'])) {
        $error = 'Subject, Day, Start Time, and End Time are required.';
    } elseif ($data['start_time'] >= $data['end_time']) {
        $error = 'End time must be after start time.';
    } else {
        try {
            $scheduleModel->create($data);
            setFlash('success', 'Schedule created successfully.');
            redirect('/modules/schedules/');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$subjects = $subjectModel->getAll(['is_active' => 1], 1, 999)['data'] ?? [];

require __DIR__ . '/../../includes/header.php';
?>

<div class="form-page">
    <div class="form-page-header">
        <h2>Add New Schedule</h2>
        <p>Create a class time slot for a subject</p>
    </div>

    <?php if ($error): ?>
        <div class="flash flash-error"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <!-- Schedule Details -->
        <div class="premium-card">
            <div class="card-top purple"></div>
            <div class="card-head">
                <div class="head-icon">🗓️</div>
                <div class="head-text">
                    <h4>Schedule Details</h4>
                    <p>Subject, day, time, and location</p>
                </div>
            <div class="card-body">
                <div class="premium-group">
                    <label for="subject_id">Subject <span class="req">*</span></label>
                    <select id="subject_id" name="subject_id" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= (int)($_POST['subject_id'] ?? 0) === $s['id'] ? 'selected' : '' ?>>
                                <?= sanitize($s['code']) ?> &mdash; <?= sanitize($s['name']) ?> (<?= $s['units'] ?>u)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="premium-row three-col">
                    <div class="premium-group">
                        <label for="day_of_week">Day <span class="req">*</span></label>
                        <select id="day_of_week" name="day_of_week" required>
                            <option value="">Select Day</option>
                            <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day): ?>
                                <option value="<?= $day ?>" <?= ($_POST['day_of_week'] ?? '') === $day ? 'selected' : '' ?>><?= $day ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="premium-group">
                        <label for="start_time">Start Time <span class="req">*</span></label>
                        <input type="time" id="start_time" name="start_time" required value="<?= sanitize($_POST['start_time'] ?? '') ?>">
                    </div>
                    <div class="premium-group">
                        <label for="end_time">End Time <span class="req">*</span></label>
                        <input type="time" id="end_time" name="end_time" required value="<?= sanitize($_POST['end_time'] ?? '') ?>">
                    </div>
                <div class="premium-row">
                    <div class="premium-group">
                        <label for="room">Room</label>
                        <input type="text" id="room" name="room" placeholder="e.g. Room 301" value="<?= sanitize($_POST['room'] ?? '') ?>">
                    </div>
                    <div class="premium-group">
                        <label for="section">Section</label>
                        <input type="text" id="section" name="section" placeholder="e.g. BSCS-3A" value="<?= sanitize($_POST['section'] ?? '') ?>">
                    </div>
            </div>

        <!-- Academic Context -->
        <div class="premium-card">
            <div class="card-top rose"></div>
            <div class="card-head">
                <div class="head-icon">🎓</div>
                <div class="head-text">
                    <h4>Academic Context</h4>
                    <p>School year and semester placement</p>
                </div>
            <div class="card-body">
                <div class="premium-row">
                    <div class="premium-group">
                        <label for="school_year">School Year</label>
                        <input type="text" id="school_year" name="school_year" value="<?= sanitize($_POST['school_year'] ?? ($_SESSION['current_school_year'] ?? '2024-2025')) ?>">
                    </div>
                    <div class="premium-group">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester">
                            <option value="1st" <?= ($_POST['semester'] ?? ($_SESSION['current_semester'] ?? '1st')) === '1st' ? 'selected' : '' ?>>1st Semester</option>
                            <option value="2nd" <?= ($_POST['semester'] ?? '') === '2nd' ? 'selected' : '' ?>>2nd Semester</option>
                            <option value="summer" <?= ($_POST['semester'] ?? '') === 'summer' ? 'selected' : '' ?>>Summer</option>
                        </select>
                    </div>
            </div>

        <div class="premium-actions">
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Save Schedule
            </button>
            <a href="<?= BASE_URL ?>/modules/schedules/" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
