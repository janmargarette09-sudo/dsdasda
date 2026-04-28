<?php
// api/match_engine.php — POST → runs algorithm, returns JSON

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../services/MatchEngine.php';
require_once __DIR__ . '/../models/Assignment.php';
require_once __DIR__ . '/../services/AuditLogger.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST only']);
    exit;
}

// Read JSON body
$input = file_get_contents('php://input');
$data = json_decode($input, true) ?? $_POST;

try {
    $engine = new MatchEngine();
    $semester = $_SESSION['current_semester'] ?? null;
    $schoolYear = $_SESSION['current_school_year'] ?? null;
    $matches = $engine->run($semester, $schoolYear);
    
    // Optionally save the matches
    if (!empty($data['save']) || !empty($_POST['save'])) {
        $assignment = new Assignment();
        $saved = 0;
        
        foreach ($matches as $match) {
            $assignment->create(
                $match['teacher_id'],
                $match['schedule_id'],
                'auto',
                'Auto-matched: ' . $match['rationale'],
                $_SESSION['user_id'] ?? null,
                'active'
            );
            $saved++;
        }
        
        AuditLogger::log('auto_match', null, null, "Auto-matched $saved assignments");
        echo json_encode(['success' => true, 'matches' => $matches, 'saved' => $saved]);
    } else {
        echo json_encode(['success' => true, 'matches' => $matches, 'saved' => 0]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
