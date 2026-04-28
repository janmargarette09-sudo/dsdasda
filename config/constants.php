<?php
// config/constants.php — Application constants

// Auto-detect base URL from project location
$docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$projectRoot = realpath(__DIR__ . '/..');
$basePath = '';
if ($docRoot && strpos($projectRoot, $docRoot) === 0) {
    $basePath = str_replace('\\', '/', substr($projectRoot, strlen($docRoot)));
}
define('BASE_URL', rtrim($basePath, '/'));

// Session timeout in seconds (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Teacher load limits
define('MAX_UNITS_PER_TEACHER', 24);
define('MIN_UNITS_PER_TEACHER', 12);
define('OVERLOAD_THRESHOLD', 21);

// Default pagination
define('ITEMS_PER_PAGE', 20);

// Upload limits
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB

// Allowed file types for import
define('ALLOWED_IMPORT_TYPES', ['csv','xlsx','xls']);

