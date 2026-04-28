<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Subject.php';
requireAuth();

$pageTitle = 'Subjects';
$extraCss = [];

$model = new Subject();
$filters = [
    'search' => $_GET['search'] ?? '',
    'department' => $_GET['department'] ?? '',
    'semester' => $_GET['semester'] ?? '',
    'year_level' => $_GET['year_level'] ?? ''
];
$page = (int)($_GET['page'] ?? 1);
$result = $model->getAll($filters, $page);
$subjects = $result['data'];
$pagination = $result;
$departments = $model->getDepartments();

require __DIR__ . '/../../includes/header.php';
?>

<div class="page-toolbar">
    <h2>Subjects</h2>
    <div class="toolbar-actions">
        <a href="<?= BASE_URL ?>/modules/subjects/import.php" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Import
        </a>
        <a href="<?= BASE_URL ?>/modules/subjects/create.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Subject
        </a>
    </div>
</div>

<div class="filter-bar">
    <form method="GET" class="filter-form">
        <input type="text" name="search" placeholder="Search subjects..." value="<?= sanitize($filters['search']) ?>" class="filter-input">
        <select name="department" class="filter-select">
            <option value="">All Departments</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?= sanitize($d) ?>" <?= $filters['department'] === $d ? 'selected' : '' ?>><?= sanitize($d) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="semester" class="filter-select">
            <option value="">All Semesters</option>
            <option value="1st" <?= $filters['semester'] === '1st' ? 'selected' : '' ?>>1st</option>
            <option value="2nd" <?= $filters['semester'] === '2nd' ? 'selected' : '' ?>>2nd</option>
            <option value="summer" <?= $filters['semester'] === 'summer' ? 'selected' : '' ?>>Summer</option>
        </select>
        <select name="year_level" class="filter-select">
            <option value="">All Years</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>" <?= $filters['year_level'] == $i ? 'selected' : '' ?>>Year <?= $i ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="<?= BASE_URL ?>/modules/subjects/" class="btn btn-ghost">Reset</a>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Units</th>
                    <th>Dept</th>
                    <th>Sem</th>
                    <th>Year</th>
                    <th>Schedules</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subjects)): ?>
                    <tr>
                        <td colspan="8" class="empty-cell">
                            <div class="empty-state">
                                <div class="empty-state-icon">📚</div>
                                <div>No subjects found.</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subjects as $s): ?>
                        <tr>
                            <td><strong><?= sanitize($s['code']) ?></strong></td>
                            <td><?= sanitize($s['name']) ?></td>
                            <td><?= $s['units'] ?></td>
                            <td><?= sanitize($s['department'] ?? '-') ?></td>
                            <td><?= sanitize($s['semester']) ?></td>
                            <td><?= $s['year_level'] ?></td>
                            <td><?= $s['schedule_count'] ?></td>
                            <td class="actions">
                                <a href="<?= BASE_URL ?>/modules/subjects/view.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-ghost" title="View">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="<?= BASE_URL ?>/modules/subjects/edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-ghost" title="Edit">
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

