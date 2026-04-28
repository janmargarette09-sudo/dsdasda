<?php
// modules/teachers/edit.php — Edit teacher + availability calendar
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Teacher.php';
require_once __DIR__ . '/../../models/Subject.php';
requireAuth();

$id = (int)($_GET['id'] ?? 0);
$teacherModel = new Teacher();
$teacher = $teacherModel->getById($id);

if (!$teacher) {
    setFlash('error', 'Teacher not found.');
    redirect('/modules/teachers/');
}

$errors = [];
if ($_POST) {
    $data = [
        'employee_id' => trim($_POST['employee_id'] ?? ''),
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'department' => trim($_POST['department'] ?? ''),
        'max_units' => (float)($_POST['max_units'] ?? 24),
        'min_units' => (float)($_POST['min_units'] ?? 12),
        'employment_type' => $_POST['employment_type'] ?? 'full_time',
        'status' => $_POST['status'] ?? 'active',
        'expertise' => [],
        'availability' => []
    ];

    if (empty($data['employee_id'])) $errors[] = 'Employee ID is required.';
    if (empty($data['first_name'])) $errors[] = 'First name is required.';
    if (empty($data['last_name'])) $errors[] = 'Last name is required.';

    // Parse expertise
    if (!empty($_POST['expertise_areas'])) {
        foreach ($_POST['expertise_areas'] as $i => $area) {
            if (trim($area)) {
                $data['expertise'][] = [
                    'subject_area' => trim($area),
                    'proficiency_level' => $_POST['expertise_levels'][$i] ?? 'primary'
                ];
            }
        }
    }

    // Parse availability
    $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    foreach ($days as $day) {
        if (!empty($_POST['avail_day']) && in_array($day, $_POST['avail_day'])) {
            foreach ($_POST['avail_start'][$day] ?? [] as $i => $start) {
                $end = $_POST['avail_end'][$day][$i] ?? '';
                if ($start && $end) {
                    $data['availability'][] = [
                        'day_of_week' => $day,
                        'start_time' => $start . ':00',
                        'end_time' => $end . ':00',
                        'is_preferred' => 1
                    ];
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $teacherModel->update($id, $data);
            setFlash('success', 'Teacher updated successfully.');
            redirect('/modules/teachers/view.php?id=' . $id);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $errors[] = 'Employee ID already exists.';
            } else {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}



$departments = $teacherModel->getDepartments();
$subjectModel = new Subject();
$subjectNames = $subjectModel->getAllSubjectNames();
$pageTitle = 'Edit Teacher';
$extraJs = ['/assets/js/teachers.js'];

require __DIR__ . '/../../includes/header.php';
?>

<div class="page-toolbar">
    <h2>Edit Teacher</h2>
    <a href="<?= BASE_URL ?>/modules/teachers/view.php?id=<?= $teacher['id'] ?>" class="btn btn-outline">← Back to Profile</a>
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
                <label for="employee_id">Employee ID *</label>
                <input type="text" id="employee_id" name="employee_id" required
                       value="<?= htmlspecialchars($_POST['employee_id'] ?? $teacher['employee_id']) ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? $teacher['email'] ?? '') ?>">
            </div>

        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" required
                       value="<?= htmlspecialchars($_POST['first_name'] ?? $teacher['first_name']) ?>">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" required
                       value="<?= htmlspecialchars($_POST['last_name'] ?? $teacher['last_name']) ?>">
            </div>

        <div class="form-row">
            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" id="department" name="department" list="dept-list"
                       value="<?= htmlspecialchars($_POST['department'] ?? $teacher['department'] ?? '') ?>">
                <datalist id="dept-list">
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= htmlspecialchars($d) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone"
                       value="<?= htmlspecialchars($_POST['phone'] ?? $teacher['phone'] ?? '') ?>">
            </div>

        <div class="form-row">
            <div class="form-group">
                <label for="employment_type">Employment Type</label>
                <select id="employment_type" name="employment_type">
                    <option value="full_time" <?= ($_POST['employment_type'] ?? $teacher['employment_type']) === 'full_time' ? 'selected' : '' ?>>Full Time</option>
                    <option value="part_time" <?= ($_POST['employment_type'] ?? $teacher['employment_type']) === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                    <option value="contractual" <?= ($_POST['employment_type'] ?? $teacher['employment_type']) === 'contractual' ? 'selected' : '' ?>>Contractual</option>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?= ($_POST['status'] ?? $teacher['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($_POST['status'] ?? $teacher['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="on_leave" <?= ($_POST['status'] ?? $teacher['status']) === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
                </select>
            </div>

        <div class="form-row">
            <div class="form-group">
                <label for="max_units">Max Units</label>
                <input type="number" id="max_units" name="max_units" step="0.5" min="0"
                       value="<?= htmlspecialchars($_POST['max_units'] ?? $teacher['max_units']) ?>">
            </div>
            <div class="form-group">
                <label for="min_units">Min Units</label>
                <input type="number" id="min_units" name="min_units" step="0.5" min="0"
                       value="<?= htmlspecialchars($_POST['min_units'] ?? $teacher['min_units']) ?>">
            </div>

        <div class="form-group">
            <label>Expertise / Specializations</label>
            <div id="expertise-rows">
                <?php
                $expertise = $_POST['expertise_areas'] ?? null;
                if ($expertise === null) {
                    $expertiseRows = $teacher['expertise'];
                } else {
                    $expertiseRows = [];
                    foreach ($expertise as $i => $area) {
                        if (trim($area)) $expertiseRows[] = [
                            'subject_area' => trim($area),
                            'proficiency_level' => $_POST['expertise_levels'][$i] ?? 'primary'
                        ];
                    }
                }
                if (empty($expertiseRows)) $expertiseRows = [['subject_area'=>'','proficiency_level'=>'primary']];
                ?>
                <?php foreach ($expertiseRows as $i => $e): ?>
                    <div class="expertise-row">
                        <select name="expertise_areas[]">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjectNames as $name): ?>
                                <option value="<?= htmlspecialchars($name) ?>" <?= $e['subject_area'] === $name ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="expertise_levels[]">
                            <option value="primary" <?= $e['proficiency_level'] === 'primary' ? 'selected' : '' ?>>Primary</option>
                            <option value="secondary" <?= $e['proficiency_level'] === 'secondary' ? 'selected' : '' ?>>Secondary</option>
                            <option value="tertiary" <?= $e['proficiency_level'] === 'tertiary' ? 'selected' : '' ?>>Tertiary</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-ghost remove-row" <?= $i === 0 && count($expertiseRows) === 1 ? 'style="display:none"' : '' ?>>✕</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline" id="add-expertise">+ Add Expertise</button>
        </div>

        <div class="form-group">
            <label>Availability Schedule</label>
            <div class="availability-editor">
                <?php
                $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                $existingAvail = [];
                foreach ($teacher['availability'] as $a) {
                    $existingAvail[$a['day_of_week']][] = [
                        'start' => substr($a['start_time'], 0, 5),
                        'end' => substr($a['end_time'], 0, 5)
                    ];
                }
                ?>
                <?php foreach ($days as $day): ?>
                    <div class="avail-day">
                        <label class="day-label">
                            <input type="checkbox" name="avail_day[]" value="<?= $day ?>"
                                <?= isset($existingAvail[$day]) ? 'checked' : '' ?>>
                            <?= $day ?>
                        </label>
                        <div class="day-slots" data-day="<?= $day ?>">
                            <?php
                            $slots = $existingAvail[$day] ?? [['start'=>'08:00','end'=>'17:00']];
                            foreach ($slots as $i => $slot):
                            ?>
                                <div class="slot-row">
                                    <input type="time" name="avail_start[<?= $day ?>][]" value="<?= $slot['start'] ?>">
                                    <span>to</span>
                                    <input type="time" name="avail_end[<?= $day ?>][]" value="<?= $slot['end'] ?>">
                                    <button type="button" class="btn btn-sm btn-ghost remove-slot" <?= count($slots) === 1 ? 'style="display:none"' : '' ?>>✕</button>
                                </div>
                            <?php endforeach; ?>
                            <button type="button" class="btn btn-sm btn-ghost add-slot">+ Add Slot</button>
                        </div>
                <?php endforeach; ?>
            </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Teacher</button>
            <a href="<?= BASE_URL ?>/modules/teachers/view.php?id=<?= $teacher['id'] ?>" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
