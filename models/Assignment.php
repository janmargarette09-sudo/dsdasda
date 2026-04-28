<?php
// models/Assignment.php — Assignment CRUD + rationale store

require_once __DIR__ . '/../config/database.php';

class Assignment {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll(array $filters = [], int $page = 1, int $perPage = ITEMS_PER_PAGE): array {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['teacher_id'])) {
            $where[] = "a.teacher_id = :tid";
            $params[':tid'] = $filters['teacher_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = "a.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['assignment_type'])) {
            $where[] = "a.assignment_type = :atype";
            $params[':atype'] = $filters['assignment_type'];
        }
        if (!empty($filters['semester'])) {
            $where[] = "sch.semester = :sem";
            $params[':sem'] = $filters['semester'];
        }
        if (!empty($filters['school_year'])) {
            $where[] = "sch.school_year = :sy";
            $params[':sy'] = $filters['school_year'];
        }
        
        $whereSql = implode(' AND ', $where);
        
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) FROM assignments a
            JOIN schedules sch ON a.schedule_id = sch.id
            WHERE $whereSql
        ");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("
            SELECT a.*, t.first_name, t.last_name, sub.code as subject_code, sub.name as subject_name,
                   sub.units, sch.day_of_week, sch.start_time, sch.end_time, sch.room, sch.section
            FROM assignments a
            JOIN teachers t ON a.teacher_id = t.id
            JOIN schedules sch ON a.schedule_id = sch.id
            JOIN subjects sub ON sch.subject_id = sub.id
            WHERE $whereSql
            ORDER BY a.created_at DESC
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
            SELECT a.*, t.first_name, t.last_name, sub.code as subject_code, sub.name as subject_name,
                   sch.day_of_week, sch.start_time, sch.end_time, sch.room
            FROM assignments a
            JOIN teachers t ON a.teacher_id = t.id
            JOIN schedules sch ON a.schedule_id = sch.id
            JOIN subjects sub ON sch.subject_id = sub.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public function create(int $teacherId, int $scheduleId, string $type = 'manual', ?string $rationale = null, ?int $assignedBy = null, string $status = 'pending'): int {
        $stmt = $this->db->prepare("
            INSERT INTO assignments (teacher_id, schedule_id, assigned_by, assignment_type, rationale, status)
            VALUES (:tid, :sid, :by, :type, :rationale, :status)
            ON DUPLICATE KEY UPDATE
                assignment_type = :type, rationale = :rationale, status = :status, updated_at = NOW()
        ");
        $stmt->execute([
            ':tid' => $teacherId,
            ':sid' => $scheduleId,
            ':by' => $assignedBy,
            ':type' => $type,
            ':rationale' => $rationale,
            ':status' => $status
        ]);
        return (int)$this->db->lastInsertId() ?: $this->getIdByPair($teacherId, $scheduleId);
    }
    
    private function getIdByPair(int $teacherId, int $scheduleId): int {
        $stmt = $this->db->prepare("SELECT id FROM assignments WHERE teacher_id = ? AND schedule_id = ?");
        $stmt->execute([$teacherId, $scheduleId]);
        return (int)$stmt->fetchColumn();
    }
    
    public function updateStatus(int $id, string $status): bool {
        return $this->db->prepare("UPDATE assignments SET status = ? WHERE id = ?")->execute([$status, $id]);
    }
    
    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM assignments WHERE id = ?")->execute([$id]);
    }
    
    public function getTeacherAssignments(int $teacherId): array {
        $stmt = $this->db->prepare("
            SELECT a.*, sub.code, sub.name, sub.units, sch.day_of_week, sch.start_time, sch.end_time, sch.room
            FROM assignments a
            JOIN schedules sch ON a.schedule_id = sch.id
            JOIN subjects sub ON sch.subject_id = sub.id
            WHERE a.teacher_id = ? AND a.status = 'active'
            ORDER BY sch.day_of_week, sch.start_time
        ");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }
    
    public function getUnassignedSchedules(): array {
        $stmt = $this->db->query("
            SELECT s.*, sub.code as subject_code, sub.name as subject_name, sub.units, sub.department
            FROM schedules s
            JOIN subjects sub ON s.subject_id = sub.id
            LEFT JOIN assignments a ON s.id = a.schedule_id AND a.status = 'active'
            WHERE s.is_active = 1 AND a.id IS NULL
            ORDER BY s.day_of_week, s.start_time
        ");
        return $stmt->fetchAll();
    }
    
    public function clearAutoAssignments(): bool {
        return $this->db->prepare("DELETE FROM assignments WHERE assignment_type = 'auto'")->execute();
    }
}
