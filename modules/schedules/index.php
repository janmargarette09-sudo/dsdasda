<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Schedule.php';
require_once __DIR__ . '/../../models/Subject.php';
requireAuth();

$pageTitle = 'Schedules';
$extraCss = [];

$model = new Schedule();
$filters = [
    'day' => $_GET['day'] ?? '',
    'room' => $_GET['room'] ?? '',
    'subject_id' => $_GET['subject_id'] ?? '',
    'semester' => $_GET['semester'] ?? ''
];
$page = (int)($_GET['page'] ?? 1);
$result = $model->getAll($filters, $page);
$schedules = $result['data'];
$pagination = $result;

$subjectModel = new Subject();
$subjects = $subjectModel->getAllSimple();
$rooms = $model->getRooms();

require __DIR__ . '/../../includes/header.php';
?>

<div class="page-toolbar">
    <h2>Schedules</h2>
    <div class="toolbar-actions">
        <a href="<?= BASE_URL ?>/modules/schedules/import.php" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Import
        </a>
        <a href="<?= BASE_URL ?>/modules/schedules/create.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Schedule
        </a>
    </div>
</div>

<div class="filter-bar">
    <form method="GET" class="filter-form">
        <select name="day" class="filter-select">
            <option value="">All Days</option>
            <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
                <option value="<?= $d ?>" <?= $filters['day'] === $d ? 'selected' : '' ?>><?= $d ?></option>
            <?php endforeach; ?>
        </select>
        <select name="subject_id" class="filter-select">
            <option value="">All Subjects</option>
            <?php foreach ($subjects as $s): ?>
                <option value="<?= $s['id'] ?>" <?= $filters['subject_id'] == $s['id'] ? 'selected' : '' ?>><?= sanitize($s['code']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="room" class="filter-select">
            <option value="">All Rooms</option>
            <?php foreach ($rooms as $r): ?>
                <option value="<?= sanitize($r) ?>" <?= $filters['room'] === $r ? 'selected' : '' ?>><?= sanitize($r) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="<?= BASE_URL ?>/modules/schedules/" class="btn btn-ghost">Reset</a>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Duration</th>
                    <th>Room</th>
                    <th>Section</th>
                    <th>Semester</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="8" class="empty-cell">
                            <div class="empty-state">
                                <div class="empty-state-icon">🗓️</div>
                                <div>No schedules found.</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schedules as $s): ?>
                        <tr>
                            <td><strong><?= sanitize($s['subject_code']) ?></strong><br><small><?= sanitize($s['subject_name']) ?></small></td>
                            <td><?= sanitize($s['day_of_week']) ?></td>
                            <td><?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?></td>
                            <td><?= round((strtotime($s['end_time']) - strtotime($s['start_time'])) / 3600, 1) ?> hrs</td>
                            <td><?= sanitize($s['room'] ?? '-') ?></td>
                            <td><?= sanitize($s['section'] ?? '-') ?></td>
                            <td><?= sanitize($s['semester']) ?></td>
                            <td class="actions">
                                <a href="<?= BASE_URL ?>/modules/schedules/edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-ghost" title="Edit">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
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

<?php require __DIR__ . '/../../includes/footer.php'; ?>

