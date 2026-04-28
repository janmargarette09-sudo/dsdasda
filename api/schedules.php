<?php
// api/schedules.php — GET/POST/PUT/DELETE schedules

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../services/AuditLogger.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$model = new Schedule();

try {
    switch ($method) {
        case 'GET':
            if (!empty($_GET['id'])) {
                $sched = $model->getById((int)$_GET['id']);
                echo json_encode($sched ?: ['error' => 'Not found']);
            } elseif (!empty($_GET['rooms'])) {
                echo json_encode($model->getRooms());
            } elseif (!empty($_GET['conflicts'])) {
                echo json_encode($model->getConflicts());
            } else {
                $filters = [
                    'day' => $_GET['day'] ?? '',
                    'room' => $_GET['room'] ?? '',
                    'subject_id' => $_GET['subject_id'] ?? '',
                    'semester' => $_GET['semester'] ?? ''
                ];
                echo json_encode($model->getAll($filters, (int)($_GET['page'] ?? 1)));
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $id = $model->create($data);
            AuditLogger::log('create', 'schedule', $id, 'Created schedule slot');
            echo json_encode(['success' => true, 'id' => $id]);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (empty($data['id'])) {
                echo json_encode(['error' => 'ID required']);
                exit;
            }
            $model->update((int)$data['id'], $data);
            AuditLogger::log('update', 'schedule', (int)$data['id'], 'Updated schedule');
            echo json_encode(['success' => true]);
            break;
            
        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) {
                echo json_encode(['error' => 'ID required']);
                exit;
            }
            $model->delete($id);
            AuditLogger::log('delete', 'schedule', $id, 'Deleted schedule');
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
