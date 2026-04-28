<?php
// api/subjects.php — GET/POST/PUT/DELETE subjects

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../services/AuditLogger.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$model = new Subject();

try {
    switch ($method) {
        case 'GET':
            if (!empty($_GET['id'])) {
                $subject = $model->getById((int)$_GET['id']);
                echo json_encode($subject ?: ['error' => 'Not found']);
            } elseif (!empty($_GET['list'])) {
                echo json_encode($model->getAllSimple());
            } else {
                $filters = [
                    'search' => $_GET['search'] ?? '',
                    'department' => $_GET['department'] ?? '',
                    'semester' => $_GET['semester'] ?? '',
                    'year_level' => $_GET['year_level'] ?? ''
                ];
                echo json_encode($model->getAll($filters, (int)($_GET['page'] ?? 1)));
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $id = $model->create($data);
            AuditLogger::logSubject('create', $id, 'Created subject: ' . ($data['code'] ?? ''));
            echo json_encode(['success' => true, 'id' => $id]);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (empty($data['id'])) {
                echo json_encode(['error' => 'ID required']);
                exit;
            }
            $model->update((int)$data['id'], $data);
            AuditLogger::logSubject('update', (int)$data['id'], 'Updated subject');
            echo json_encode(['success' => true]);
            break;
            
        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) {
                echo json_encode(['error' => 'ID required']);
                exit;
            }
            $model->delete($id);
            AuditLogger::logSubject('delete', $id, 'Deleted subject');
            echo json_encode(['success' => true]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
