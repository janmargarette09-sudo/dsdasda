<?php
// modules/reports/export_csv.php — CSV download endpoint wrapper

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

// Redirect to API endpoint
$filters = ['department' => $_GET['department'] ?? ''];
$query = http_build_query(['format' => 'csv', 'report' => 'load'] + $filters);
header("Location: " . BASE_URL . "/api/export.php?$query");
exit;
