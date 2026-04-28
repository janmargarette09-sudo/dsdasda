<?php
// api/audit.php — GET audit trail entries

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/AuditLog.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'GET only']);
    exit;
}

try {
    $model = new AuditLog();
    echo json_encode($model->getAll((int)($_GET['page'] ?? 1)));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
