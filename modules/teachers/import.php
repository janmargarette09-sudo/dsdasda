<?php
// modules/teachers/import.php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../services/ImportParser.php';
require_once __DIR__ . '/../../models/Teacher.php';
requireAuth();

$pageTitle = 'Import Teachers';
$extraCss = ['/assets/css/premium-forms.css'];
$extraJs = ['/assets/js/import.js'];

$error = '';
$success = '';
$preview = [];

if ($_FILES && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Error code: ' . $file['error'];
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_IMPORT_TYPES)) {
            $error = 'Invalid file type. Please upload CSV, XLSX, or XLS.';
        } else {
            try {
                $parser = new ImportParser();
                $preview = $parser->parseTeachers($file['tmp_name'], $ext);
                if (empty($preview)) {
                    $error = 'No valid records found in the file.';
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Handle confirmed import
if ($_POST && isset($_POST['confirm_import']) && !empty($_POST['records'])) {
    $teacherModel = new Teacher();
    $imported = 0;
    foreach ($_POST['records'] as $recordJson) {
        $record = json_decode($recordJson, true);
        if ($record) {
            try {
                $teacherModel->create($record);
                $imported++;
            } catch (Exception $e) {
                // Skip duplicates
            }
        }
    }
    setFlash('success', "$imported teacher(s) imported successfully.");
    redirect('/modules/teachers/');
}

require __DIR__ . '/../../includes/header.php';
?>

<div class="form-page">
    <div class="form-page-header">
        <h2>Import Teachers</h2>
        <p>Bulk upload faculty records from a spreadsheet</p>
    </div>

    <?php if ($error): ?>
        <div class="flash flash-error"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <?php if (empty($preview)): ?>
        <!-- Upload Step -->
        <div class="premium-card">
            <div class="card-top teal"></div>
            <div class="card-head">
                <div class="head-icon">📥</div>
                <div class="head-text">
                    <h4>Upload File</h4>
                    <p>Supported formats: CSV, XLSX, XLS</p>
                </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="upload-zone" onclick="document.getElementById('csv_file').click()">
                        <div class="up-icon">📂</div>
                        <div class="up-title">Click to select a file</div>
                        <div class="up-hint">or drag and drop a CSV/Excel file here</div>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv,.xlsx,.xls" style="display:none" onchange="this.form.submit()">
                </form>

                <div style="margin-top:1.5rem;padding:1rem;background:var(--slate-50);border-radius:var(--radius-sm);">
                    <h5 style="font-size:0.85rem;font-weight:700;color:var(--slate-700);margin-bottom:0.5rem;">Expected Columns:</h5>
                    <code style="font-size:0.78rem;color:var(--slate-600);">
                        employee_id, first_name, last_name, email, phone, department, max_units, min_units, employment_type, status
                    </code>
                    <p style="margin-top:0.5rem;font-size:0.78rem;color:var(--slate-500);">
                        Download template: <a href="<?= BASE_URL ?>/samples/teachers_template.csv">teachers_template.csv</a>
                    </p>
                </div>
        </div>
    <?php else: ?>
        <!-- Preview Step -->
        <div class="premium-card">
            <div class="card-top amber"></div>
            <div class="card-head">
                <div class="head-icon">👁️</div>
                <div class="head-text">
                    <h4>Preview</h4>
                    <p><?= count($preview) ?> record(s) found. Review before confirming.</p>
                </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Dept</th>
                                <th>Type</th>
                                <th>Max Units</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($preview, 0, 20) as $row): ?>
                                <tr>
                                    <td><?= sanitize($row['employee_id'] ?? '-') ?></td>
                                    <td><?= sanitize(($row['last_name'] ?? '') . ', ' . ($row['first_name'] ?? '')) ?></td>
                                    <td><?= sanitize($row['department'] ?? '-') ?></td>
                                    <td><?= ucwords(str_replace('_', ' ', $row['employment_type'] ?? '')) ?></td>
                                    <td><?= $row['max_units'] ?? '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (count($preview) > 20): ?>
                        <p style="text-align:center;padding:0.75rem;color:var(--slate-500);font-size:0.85rem;">... and <?= count($preview) - 20 ?> more</p>
                    <?php endif; ?>
                </div>
        </div>

        <form method="POST">
            <?php foreach ($preview as $row): ?>
                <input type="hidden" name="records[]" value="<?= htmlspecialchars(json_encode($row)) ?>">
            <?php endforeach; ?>
            <div class="premium-actions">
                <button type="submit" name="confirm_import" value="1" class="btn btn-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Confirm Import
                </button>
                <a href="<?= BASE_URL ?>/modules/teachers/import.php" class="btn btn-ghost">Upload Different File</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
