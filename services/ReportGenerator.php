<?php
// services/ReportGenerator.php — Builds report data arrays

require_once __DIR__ . '/../config/database.php';

class ReportGenerator {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Generate full load assignment report
     */
    public function generateLoadReport(array $filters = []): array {
        $where = ['t.status = "active"'];
        $params = [];
        
        if (!empty($filters['department'])) {
            $where[] = "t.department = :dept";
            $params[':dept'] = $filters['department'];
        }
        
        $whereSql = implode(' AND ', $where);
        
        $stmt = $this->db->prepare("
            SELECT t.id, t.employee_id, t.first_name, t.last_name, t.department,
                   t.employment_type, t.max_units, t.min_units,
                   COALESCE(SUM(s.units), 0) as current_load,
                   COUNT(DISTINCT a.id) as assignment_count
            FROM teachers t
            LEFT JOIN assignments a ON t.id = a.teacher_id AND a.status = 'active'
            LEFT JOIN schedules sch ON a.schedule_id = sch.id
            LEFT JOIN subjects s ON sch.subject_id = s.id
            WHERE $whereSql
            GROUP BY t.id
            ORDER BY t.last_name, t.first_name
        ");
        $stmt->execute($params);
        $teachers = $stmt->fetchAll();
        
        // Add assignment details per teacher
        foreach ($teachers as &$teacher) {
            $teacher['status'] = $this->getLoadStatus($teacher);
            
            $stmt2 = $this->db->prepare("
                SELECT sub.code, sub.name, sub.units, sch.day_of_week, 
                       TIME_FORMAT(sch.start_time, '%H:%i') as start_time,
                       TIME_FORMAT(sch.end_time, '%H:%i') as end_time,
                       sch.room, sch.section
                FROM assignments a
                JOIN schedules sch ON a.schedule_id = sch.id
                JOIN subjects sub ON sch.subject_id = sub.id
                WHERE a.teacher_id = ? AND a.status = 'active'
                ORDER BY sch.day_of_week, sch.start_time
            ");
            $stmt2->execute([$teacher['id']]);
            $teacher['assignments'] = $stmt2->fetchAll();
        }
        
        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'filters' => $filters,
            'teachers' => $teachers,
            'summary' => $this->calculateSummary($teachers)
        ];
    }
    
    private function getLoadStatus(array $teacher): string {
        $load = (float)$teacher['current_load'];
        $max = (float)$teacher['max_units'];
        $min = (float)$teacher['min_units'];
        
        if ($max > 0 && $load > $max) return 'overload';
        if ($load < $min) return 'underload';
        if ($max > 0 && $load / $max > 0.85) return 'near_limit';
        return 'normal';
    }
    
    private function calculateSummary(array $teachers): array {
        $total = count($teachers);
        $overload = 0;
        $underload = 0;
        $normal = 0;
        $totalUnits = 0;
        
        foreach ($teachers as $t) {
            if ($t['status'] === 'overload') $overload++;
            elseif ($t['status'] === 'underload') $underload++;
            else $normal++;
            $totalUnits += (float)$t['current_load'];
        }
        
        return [
            'total_teachers' => $total,
            'overload_count' => $overload,
            'underload_count' => $underload,
            'normal_count' => $normal,
            'total_assigned_units' => $totalUnits,
            'average_load' => $total > 0 ? round($totalUnits / $total, 2) : 0
        ];
    }
    
    /**
     * Generate schedule coverage report
     */
    public function generateScheduleReport(): array {
        $stmt = $this->db->query("
            SELECT s.*, sub.code, sub.name, sub.units,
                   t.first_name, t.last_name, a.status as assign_status
            FROM schedules s
            JOIN subjects sub ON s.subject_id = sub.id
            LEFT JOIN assignments a ON s.id = a.schedule_id AND a.status = 'active'
            LEFT JOIN teachers t ON a.teacher_id = t.id
            WHERE s.is_active = 1
            ORDER BY FIELD(s.day_of_week,'Mon','Tue','Wed','Thu','Fri','Sat','Sun'), s.start_time
        ");
        return $stmt->fetchAll();
    }
}
