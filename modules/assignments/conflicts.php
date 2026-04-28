<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../services/ConflictDetector.php';
requireAuth();

$pageTitle = 'Conflict Review';
$extraCss = ['/assets/css/components.css'];

$detector = new ConflictDetector();
$overloaded = $detector->getOverloadedTeachers();
$underloaded = $detector->getUnderloadedTeachers();
$conflicts = $detector->getAllScheduleConflicts();

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <h2>⚠️ Conflict & Overload Review</h2>
    
    <?php if (!empty($overloaded)): ?>
        <h3 class="text-danger">Overloaded Teachers</h3>
        <table class="data-table">
            <thead>
                <tr><th>Employee ID</th><th>Name</th><th>Max Units</th><th>Current Load</th><th>Over By</th></tr>
            </thead>
            <tbody>
                <?php foreach ($overloaded as $t): ?>
                    <tr>
                        <td><?= sanitize($t['employee_id']) ?></td>
                        <td><?= sanitize($t['last_name'] . ', ' . $t['first_name']) ?></td>
                        <td><?= $t['max_units'] ?></td>
                        <td class="text-danger"><strong><?= $t['current_load'] ?></strong></td>
                        <td class="text-danger">+<?= round($t['current_load'] - $t['max_units'], 1) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-success">No overloaded teachers.</div>
    <?php endif; ?>
    
    <?php if (!empty($underloaded)): ?>
        <h3 class="text-warning">Underloaded Teachers</h3>
        <table class="data-table">
            <thead>
                <tr><th>Name</th><th>Min Units</th><th>Current Load</th><th>Under By</th></tr>
            </thead>
            <tbody>
                <?php foreach ($underloaded as $t): ?>
                    <tr>
                        <td><?= sanitize($t['last_name'] . ', ' . $t['first_name']) ?></td>
                        <td><?= $t['min_units'] ?></td>
                        <td class="text-warning"><strong><?= $t['current_load'] ?></strong></td>
                        <td><?= round($t['min_units'] - $t['current_load'], 1) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-success">No underloaded teachers.</div>
    <?php endif; ?>
    
    <?php if (!empty($conflicts)): ?>
        <h3 class="text-danger">Schedule Conflicts</h3>
        <table class="data-table">
            <thead>
                <tr><th>Subject 1</th><th>Time 1</th><th>Room</th><th>Subject 2</th><th>Time 2</th></tr>
            </thead>
            <tbody>
                <?php foreach ($conflicts as $c): ?>
                    <tr>
                        <td><?= sanitize($c['code1']) ?></td>
                        <td><?= sanitize($c['day1']) ?> <?= sanitize($c['start1']) ?>-<?= sanitize($c['end1']) ?></td>
                        <td><?= sanitize($c['room1'] ?? '-') ?></td>
                        <td><?= sanitize($c['code2']) ?></td>
                        <td><?= sanitize($c['day2']) ?> <?= sanitize($c['start2']) ?>-<?= sanitize($c['end2']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-success">No schedule conflicts detected.</div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
