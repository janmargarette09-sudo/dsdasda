<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../services/ReportGenerator.php';
require_once __DIR__ . '/../../models/Teacher.php';
requireAuth();

$pageTitle = 'Load Reports';
$extraCss = ['/assets/css/reports.css'];
$extraJs = ['/assets/js/reports.js'];

$generator = new ReportGenerator();
$teacherModel = new Teacher();

$filters = ['department' => $_GET['department'] ?? ''];
$report = $generator->generateLoadReport($filters);
$departments = $teacherModel->getDepartments();

require __DIR__ . '/../../includes/header.php';
?>

<div class="page-toolbar">
    <h2>Load Assignment Report</h2>
    <div class="toolbar-actions">
        <a href="<?= BASE_URL ?>/api/export.php?format=csv&report=load&department=<?= urlencode($filters['department']) ?>" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            CSV
        </a>
        <a href="<?= BASE_URL ?>/api/export.php?format=pdf&report=load&department=<?= urlencode($filters['department']) ?>" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            PDF
        </a>
    </div>
</div>

<div class="filter-bar">
    <form method="GET" class="filter-form">
        <select name="department" class="filter-select">
            <option value="">All Departments</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?= sanitize($d) ?>" <?= $filters['department'] === $d ? 'selected' : '' ?>><?= sanitize($d) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="<?= BASE_URL ?>/modules/reports/" class="btn btn-ghost">Reset</a>
    </form>
</div>

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-value"><?= $report['summary']['total_teachers'] ?></div>
        <div class="summary-label">Total Teachers</div>
    </div>
    <div class="summary-card">
        <div class="summary-value" style="color: #ef4444;"><?= $report['summary']['overload_count'] ?></div>
        <div class="summary-label">Overloaded</div>
    </div>
    <div class="summary-card">
        <div class="summary-value" style="color: #f59e0b;"><?= $report['summary']['underload_count'] ?></div>
        <div class="summary-label">Underloaded</div>
    </div>
    <div class="summary-card">
        <div class="summary-value" style="color: #22c55e;"><?= $report['summary']['normal_count'] ?></div>
        <div class="summary-label">Normal Load</div>
    </div>
    <div class="summary-card">
        <div class="summary-value"><?= $report['summary']['total_assigned_units'] ?></div>
        <div class="summary-label">Total Units</div>
    </div>
</div>

<div class="card">
    <h3>Teacher Details</h3>
    <div class="table-responsive">
        <table class="data-table report-table">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Max Units</th>
                    <th>Current Load</th>
                    <th>Status</th>
                    <th>Assigned Subjects</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($report['teachers'])): ?>
                    <tr>
                        <td colspan="8" class="empty-cell">
                            <div class="empty-state">
                                <div class="empty-state-icon">📊</div>
                                <div>No teachers found.</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($report['teachers'] as $t): ?>
                        <tr class="status-<?= $t['status'] ?>">
                            <td><?= sanitize($t['employee_id']) ?></td>
                            <td><?= sanitize($t['last_name'] . ', ' . $t['first_name']) ?></td>
                            <td><?= sanitize($t['department']) ?></td>
                            <td><?= sanitize($t['employment_type']) ?></td>
                            <td><?= $t['max_units'] ?></td>
                            <td><strong><?= $t['current_load'] ?></strong></td>
                            <td><span class="badge badge-<?= $t['status'] === 'overload' ? 'danger' : ($t['status'] === 'underload' ? 'warning' : ($t['status'] === 'near_limit' ? 'warning' : 'success')) ?>"><?= ucfirst($t['status']) ?></span></td>
                            <td>
                                <?php
                                $codes = array_column($t['assignments'], 'code');
                                echo sanitize(implode(', ', $codes)) ?: '-';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

