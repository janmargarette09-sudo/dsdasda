<?php
// services/MatchEngine.php — Auto-matching algorithm (expertise→availability→units)

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/ConflictDetector.php';

class MatchEngine {
    private PDO $db;
    private Teacher $teacherModel;
    private Schedule $scheduleModel;
    private ConflictDetector $conflictDetector;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->teacherModel = new Teacher();
        $this->scheduleModel = new Schedule();
        $this->conflictDetector = new ConflictDetector();
    }
    
    /**
     * Run the auto-matching algorithm
     * Returns array of proposed assignments
     */
    public function run(?string $semester = null, ?string $schoolYear = null): array {
        $results = [];
        
        // Get all unassigned schedules (filtered by semester/school year)
        $unassigned = $this->scheduleModel->getUnassignedSchedules($semester, $schoolYear);
        
        // Get all active teachers with their data
        $teachersResult = $this->teacherModel->getAll(['status' => 'active'], 1, 9999);
        $teachers = $teachersResult['data'];
        
        // Pre-load all teacher availability and expertise for performance
        $teacherData = [];
        foreach ($teachers as $t) {
            $teacherData[$t['id']] = [
                'teacher' => $t,
                'expertise' => array_column($t['expertise'] ?? [], 'subject_area'),
                'availability' => $t['availability'] ?? [],
                'current_load' => $t['current_load'] ?? 0
            ];
        }
        
        foreach ($unassigned as $schedule) {
            $bestMatch = $this->findBestMatch($schedule, $teacherData);
            
            if ($bestMatch) {
                $results[] = [
                    'schedule_id' => $schedule['id'],
                    'teacher_id' => $bestMatch['teacher_id'],
                    'score' => $bestMatch['score'],
                    'rationale' => $bestMatch['rationale']
                ];
            }
        }
        
        return $results;
    }
    
    private function findBestMatch(array $schedule, array $teacherData): ?array {
        $bestScore = -1;
        $bestMatch = null;
        
        $subjectDept = $schedule['department'] ?? '';
        $subjectName = $schedule['subject_name'] ?? '';
        
        foreach ($teacherData as $tid => $data) {
            $teacher = $data['teacher'];
            
            // Check 1: Max units limit
            $newLoad = $data['current_load'] + ($schedule['units'] ?? 3);
            if ($newLoad > $teacher['max_units']) {
                continue;
            }
            
            // Check 2: Schedule conflict
            if ($this->conflictDetector->hasScheduleConflict($tid, $schedule)) {
                continue;
            }
            
            // Check 3: Availability
            $availScore = $this->scoreAvailability($schedule, $data['availability']);
            if ($availScore <= 0) {
                continue;
            }
            
            // Score components
            $score = 0;
            $rationale = [];
            
            // Expertise match (highest weight)
            $expScore = $this->scoreExpertise($subjectName, $subjectDept, $data['expertise']);
            $score += $expScore * 50;
            if ($expScore > 0) $rationale[] = "Expertise match: {$expScore}x";
            
            // Availability fit
            $score += $availScore * 30;
            $rationale[] = "Available slot";
            
            // Load balance (prefer less loaded teachers)
            $loadRatio = $teacher['max_units'] > 0 ? $data['current_load'] / $teacher['max_units'] : 1;
            $loadScore = 1 - $loadRatio;
            $score += $loadScore * 20;
            $rationale[] = "Load " . round($loadRatio * 100) . "%";
            
            // Employment type preference (full-time first)
            if ($teacher['employment_type'] === 'full_time') {
                $score += 5;
                $rationale[] = "Full-time";
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = [
                    'teacher_id' => $tid,
                    'score' => round($score, 2),
                    'rationale' => implode(', ', $rationale)
                ];
            }
        }
        
        return $bestMatch;
    }
    
    private function scoreExpertise(string $subjectName, string $subjectDept, array $expertise): float {
        $score = 0;
        foreach ($expertise as $exp) {
            $exp = strtolower($exp);
            $subj = strtolower($subjectName);
            $dept = strtolower($subjectDept);
            
            if (stripos($subj, $exp) !== false || stripos($exp, $subj) !== false) {
                $score = max($score, 1.0);
            } elseif (stripos($dept, $exp) !== false || stripos($exp, $dept) !== false) {
                $score = max($score, 0.5);
            }
        }
        return $score;
    }
    
    private function scoreAvailability(array $schedule, array $availability): float {
        $day = $schedule['day_of_week'];
        $start = strtotime($schedule['start_time']);
        $end = strtotime($schedule['end_time']);
        
        foreach ($availability as $avail) {
            if ($avail['day_of_week'] !== $day) continue;
            
            $availStart = strtotime($avail['start_time']);
            $availEnd = strtotime($avail['end_time']);
            
            if ($start >= $availStart && $end <= $availEnd) {
                return $avail['is_preferred'] ? 1.0 : 0.7;
            }
        }
        return 0;
    }
}
