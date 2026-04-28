<?php
// api/assignments.php — GET/POST/PUT assignments

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Assignment.php';
require_once __DIR__ . '/../services/AuditLogger.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$model = new Assignment();

try {
    switch ($method) {
        case 'GET':
            if (!empty($_GET['id'])) {
                echo json_encode($model->getById((int)$_GET['id']) ?: ['error' => 'Not found']);
            } elseif (!empty($_GET['teacher_id'])) {
                echo json_encode($model->getTeacherAssignments((int)$_GET['teacher_id']));
            } elseif (!empty($_GET['unassigned'])) {
                echo json_encode($model->getUnassignedSchedules());
            } else {
                $filters = [
                    'teacher_id' => $_GET['teacher_id'] ?? '',
                    'status' => $_GET['status'] ?? '',
                    'assignment_type' => $_GET['type'] ?? ''
                ];
                echo json_encode($model->getAll($filters, (int)($_GET['page'] ?? 1)));
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $id = $model->create(
                (int)$data['teacher_id'],
                (int)$data['schedule_id'],
                $data['assignment_type'] ?? 'manual',
                $data['rationale'] ?? null,
                $_SESSION['user_id'] ?? null
            );
            AuditLogger::logAssignment('create', $id, 'Manual assignment created');
            echo json_encode(['success' => true, 'id' => $id]);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (empty($data['id'])) {
                echo json_encode(['error' => 'ID required']);
                exit;
            }
            $model->updateStatus((int)$data['id'], $data['status'] ?? 'active');
            AuditLogger::logAssignment('update', (int)$data['id'], 'Status changed to ' . ($data['status'] ?? 'active'));
            echo json_encode(['success' => true]);
            break;
            
        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) {
                echo json_encode(['error' => 'ID required']);
                exit;
            }
            $model->delete($id);
            AuditLogger::logAssignment('delete', $id, 'Assignment removed');
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
