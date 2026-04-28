<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/AuditLog.php';

requireAuth();
$pageTitle = 'Audit Log';
$extraCss = ['/assets/css/components.css'];

$model = new AuditLog();
$result = $model->getAll((int)($_GET['page'] ?? 1));
$entries = $result['data'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <h2>Audit Trail</h2>
    <table class="data-table">
        <thead>
            <tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>Details</th><th>IP</th></tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $e): ?>
                <tr>
                    <td><?= formatDate($e['created_at']) ?></td>
                    <td><?= sanitize($e['full_name'] ?? 'System') ?></td>
                    <td><span class="badge badge-info"><?= sanitize($e['action']) ?></span></td>
                    <td><?= sanitize($e['entity_type'] ?? '-') ?> #<?= $e['entity_id'] ?? '-' ?></td>
                    <td><?= sanitize($e['details'] ?? '-') ?></td>
                    <td><small><?= sanitize($e['ip_address'] ?? '-') ?></small></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
