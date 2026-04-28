<?php
// modules/schedules/create.php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Subject.php';
require_once __DIR__ . '/../../models/Schedule.php';
requireAuth();

$pageTitle = 'Add Schedule';

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

<div class="form-wrapper wide">
    <!-- Breadcrumb Header -->
    <div class="page-header">
        <nav class="breadcrumb">
            <a href="<?= BASE_URL ?>/modules/dashboard/">Dashboard</a>
            <span class="breadcrumb-sep">/</span>
            <a href="<?= BASE_URL ?>/modules/schedules/">Schedules</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">Add New</span>
        </nav>
        <h1 class="page-heading">Add New Schedule</h1>
        <p class="page-subheading">Create a class time slot for a subject</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="create-form">
        <!-- Schedule Details -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="section-icon icon-purple">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="section-title">
                    <h3>Schedule Details</h3>
                    <p>Subject, day, time, and location</p>
                </div>
            </div>
            <div class="form-section-body">
                <div class="form-group full-width">
                    <label for="subject_id">Subject <span class="required">*</span></label>
                    <select id="subject_id" name="subject_id" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= (int)($_POST['subject_id'] ?? 0) === $s['id'] ? 'selected' : '' ?>>
                                <?= sanitize($s['code']) ?> &mdash; <?= sanitize($s['name']) ?> (<?= $s['units'] ?>u)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grid three-col">
                    <div class="form-group">
                        <label for="day_of_week">Day <span class="required">*</span></label>
                        <select id="day_of_week" name="day_of_week" required>
                            <option value="">Select Day</option>
                            <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day): ?>
                                <option value="<?= $day ?>" <?= ($_POST['day_of_week'] ?? '') === $day ? 'selected' : '' ?>><?= $day ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="start_time">Start Time <span class="required">*</span></label>
                        <input type="time" id="start_time" name="start_time" required value="<?= sanitize($_POST['start_time'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time <span class="required">*</span></label>
                        <input type="time" id="end_time" name="end_time" required value="<?= sanitize($_POST['end_time'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="room">Room</label>
                        <input type="text" id="room" name="room" placeholder="e.g. Room 301" value="<?= sanitize($_POST['room'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="section">Section</label>
                        <input type="text" id="section" name="section" placeholder="e.g. BSCS-3A" value="<?= sanitize($_POST['section'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Context -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="section-icon icon-rose">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.66 4 3 9 3s9-1.34 9-3v-5"/></svg>
                </div>
                <div class="section-title">
                    <h3>Academic Context</h3>
                    <p>School year and semester placement</p>
                </div>
            </div>
            <div class="form-section-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="school_year">School Year</label>
                        <input type="text" id="school_year" name="school_year" value="<?= sanitize($_POST['school_year'] ?? ($_SESSION['current_school_year'] ?? '2024-2025')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester">
                            <option value="1st" <?= ($_POST['semester'] ?? ($_SESSION['current_semester'] ?? '1st')) === '1st' ? 'selected' : '' ?>>1st Semester</option>
                            <option value="2nd" <?= ($_POST['semester'] ?? '') === '2nd' ? 'selected' : '' ?>>2nd Semester</option>
                            <option value="summer" <?= ($_POST['semester'] ?? '') === 'summer' ? 'selected' : '' ?>>Summer</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions-bar">
            <button type="submit" class="btn btn-primary btn-lg">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Save Schedule
            </button>
            <a href="<?= BASE_URL ?>/modules/schedules/" class="btn btn-ghost btn-lg">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

