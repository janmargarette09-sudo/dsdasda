<?php
// modules/teachers/index.php — Teacher list with search/filter
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Teacher.php';
requireAuth();

$pageTitle = 'Teachers';
$extraCss = [];
$extraJs = ['/assets/js/teachers.js'];

$teacherModel = new Teacher();

// Parse filters
$filters = [
    'search' => $_GET['search'] ?? '',
    'department' => $_GET['department'] ?? '',
    'status' => $_GET['status'] ?? '',
    'employment_type' => $_GET['employment_type'] ?? ''
];
$page = max(1, (int)($_GET['page'] ?? 1));

$result = $teacherModel->getAll($filters, $page);
$teachers = $result['data'];
$pagination = paginate($result['total'], $page);

$departments = $teacherModel->getDepartments();

require __DIR__ . '/../../includes/header.php';
?>

<div class="page-toolbar">
    <h2>Teachers</h2>
    <div class="toolbar-actions">
        <a href="<?= BASE_URL ?>/modules/teachers/import.php" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Import
        </a>
        <a href="<?= BASE_URL ?>/modules/teachers/create.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Teacher
        </a>
    </div>
</div>

<div class="filter-bar">
    <form method="GET" class="filter-form">
        <input type="text" name="search" placeholder="Search name, ID, email..." 
               value="<?= htmlspecialchars($filters['search']) ?>" class="filter-input">
        
        <select name="department" class="filter-select">
            <option value="">All Departments</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?= htmlspecialchars($dept) ?>" <?= $filters['department'] === $dept ? 'selected' : '' ?>>
                    <?= htmlspecialchars($dept) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <select name="status" class="filter-select">
            <option value="">All Status</option>
            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            <option value="on_leave" <?= $filters['status'] === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
        </select>
        
        <select name="employment_type" class="filter-select">
            <option value="">All Types</option>
            <option value="full_time" <?= $filters['employment_type'] === 'full_time' ? 'selected' : '' ?>>Full Time</option>
            <option value="part_time" <?= $filters['employment_type'] === 'part_time' ? 'selected' : '' ?>>Part Time</option>
            <option value="contractual" <?= $filters['employment_type'] === 'contractual' ? 'selected' : '' ?>>Contractual</option>
        </select>
        
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="<?= BASE_URL ?>/modules/teachers/" class="btn btn-ghost">Reset</a>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Load</th>
                    <th>Status</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($teachers)): ?>
                    <tr>
                        <td colspan="7" class="empty-cell">No teachers found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($teachers as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['employee_id']) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/modules/teachers/view.php?id=<?= $t['id'] ?>" class="link-primary">
                                    <?= htmlspecialchars($t['last_name'] . ', ' . $t['first_name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($t['department'] ?? '—') ?></td>
                            <td><?= ucwords(str_replace('_', ' ', $t['employment_type'])) ?></td>
                            <td>
                                <div class="load-indicator">
                                    <?php 
                                    $pct = $t['max_units'] > 0 ? ($t['current_load'] / $t['max_units']) * 100 : 0;
                                    $barClass = $pct > 100 ? 'danger' : ($pct > 85 ? 'warning' : 'success');
                                    ?>
                                    <div class="load-bar">
                                        <div class="load-fill load-<?= $barClass ?>" style="width:<?= min(100, $pct) ?>"></div>
                                    </div>
                                    <span class="load-text"><?= formatUnits($t['current_load']) ?> / <?= formatUnits($t['max_units']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?= $t['status'] === 'active' ? 'success' : ($t['status'] === 'on_leave' ? 'warning' : 'secondary') ?>">
                                    <?= ucwords(str_replace('_', ' ', $t['status'])) ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="<?= BASE_URL ?>/modules/teachers/view.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-ghost" title="View">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="<?= BASE_URL ?>/modules/teachers/edit.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-ghost" title="Edit">
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
                <a href="<?= buildQuery(['page' => $pagination['page'] - 1]) ?>" class="btn btn-sm btn-outline">← Prev</a>
            <?php endif; ?>
            <span class="page-info">Page <?= $pagination['page'] ?> of <?= $pagination['totalPages'] ?></span>
            <?php if ($pagination['hasNext']): ?>
                <a href="<?= buildQuery(['page' => $pagination['page'] + 1]) ?>" class="btn btn-sm btn-outline">Next →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
