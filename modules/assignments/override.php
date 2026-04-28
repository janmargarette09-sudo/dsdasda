<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Assignment.php';
require_once __DIR__ . '/../../models/Teacher.php';
require_once __DIR__ . '/../../models/Schedule.php';
require_once __DIR__ . '/../../services/ConflictDetector.php';
requireAuth();

$pageTitle = 'Manual Override';
$extraCss = ['/assets/css/premium-forms.css', '/assets/css/assignments.css'];
$extraJs = ['/assets/js/override.js'];

$assignment = new Assignment();
$teacherModel = new Teacher();
$scheduleModel = new Schedule();
$conflictDetector = new ConflictDetector();

$teachers = $teacherModel->getAll(['status' => 'active'], 1, 999)['data'] ?? [];
$currentSem = $_SESSION['current_semester'] ?? null;
$currentSY = $_SESSION['current_school_year'] ?? null;
$unassigned = $scheduleModel->getUnassignedSchedules($currentSem, $currentSY);

$error = '';
$warning = '';
$scheduleUnits = 0;
$teacherMaxUnits = 0;

if ($_POST) {
    $teacherId = (int)$_POST['teacher_id'];
    $scheduleId = (int)$_POST['schedule_id'];
    $schedule = $scheduleModel->getById($scheduleId);
    $teacher = $teacherModel->getById($teacherId);
    $scheduleUnits = $schedule['units'] ?? 3;
    $teacherMaxUnits = $teacher['max_units'] ?? 24;
    
    $hasConflict = $conflictDetector->hasScheduleConflict($teacherId, $schedule);
    $wouldOverload = $conflictDetector->isOverloaded($teacherId, $scheduleUnits);
    $hasRationale = !empty(trim($_POST['rationale'] ?? ''));
    
    // Block if conflict or overload exists and user didn't confirm override
    if (($hasConflict || $wouldOverload) && empty($_POST['force_override'])) {
        if ($hasConflict) {
            $warning = 'Selected teacher has a SCHEDULE CONFLICT with this time slot.';
        } else {
            $overage = (($teacher['current_load'] ?? 0) + $scheduleUnits) - $teacherMaxUnits;
            $warning = 'This assignment would EXCEED max units by ' . $overage . ' units.';
        }
        if (!$hasRationale) {
            $warning .= ' Please provide a rationale.';
        }
    } else {
        try {
            $assignment->create(
                $teacherId,
                $scheduleId,
                'manual',
                $_POST['rationale'] ?? 'Manual override by admin',
                $_SESSION['user_id'] ?? null,
                'active'
            );
            setFlash('success', 'Manual assignment created successfully.');
            redirect('index.php');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

require __DIR__ . '/../../includes/header.php';
?>

<div class="form-page">
    <div class="form-page-header">
        <h2>Manual Override</h2>
        <p>Manually assign a teacher to a schedule slot</p>
    </div>

    <?php if ($error): ?>
        <div class="flash flash-error"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <?php if ($warning): ?>
        <div class="flash flash-warning">
            <strong>⚠️ <?= sanitize($warning) ?></strong><br>
            <div style="margin-top:0.5rem;">Are you sure you want to proceed?</div>
            <form method="POST" style="display:inline; margin-top:0.5rem;">
                <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($_POST['teacher_id'] ?? '') ?>">
                <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($_POST['schedule_id'] ?? '') ?>">
                <input type="hidden" name="rationale" value="<?= htmlspecialchars($_POST['rationale'] ?? '') ?>">
                <input type="hidden" name="force_override" value="1">
                <button type="submit" class="btn btn-danger btn-sm">Yes, Force Override</button>
            </form>
            <a href="override.php" class="btn btn-sm btn-ghost">Cancel</a>
        </div>
    <?php endif; ?>

    <form method="POST" id="override-form">
        <!-- Assignment Selection -->
        <div class="premium-card">
            <div class="card-top rose"></div>
            <div class="card-head">
                <div class="head-icon">👤</div>
                <div class="head-text">
                    <h4>Select Teacher & Schedule</h4>
                    <p>Choose who teaches and when</p>
                </div>
            <div class="card-body">
                <div class="premium-row">
                    <div class="premium-group">
                        <label for="teacher_id">Teacher <span class="req">*</span></label>
                        <select id="teacher_id" name="teacher_id" required>
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $t): 
                                $loadPct = $t['max_units'] > 0 ? round(($t['current_load'] / $t['max_units']) * 100, 0) : 0;
                                $loadClass = $loadPct >= 100 ? 'text-danger' : ($loadPct >= 80 ? 'text-warning' : 'text-success');
                            ?>
                                <option value="<?= $t['id'] ?>" <?= ($_POST['teacher_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($t['last_name'] . ', ' . $t['first_name']) ?> — <?= $t['current_load'] ?>/<?= $t['max_units'] ?>u
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="premium-group">
                        <label for="schedule_id">Unassigned Schedule <span class="req">*</span></label>
                        <select id="schedule_id" name="schedule_id" required>
                            <option value="">Select Schedule</option>
                            <?php foreach ($unassigned as $s): 
                                $schedUnits = $s['units'] ?? 3; 
                            ?>
                                <option value="<?= $s['id'] ?>" <?= ($_POST['schedule_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($s['subject_code']) ?> — <?= sanitize($s['subject_name']) ?> (<?= $schedUnits ?>u) | <?= sanitize($s['day_of_week']) ?> <?= date('h:i A', strtotime($s['start_time'])) ?>-<?= date('h:i A', strtotime($s['end_time'])) ?> | <?= sanitize($s['room'] ?? '-') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <div class="premium-group">
                    <label for="rationale">Rationale / Notes <span class="req">*</span></label>
                    <textarea id="rationale" name="rationale" rows="3" required placeholder="Explain why this manual assignment is needed..."><?= htmlspecialchars($_POST['rationale'] ?? '') ?></textarea>
                    <small>Required for audit and conflict resolution</small>
                </div>
        </div>

        <div class="premium-actions">
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Assign
            </button>
            <a href="index.php" class="btn btn-ghost">Cancel</a>
        </div>
    </form>

    <!-- Current Assignments Board -->
    <div class="premium-card" style="margin-top:2rem;">
        <div class="card-top indigo"></div>
        <div class="card-head">
            <div class="head-icon">📋</div>
            <div class="head-text">
                <h4>Current Assignments</h4>
                <p>Live view of teacher loads</p>
            </div>
        <div class="card-body">
            <div id="assignment-board" class="assignment-board">
                <?php foreach ($teachers as $t): 
                    $teacherAssignments = $assignment->getTeacherAssignments($t['id']);
                ?>
                    <div class="assignment-column">
                        <div class="column-header">
                            <strong><?= sanitize($t['last_name']) ?></strong>
                            <span class="load-badge"><?= $t['current_load'] ?>/<?= $t['max_units'] ?></span>
                        </div>
                        <div class="column-items">
                            <?php foreach ($teacherAssignments as $ta): ?>
                                <div class="assignment-card">
                                    <div class="card-subject"><?= sanitize($ta['code']) ?></div>
                                    <div class="card-time"><?= sanitize($ta['day_of_week']) ?> <?= date('h:i A', strtotime($ta['start_time'])) ?></div>
                                    <div class="card-room"><?= sanitize($ta['room'] ?? '-') ?></div>
                            <?php endforeach; ?>
                            <?php if (empty($teacherAssignments)): ?>
                                <div class="empty-slot">No assignments</div>
                            <?php endif; ?>
                        </div>
                <?php endforeach; ?>
            </div>
    </div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
