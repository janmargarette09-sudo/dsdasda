<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$pageTitle = 'Import Schedules';
$extraJs = ['/assets/js/import.js'];

require __DIR__ . '/../../includes/header.php';
?>

<div class="page-toolbar">
    <h2>Import Schedules</h2>
    <div class="toolbar-actions">
        <a href="<?= BASE_URL ?>/modules/schedules/" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to Schedules
        </a>
    </div>
</div>

<div class="card form-card">
    <div class="card-body">
        <div class="import-info">
            <h3>CSV Upload</h3>
            <p class="text-muted">Upload a file with columns: <code>subject_code, day_of_week, start_time, end_time, room, section, school_year, semester</code></p>
        </div>

        <form id="import-form" enctype="multipart/form-data" data-type="schedules">
            <div class="form-group">
                <label>Select File (CSV or Excel)</label>
                <div class="file-input-wrapper">
                    <input type="file" name="file" accept=".csv,.xlsx,.xls" required class="form-control file-input">
                </div>
            </div>

            <div class="form-group">
                <a href="<?= BASE_URL ?>/samples/schedules_template.csv" class="btn btn-sm btn-ghost" download>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download Template
                </a>
            </div>

            <div id="preview-area" style="display:none; margin-top: 1.5rem;">
                <h4>Preview</h4>
                <div class="table-responsive">
                    <table class="data-table" id="preview-table">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="form-actions" style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" id="import-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Import Data
                </button>
            </div>

            <div id="import-result" style="margin-top: 1rem;"></div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

