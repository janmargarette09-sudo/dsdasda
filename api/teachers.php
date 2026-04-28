<?php
// api/teachers.php — GET/POST/PUT/DELETE teachers (JSON API)
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Teacher.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$teacherModel = new Teacher();

// Simple routing by action param or HTTP method
$action = $_GET['action'] ?? '';

// GET endpoints
if ($method === 'GET') {
    if ($action === 'distribution') {
        // Return teacher load distribution for chart
        $db = Database::getInstance();
        $ranges = [
            '0-25%' => [0, 0.25],
            '26-50%' => [0.26, 0.50],
            '51-75%' => [0.51, 0.75],
            '76-100%' => [0.76, 1.0],
            'Overloaded' => [1.01, 999]
        ];
        $values = [];
        foreach ($ranges as $label => $range) {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM (
                    SELECT t.id, t.max_units,
                           COALESCE(SUM(s.units), 0) / NULLIF(t.max_units, 0) as ratio
                    FROM teachers t
                    LEFT JOIN assignments a ON t.id = a.teacher_id AND a.status = 'active'
                    LEFT JOIN schedules sch ON a.schedule_id = sch.id
                    LEFT JOIN subjects s ON sch.subject_id = s.id
                    WHERE t.status = 'active'
                    GROUP BY t.id
                    HAVING ratio >= ? AND ratio <= ?
                ) as sub
            ");
            $stmt->execute([$range[0], $range[1]]);
            $values[] = (int)$stmt->fetchColumn();
        }
        echo json_encode([
            'labels' => array_keys($ranges),
            'values' => $values
        ]);
        exit;
    }

    if (!empty($_GET['id'])) {
        $teacher = $teacherModel->getById((int)$_GET['id']);
        if (!$teacher) {
            http_response_code(404);
            echo json_encode(['error' => 'Teacher not found']);
            exit;
        }
        echo json_encode($teacher);
        exit;
    }

    // List with filters
    $filters = [
        'search' => $_GET['search'] ?? '',
        'department' => $_GET['department'] ?? '',
        'status' => $_GET['status'] ?? '',
        'employment_type' => $_GET['employment_type'] ?? ''
    ];
    $page = max(1, (int)($_GET['page'] ?? 1));
    $result = $teacherModel->getAll($filters, $page);
    echo json_encode($result);
    exit;
}

// POST - Create
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    try {
        $id = $teacherModel->create($data);
        http_response_code(201);
        echo json_encode(['id' => $id, 'message' => 'Teacher created']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// PUT - Update
if ($method === 'PUT') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID required']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    try {
        $teacherModel->update($id, $data);
        echo json_encode(['message' => 'Teacher updated']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// DELETE
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID required']);
        exit;
    }
    $teacherModel->delete($id);
    echo json_encode(['message' => 'Teacher deleted']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
