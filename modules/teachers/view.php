<?php
// modules/teachers/view.php — Teacher profile detail page
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Teacher.php';
requireAuth();

$id = (int)($_GET['id'] ?? 0);
$teacherModel = new Teacher();
$teacher = $teacherModel->getById($id);

if (!$teacher) {
    setFlash('error', 'Teacher not found.');
    redirect('/modules/teachers/');
}

$pageTitle = htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']);

require __DIR__ . '/../../includes/header.php';
?>

<div class="page-toolbar">
    <h2><?= htmlspecialchars($teacher['last_name'] . ', ' . $teacher['first_name']) ?></h2>
    <div class="toolbar-actions">
        <a href="<?= BASE_URL ?>/modules/teachers/edit.php?id=<?= $teacher['id'] ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
        </a>
        <a href="<?= BASE_URL ?>/modules/teachers/" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back
        </a>
    </div>
</div>

<div class="profile-grid">
    <div class="card profile-card">
        <div class="profile-header">
            <div class="profile-avatar"><?= strtoupper(substr($teacher['first_name'], 0, 1) . substr($teacher['last_name'], 0, 1)) ?></div>
            <div class="profile-info">
                <h3><?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?></h3>
                <span class="badge badge-<?= $teacher['status'] === 'active' ? 'success' : 'warning' ?>">
                    <?= ucwords(str_replace('_', ' ', $teacher['status'])) ?>
                </span>
                <p class="profile-meta"><?= htmlspecialchars($teacher['employee_id']) ?> • <?= ucwords(str_replace('_', ' ', $teacher['employment_type'])) ?></p>
            </div>
        </div>

        <div class="profile-details">
            <div class="detail-row">
                <span class="detail-label">Department</span>
                <span class="detail-value"><?= htmlspecialchars($teacher['department'] ?? '—') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?= htmlspecialchars($teacher['email'] ?? '—') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone</span>
                <span class="detail-value"><?= htmlspecialchars($teacher['phone'] ?? '—') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Unit Range</span>
                <span class="detail-value"><?= formatUnits($teacher['min_units']) ?> — <?= formatUnits($teacher['max_units']) ?></span>
            </div>
        </div>

        <div class="load-section">
            <h4>Current Load</h4>
            <?php
            $pct = $teacher['max_units'] > 0 ? ($teacher['current_load'] / $teacher['max_units']) * 100 : 0;
            $barClass = $pct > 100 ? 'danger' : ($pct > 85 ? 'warning' : 'success');
            ?>
            <div class="load-big">
                <div class="load-bar large">
                    <div class="load-fill load-<?= $barClass ?>" style="width:<?= min(100, $pct) ?>"></div>
                </div>
                <div class="load-numbers">
                    <strong><?= formatUnits($teacher['current_load']) ?></strong> / <?= formatUnits($teacher['max_units']) ?>
                    <span class="load-pct">(<?= number_format($pct, 1) ?>%)</span>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-side">
        <div class="card">
            <h4>Expertise</h4>
            <?php if (empty($teacher['expertise'])): ?>
                <p class="text-muted">No expertise recorded.</p>
            <?php else: ?>
                <div class="expertise-list">
                    <?php foreach ($teacher['expertise'] as $e): ?>
                        <div class="expertise-item">
                            <span class="expertise-name"><?= htmlspecialchars($e['subject_area']) ?></span>
                            <span class="badge badge-<?= $e['proficiency_level'] === 'primary' ? 'primary' : ($e['proficiency_level'] === 'secondary' ? 'secondary' : 'info') ?>">
                                <?= ucfirst($e['proficiency_level']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h4>Availability</h4>
            <?php if (empty($teacher['availability'])): ?>
                <p class="text-muted">No availability set.</p>
            <?php else: ?>
                <div class="availability-list">
                    <?php foreach ($teacher['availability'] as $a): ?>
                        <div class="availability-item">
                            <span class="day"><?= $a['day_of_week'] ?></span>
                            <span class="time"><?= date('g:i A', strtotime($a['start_time'])) ?> — <?= date('g:i A', strtotime($a['end_time'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

