<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Subject.php';
requireAuth();

$id = (int)($_GET['id'] ?? 0);
$model = new Subject();
$subject = $model->getById($id);

if (!$subject) {
    setFlash('error', 'Subject not found.');
    redirect('index.php');
}

$pageTitle = sanitize($subject['code'] . ' — ' . $subject['name']);
$extraCss = ['/assets/css/components.css'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="page-toolbar">
    <h2><?= sanitize($subject['code']) ?> — <?= sanitize($subject['name']) ?></h2>
    <a href="index.php" class="btn btn-outline">← Back to Subjects</a>
</div>

<div class="detail-grid">
    <div class="detail-card">
        <h3>Subject Information</h3>
        <dl class="detail-list">
            <dt>Code</dt><dd><?= sanitize($subject['code']) ?></dd>
            <dt>Name</dt><dd><?= sanitize($subject['name']) ?></dd>
            <dt>Description</dt><dd><?= sanitize($subject['description'] ?? '—') ?></dd>
            <dt>Units</dt><dd><?= $subject['units'] ?></dd>
            <dt>Lecture Hours</dt><dd><?= $subject['lecture_hours'] ?></dd>
            <dt>Lab Hours</dt><dd><?= $subject['lab_hours'] ?></dd>
            <dt>Department</dt><dd><?= sanitize($subject['department'] ?? '—') ?></dd>
            <dt>Semester</dt><dd><?= sanitize($subject['semester']) ?></dd>
            <dt>Year Level</dt><dd><?= $subject['year_level'] ?></dd>
        </dl>
        <div class="form-actions">
            <a href="edit.php?id=<?= $subject['id'] ?>" class="btn btn-primary">Edit</a>
        </div>

    <div class="detail-card">
        <h3>Prerequisites</h3>
        <?php if (empty($subject['prerequisites'])): ?>
            <p>No prerequisites.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($subject['prerequisites'] as $p): ?>
                    <li><a href="view.php?id=<?= $p['id'] ?>"><?= sanitize($p['code']) ?> — <?= sanitize($p['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="detail-card full-width">
        <h3>Schedules</h3>
        <?php if (empty($subject['schedules'])): ?>
            <p>No schedules defined.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Day</th><th>Time</th><th>Room</th><th>Section</th><th>Semester</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($subject['schedules'] as $s): ?>
                        <tr>
                            <td><?= sanitize($s['day_of_week']) ?></td>
                            <td><?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?></td>
                            <td><?= sanitize($s['room'] ?? '-') ?></td>
                            <td><?= sanitize($s['section'] ?? '-') ?></td>
                            <td><?= sanitize($s['semester']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
