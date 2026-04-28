<?php
// models/Schedule.php — Availability slot queries

require_once __DIR__ . '/../config/database.php';

class Schedule {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll(array $filters = [], int $page = 1, int $perPage = ITEMS_PER_PAGE): array {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['day'])) {
            $where[] = "day_of_week = :day";
            $params[':day'] = $filters['day'];
        }
        if (!empty($filters['room'])) {
            $where[] = "room LIKE :room";
            $params[':room'] = '%' . $filters['room'] . '%';
        }
        if (!empty($filters['subject_id'])) {
            $where[] = "subject_id = :sid";
            $params[':sid'] = $filters['subject_id'];
        }
        if (!empty($filters['semester'])) {
            $where[] = "semester = :sem";
            $params[':sem'] = $filters['semester'];
        }
        
        $whereSql = implode(' AND ', $where);
        
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM schedules WHERE $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("
            SELECT s.*, sub.code as subject_code, sub.name as subject_name, sub.units
            FROM schedules s
            JOIN subjects sub ON s.subject_id = sub.id
            WHERE $whereSql
            ORDER BY FIELD(s.day_of_week,'Mon','Tue','Wed','Thu','Fri','Sat','Sun'), s.start_time
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return ['data' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'totalPages' => (int)ceil($total / $perPage)];
    }
    
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT s.*, sub.code as subject_code, sub.name as subject_name, sub.units, sub.department
            FROM schedules s
            JOIN subjects sub ON s.subject_id = sub.id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO schedules (subject_id, day_of_week, start_time, end_time, room, section, school_year, semester)
            VALUES (:sid, :day, :start, :end, :room, :section, :sy, :sem)
        ");
        $stmt->execute([
            ':sid' => $data['subject_id'],
            ':day' => $data['day_of_week'],
            ':start' => $data['start_time'],
            ':end' => $data['end_time'],
            ':room' => $data['room'] ?? null,
            ':section' => $data['section'] ?? null,
            ':sy' => $data['school_year'] ?? '2024-2025',
            ':sem' => $data['semester'] ?? '1st'
        ]);
        return (int)$this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];
        
        $allowed = ['subject_id','day_of_week','start_time','end_time','room','section','school_year','semester','is_active'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = :$f";
                $params[":$f"] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        
        return $this->db->prepare("UPDATE schedules SET " . implode(', ', $fields) . " WHERE id = :id")->execute($params);
    }
    
    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM schedules WHERE id = ?")->execute([$id]);
    }
    
    public function getConflicts(int $excludeId = 0): array {
        $sql = "
            SELECT a.id as a_id, a.day_of_week as a_day, a.start_time as a_start, a.end_time as a_end, a.room as a_room,
                   b.id as b_id, b.day_of_week as b_day, b.start_time as b_start, b.end_time as b_end, b.room as b_room
            FROM schedules a
            JOIN schedules b ON a.id < b.id
            WHERE a.day_of_week = b.day_of_week
              AND a.is_active = 1 AND b.is_active = 1
              AND (
                  (a.start_time < b.end_time AND a.end_time > b.start_time)
                  OR (a.room IS NOT NULL AND a.room = b.room AND a.start_time < b.end_time AND a.end_time > b.start_time)
              )
        ";
        if ($excludeId) $sql .= " AND a.id != :ex AND b.id != :ex";
        $stmt = $this->db->prepare($sql);
        if ($excludeId) $stmt->execute([':ex' => $excludeId]);
        else $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getRooms(): array {
        return $this->db->query("SELECT DISTINCT room FROM schedules WHERE room IS NOT NULL ORDER BY room")->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get schedules that have no active assignment
     */
    public function getUnassignedSchedules(?string $semester = null, ?string $schoolYear = null): array {
        $where = "s.is_active = 1 AND s.id NOT IN (
                  SELECT schedule_id FROM assignments WHERE status = 'active'
              )";
        $params = [];
        
        if ($semester) {
            $where .= " AND s.semester = :sem";
            $params[':sem'] = $semester;
        }
        if ($schoolYear) {
            $where .= " AND s.school_year = :sy";
            $params[':sy'] = $schoolYear;
        }
        
        $stmt = $this->db->prepare("
            SELECT s.*, sub.code as subject_code, sub.name as subject_name, sub.units, sub.department
            FROM schedules s
            JOIN subjects sub ON s.subject_id = sub.id
            WHERE $where
            ORDER BY FIELD(s.day_of_week,'Mon','Tue','Wed','Thu','Fri','Sat','Sun'), s.start_time
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
