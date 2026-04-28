<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Assignment.php';
require_once __DIR__ . '/../../models/Teacher.php';
requireAuth();

// Handle semester/school year session settings
if ($_POST && isset($_POST['set_semester'])) {
    $_SESSION['current_semester'] = $_POST['semester'] ?? '1st';
    $_SESSION['current_school_year'] = $_POST['school_year'] ?? '2024-2025';
    setFlash('success', 'Viewing ' . $_SESSION['current_school_year'] . ' — ' . $_SESSION['current_semester'] . ' Semester');
    redirect('/modules/assignments/');
}

$pageTitle = 'Assignments';
$extraCss = ['/assets/css/assignments.css'];

$model = new Assignment();
$teacherModel = new Teacher();
$currentSem = $_SESSION['current_semester'] ?? '1st';
$currentSY = $_SESSION['current_school_year'] ?? '2024-2025';

$filters = [
    'teacher_id' => $_GET['teacher_id'] ?? '',
    'status' => $_GET['status'] ?? '',
    'assignment_type' => $_GET['type'] ?? '',
    'semester' => $currentSem,
    'school_year' => $currentSY
];
$page = (int)($_GET['page'] ?? 1);
$result = $model->getAll($filters, $page);
$assignments = $result['data'];
$pagination = $result;
$teachers = $teacherModel->getAll([], 1, 999)['data'] ?? [];

require __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0;">
    <form method="POST" class="filter-bar" style="gap: 1rem; align-items: center; padding: 1rem 1.25rem; margin-bottom: 0;">
        <input type="hidden" name="set_semester" value="1">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <label style="font-weight: 600; color: #475569; font-size: 0.9rem;">School Year:</label>
            <input type="text" name="school_year" value="<?= htmlspecialchars($currentSY) ?>" class="filter-input" style="width: 120px;" onchange="this.form.submit()">
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <label style="font-weight: 600; color: #475569; font-size: 0.9rem;">Semester:</label>
            <div style="display: flex; gap: 0.25rem;">
                <button type="submit" name="semester" value="1st" class="btn btn-sm <?= $currentSem === '1st' ? 'btn-primary' : 'btn-outline' ?>">1st</button>
                <button type="submit" name="semester" value="2nd" class="btn btn-sm <?= $currentSem === '2nd' ? 'btn-primary' : 'btn-outline' ?>">2nd</button>
                <button type="submit" name="semester" value="summer" class="btn btn-sm <?= $currentSem === 'summer' ? 'btn-primary' : 'btn-outline' ?>">Summer</button>
            </div>
        </div>
        <div style="margin-left: auto; font-size: 0.875rem; color: #64748b;">
            Currently viewing: <strong style="color: #334155;"><?= htmlspecialchars($currentSY) ?> — <?= htmlspecialchars($currentSem) ?> Semester</strong>
        </div>
    </form>
</div>

<div class="page-toolbar">
    <h2>Assignments</h2>
    <div class="toolbar-actions">
        <a href="<?= BASE_URL ?>/modules/assignments/conflicts.php" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Conflicts
        </a>
        <a href="<?= BASE_URL ?>/modules/assignments/override.php" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Manual Override
        </a>
        <a href="<?= BASE_URL ?>/modules/assignments/generate.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            Auto-Match
        </a>
    </div>
</div>

<div class="filter-bar">
    <form method="GET" class="filter-form">
        <select name="teacher_id" class="filter-select">
            <option value="">All Teachers</option>
            <?php foreach ($teachers as $t): ?>
                <option value="<?= $t['id'] ?>" <?= $filters['teacher_id'] == $t['id'] ? 'selected' : '' ?>>
                    <?= sanitize($t['last_name'] . ', ' . $t['first_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="filter-select">
            <option value="">All Status</option>
            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="removed" <?= $filters['status'] === 'removed' ? 'selected' : '' ?>>Removed</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="<?= BASE_URL ?>/modules/assignments/" class="btn btn-ghost">Reset</a>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Teacher</th>
                    <th>Subject</th>
                    <th>Schedule</th>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="7" class="empty-cell">
                            <div class="empty-state">
                                <div class="empty-state-icon">⚡</div>
                                <div>No assignments found for this semester.</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td><?= sanitize($a['last_name'] . ', ' . $a['first_name']) ?></td>
                            <td><strong><?= sanitize($a['subject_code']) ?></strong><br><small><?= sanitize($a['subject_name']) ?></small></td>
                            <td><?= sanitize($a['day_of_week']) ?> <?= date('h:i A', strtotime($a['start_time'])) ?>-<?= date('h:i A', strtotime($a['end_time'])) ?></td>
                            <td><?= sanitize($a['room'] ?? '-') ?></td>
                            <td><span class="badge badge-<?= $a['assignment_type'] === 'auto' ? 'info' : 'warning' ?>"><?= ucfirst($a['assignment_type']) ?></span></td>
                            <td><span class="badge badge-<?= $a['status'] === 'active' ? 'success' : ($a['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= ucfirst($a['status']) ?></span></td>
                            <td class="actions">
                                <?php if ($a['status'] === 'pending'): ?>
                                    <a href="#" onclick="activateAssignment(<?= $a['id'] ?>)" class="btn btn-sm btn-success" title="Activate">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    </a>
                                <?php endif; ?>
                                <a href="#" onclick="deleteAssignment(<?= $a['id'] ?>)" class="btn btn-sm btn-danger" title="Remove">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination['totalPages'] > 1): ?>
        <div class="pagination">
            <?php if ($pagination['hasPrev']): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm btn-outline">← Prev</a>
            <?php endif; ?>
            <span class="page-info">Page <?= $page ?> of <?= $pagination['totalPages'] ?> (<?= $pagination['total'] ?> total)</span>
            <?php if ($pagination['hasNext']): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm btn-outline">Next →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function activateAssignment(id) {
    if (!confirm('Activate this assignment?')) return;
    apiRequest('<?= BASE_URL ?>/api/assignments.php?id=' + id, {
        method: 'PUT',
        body: JSON.stringify({id: id, status: 'active'})
    })
    .then(() => location.reload())
    .catch(err => { alert('Error: ' + err.message); console.error(err); });
}
function deleteAssignment(id) {
    if (!confirm('Remove this assignment?')) return;
    apiRequest('<?= BASE_URL ?>/api/assignments.php?id=' + id, { method: 'DELETE' })
    .then(() => location.reload())
    .catch(err => { alert('Error: ' + err.message); console.error(err); });
}
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

