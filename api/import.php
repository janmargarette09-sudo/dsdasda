<?php
// api/import.php — POST CSV/Excel → parse & store

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../services/ImportParser.php';
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../services/AuditLogger.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST only']);
    exit;
}

try {
    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'No file uploaded']);
        exit;
    }
    
    $type = $_POST['type'] ?? 'teachers'; // teachers, subjects, schedules
    $file = $_FILES['file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    
    if (!in_array(strtolower($ext), ALLOWED_IMPORT_TYPES)) {
        echo json_encode(['error' => 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_IMPORT_TYPES)]);
        exit;
    }
    
    $uploadDir = __DIR__ . '/../uploads/' . $type . '/';
    if (!is_dir($uploadDir)) {
        $created = @mkdir($uploadDir, 0775, true);
        if (!$created) {
            echo json_encode(['error' => 'Failed to create upload directory: ' . $uploadDir]);
            exit;
        }
    }
    
    $filename = date('Ymd_His') . '_' . basename($file['name']);
    $filepath = $uploadDir . $filename;
    $moved = move_uploaded_file($file['tmp_name'], $filepath);
    if (!$moved) {
        echo json_encode(['error' => 'Failed to save uploaded file']);
        exit;
    }
    
    $parser = new ImportParser();
    $parsed = $parser->parse($filepath, $type);
    
    // Validate
    $errors = [];
    if ($type === 'teachers') {
        $errors = $parser->validateTeachers($parsed['rows']);
    } elseif ($type === 'subjects') {
        $errors = $parser->validateSubjects($parsed['rows']);
    } elseif ($type === 'schedules') {
        $errors = $parser->validateSchedules($parsed['rows']);
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors, 'preview' => array_slice($parsed['rows'], 0, 5)]);
        exit;
    }
    
    // Import
    $imported = 0;
    if ($type === 'teachers') {
        $model = new Teacher();
        foreach ($parsed['rows'] as $row) {
            $model->create([
                'employee_id' => $row['employee_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'] ?? null,
                'phone' => $row['phone'] ?? null,
                'department' => $row['department'] ?? null,
                'employment_type' => $row['employment_type'] ?? 'full_time',
                'max_units' => $row['max_units'] ?? 24,
                'min_units' => $row['min_units'] ?? 12
            ]);
            $imported++;
        }
    } elseif ($type === 'subjects') {
        $model = new Subject();
        foreach ($parsed['rows'] as $row) {
            $model->create([
                'code' => $row['code'],
                'name' => $row['name'],
                'description' => $row['description'] ?? null,
                'units' => $row['units'],
                'lecture_hours' => $row['lecture_hours'] ?? 3,
                'lab_hours' => $row['lab_hours'] ?? 0,
                'department' => $row['department'] ?? null,
                'semester' => $row['semester'] ?? '1st',
                'year_level' => $row['year_level'] ?? 1
            ]);
            $imported++;
        }
    } elseif ($type === 'schedules') {
        $model = new Schedule();
        $subjectModel = new Subject();
        foreach ($parsed['rows'] as $row) {
            // Look up subject_id by code
            $subject = $subjectModel->getAll(['search' => $row['subject_code'] ?? ''], 1);
            $subjectId = null;
            if (!empty($subject['data'])) {
                foreach ($subject['data'] as $s) {
                    if ($s['code'] === ($row['subject_code'] ?? '')) {
                        $subjectId = $s['id'];
                        break;
                    }
                }
            }
            if (!$subjectId) {
                continue; // skip if subject not found
            }
            $model->create([
                'subject_id' => $subjectId,
                'day_of_week' => $row['day_of_week'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'room' => $row['room'] ?? null,
                'section' => $row['section'] ?? null,
                'school_year' => $row['school_year'] ?? ($_SESSION['current_school_year'] ?? '2024-2025'),
                'semester' => $row['semester'] ?? ($_SESSION['current_semester'] ?? '1st')
            ]);
            $imported++;
        }
    }
    
    AuditLogger::log('import', $type, null, "Imported $imported $type from $filename");
    echo json_encode(['success' => true, 'imported' => $imported, 'filename' => $filename]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
