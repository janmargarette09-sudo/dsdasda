<?php
// modules/dashboard/index.php — Main overview & stats
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$pageTitle = 'Dashboard';
$extraCss = ['/assets/css/dashboard.css'];
$extraJs = ['/assets/vendors/chart.js/chart.umd.min.js', '/assets/js/dashboard.js'];

// Fetch stats
$db = Database::getInstance();
$stats = [
    'total_teachers' => (int)$db->query("SELECT COUNT(*) FROM teachers WHERE status = 'active'")->fetchColumn(),
    'total_subjects' => (int)$db->query("SELECT COUNT(*) FROM subjects WHERE is_active = 1")->fetchColumn(),
    'total_schedules' => (int)$db->query("SELECT COUNT(*) FROM schedules WHERE is_active = 1")->fetchColumn(),
    'total_assignments' => (int)$db->query("SELECT COUNT(*) FROM assignments WHERE status = 'active'")->fetchColumn(),
    'pending_assignments' => (int)$db->query("SELECT COUNT(*) FROM assignments WHERE status = 'pending'")->fetchColumn(),
    'overload_teachers' => 0
];

// Find overloaded teachers
$stmt = $db->query("
    SELECT t.id, t.first_name, t.last_name, t.max_units,
           COALESCE(SUM(s.units), 0) as current_load
    FROM teachers t
    LEFT JOIN assignments a ON t.id = a.teacher_id AND a.status = 'active'
    LEFT JOIN schedules sch ON a.schedule_id = sch.id
    LEFT JOIN subjects s ON sch.subject_id = s.id
    WHERE t.status = 'active'
    GROUP BY t.id
    HAVING current_load > t.max_units
");
$overloadTeachers = $stmt->fetchAll();
$stats['overload_teachers'] = count($overloadTeachers);

// Recent assignments
$recent = $db->query("
    SELECT a.*, t.first_name, t.last_name, sub.name as subject_name, sub.code as subject_code
    FROM assignments a
    JOIN teachers t ON a.teacher_id = t.id
    JOIN schedules sch ON a.schedule_id = sch.id
    JOIN subjects sub ON sch.subject_id = sub.id
    ORDER BY a.created_at DESC
    LIMIT 10
")->fetchAll();

require __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-grid">
    <!-- KPI Cards -->
    <div class="kpi-cards">
        <div class="kpi-card kpi-teachers">
            <div class="kpi-icon">👨‍🏫</div>
            <div class="kpi-info">
                <div class="kpi-value"><?= $stats['total_teachers'] ?></div>
                <div class="kpi-label">Active Teachers</div>
            </div>
        </div>
        <div class="kpi-card kpi-subjects">
            <div class="kpi-icon">📖</div>
            <div class="kpi-info">
                <div class="kpi-value"><?= $stats['total_subjects'] ?></div>
                <div class="kpi-label">Active Subjects</div>
            </div>
        </div>
        <div class="kpi-card kpi-schedules">
            <div class="kpi-icon">🗓️</div>
            <div class="kpi-info">
                <div class="kpi-value"><?= $stats['total_schedules'] ?></div>
                <div class="kpi-label">Schedule Slots</div>
            </div>
        </div>
        <div class="kpi-card kpi-assignments">
            <div class="kpi-icon">⚡</div>
            <div class="kpi-info">
                <div class="kpi-value"><?= $stats['total_assignments'] ?></div>
                <div class="kpi-label">Active Assignments</div>
            </div>
        </div>
    </div>

    <!-- Alert Banner -->
    <?php if ($stats['overload_teachers'] > 0 || $stats['pending_assignments'] > 0): ?>
        <div class="alert-banner">
            <?php if ($stats['overload_teachers'] > 0): ?>
                <div class="alert-item alert-danger">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <strong><?= $stats['overload_teachers'] ?></strong> teacher(s) are overloaded beyond max units.
                    <a href="<?= BASE_URL ?>/modules/reports/">View Report →</a>
                </div>
            <?php endif; ?>
            <?php if ($stats['pending_assignments'] > 0): ?>
                <div class="alert-item alert-warning">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <strong><?= $stats['pending_assignments'] ?></strong> assignment(s) pending approval.
                    <a href="<?= BASE_URL ?>/modules/assignments/">Review →</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content Grid -->
    <div class="dashboard-main">
        <!-- Chart Section -->
        <div class="card chart-card">
            <div class="card-header">
                <h3>Teacher Load Distribution</h3>
            </div>
            <div class="card-body">
                <canvas id="loadChart" height="200"></canvas>
            </div>
        </div>

        <!-- Recent Assignments -->
        <div class="card recent-card">
            <div class="card-header">
                <h3>Recent Assignments</h3>
                <a href="<?= BASE_URL ?>/modules/assignments/" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <div>No assignments yet.</div>
                    </div>
                <?php else: ?>
                    <div class="recent-list">
                        <?php foreach ($recent as $r): ?>
                            <div class="recent-item">
                                <div class="recent-avatar">
                                    <?= strtoupper(substr($r['first_name'], 0, 1) . substr($r['last_name'], 0, 1)) ?>
                                </div>
                                <div class="recent-info">
                                    <div class="recent-teacher">
                                        <?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?>
                                    </div>
                                    <div class="recent-subject">
                                        <?= htmlspecialchars($r['subject_code'] . ' — ' . $r['subject_name']) ?>
                                    </div>
                                </div>
                                <div class="recent-meta">
                                    <span class="badge badge-<?= $r['status'] === 'active' ? 'success' : ($r['status'] === 'pending' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst($r['status']) ?>
                                    </span>
                                    <span class="time"><?= formatDate($r['created_at'], 'M j, g:i A') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-grid">
            <a href="<?= BASE_URL ?>/modules/teachers/create.php" class="action-card">
                <span class="action-icon">➕</span>
                <span class="action-label">Add Teacher</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/subjects/create.php" class="action-card">
                <span class="action-icon">📚</span>
                <span class="action-label">Add Subject</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/schedules/create.php" class="action-card">
                <span class="action-icon">🗓️</span>
                <span class="action-label">Add Schedule</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/assignments/generate.php" class="action-card">
                <span class="action-icon">⚡</span>
                <span class="action-label">Auto-Assign</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/reports/export_csv.php" class="action-card">
                <span class="action-icon">📊</span>
                <span class="action-label">Export CSV</span>
            </a>
            <a href="<?= BASE_URL ?>/modules/teachers/import.php" class="action-card">
                <span class="action-icon">📥</span>
                <span class="action-label">Import Teachers</span>
            </a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

