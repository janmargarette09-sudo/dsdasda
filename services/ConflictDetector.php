<?php
// services/ConflictDetector.php — Overload & schedule conflict checks

require_once __DIR__ . '/../config/database.php';

class ConflictDetector {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Check if a teacher has a schedule conflict with proposed schedule
     */
    public function hasScheduleConflict(int $teacherId, array $proposedSchedule): bool {
        $stmt = $this->db->prepare("
            SELECT sch.day_of_week, sch.start_time, sch.end_time
            FROM assignments a
            JOIN schedules sch ON a.schedule_id = sch.id
            WHERE a.teacher_id = ? AND a.status = 'active'
        ");
        $stmt->execute([$teacherId]);
        $existing = $stmt->fetchAll();
        
        $newDay = $proposedSchedule['day_of_week'];
        $newStart = strtotime($proposedSchedule['start_time']);
        $newEnd = strtotime($proposedSchedule['end_time']);
        
        foreach ($existing as $ex) {
            if ($ex['day_of_week'] !== $newDay) continue;
            
            $exStart = strtotime($ex['start_time']);
            $exEnd = strtotime($ex['end_time']);
            
            // Overlap check
            if ($newStart < $exEnd && $newEnd > $exStart) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if teacher would be overloaded
     */
    public function isOverloaded(int $teacherId, float $additionalUnits = 0): bool {
        $stmt = $this->db->prepare("
            SELECT t.max_units, COALESCE(SUM(s.units), 0) as current
            FROM teachers t
            LEFT JOIN assignments a ON t.id = a.teacher_id AND a.status = 'active'
            LEFT JOIN schedules sch ON a.schedule_id = sch.id
            LEFT JOIN subjects s ON sch.subject_id = s.id
            WHERE t.id = ?
            GROUP BY t.id
        ");
        $stmt->execute([$teacherId]);
        $result = $stmt->fetch();
        
        if (!$result) return false;
        
        return ($result['current'] + $additionalUnits) > $result['max_units'];
    }
    
    /**
     * Get all overloaded teachers
     */
    public function getOverloadedTeachers(): array {
        $stmt = $this->db->query("
            SELECT t.id, t.first_name, t.last_name, t.max_units, t.employee_id,
                   COALESCE(SUM(s.units), 0) as current_load
            FROM teachers t
            LEFT JOIN assignments a ON t.id = a.teacher_id AND a.status = 'active'
            LEFT JOIN schedules sch ON a.schedule_id = sch.id
            LEFT JOIN subjects s ON sch.subject_id = s.id
            WHERE t.status = 'active'
            GROUP BY t.id
            HAVING COALESCE(SUM(s.units), 0) > t.max_units
            ORDER BY (COALESCE(SUM(s.units), 0) - t.max_units) DESC
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Get all schedule conflicts (room + time overlap)
     */
    public function getAllScheduleConflicts(): array {
        $stmt = $this->db->query("
            SELECT 
                a.id as id1, a.day_of_week as day1, TIME_FORMAT(a.start_time,'%H:%i') as start1, TIME_FORMAT(a.end_time,'%H:%i') as end1, a.room as room1,
                b.id as id2, b.day_of_week as day2, TIME_FORMAT(b.start_time,'%H:%i') as start2, TIME_FORMAT(b.end_time,'%H:%i') as end2, b.room as room2,
                sa.code as code1, sb.code as code2
            FROM schedules a
            JOIN schedules b ON a.id < b.id
            JOIN subjects sa ON a.subject_id = sa.id
            JOIN subjects sb ON b.subject_id = sb.id
            WHERE a.is_active = 1 AND b.is_active = 1
              AND a.day_of_week = b.day_of_week
              AND a.start_time < b.end_time AND a.end_time > b.start_time
            ORDER BY a.day_of_week, a.start_time
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Check if a teacher falls below minimum units
     */
    public function getUnderloadedTeachers(): array {
        $stmt = $this->db->query("
            SELECT t.id, t.first_name, t.last_name, t.min_units,
                   COALESCE(SUM(s.units), 0) as current_load
            FROM teachers t
            LEFT JOIN assignments a ON t.id = a.teacher_id AND a.status = 'active'
            LEFT JOIN schedules sch ON a.schedule_id = sch.id
            LEFT JOIN subjects s ON sch.subject_id = s.id
            WHERE t.status = 'active'
            GROUP BY t.id
            HAVING COALESCE(SUM(s.units), 0) < t.min_units
            ORDER BY COALESCE(SUM(s.units), 0) ASC
        ");
        return $stmt->fetchAll();
    }
}
