<?php
// models/Subject.php — Subject CRUD + prerequisite lookups

require_once __DIR__ . '/../config/database.php';

class Subject {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll(array $filters = [], int $page = 1, int $perPage = ITEMS_PER_PAGE): array {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['search'])) {
            $where[] = "(code LIKE :search OR name LIKE :search OR description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['department'])) {
            $where[] = "department = :dept";
            $params[':dept'] = $filters['department'];
        }
        if (!empty($filters['semester'])) {
            $where[] = "semester = :sem";
            $params[':sem'] = $filters['semester'];
        }
        if (!empty($filters['year_level'])) {
            $where[] = "year_level = :yl";
            $params[':yl'] = $filters['year_level'];
        }
        
        $whereSql = implode(' AND ', $where);
        
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM subjects WHERE $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("SELECT * FROM subjects WHERE $whereSql ORDER BY code LIMIT :limit OFFSET :offset");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $subjects = $stmt->fetchAll();
        
        foreach ($subjects as &$s) {
            $s['prerequisites'] = $this->getPrerequisites($s['id']);
            $s['schedule_count'] = $this->getScheduleCount($s['id']);
        }
        
        return ['data' => $subjects, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'totalPages' => (int)ceil($total / $perPage)];
    }
    
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
        $subject = $stmt->fetch();
        if (!$subject) return null;
        $subject['prerequisites'] = $this->getPrerequisites($id);
        $subject['schedules'] = $this->getSchedules($id);
        return $subject;
    }
    
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO subjects (code, name, description, units, lecture_hours, lab_hours, department, semester, year_level)
            VALUES (:code, :name, :desc, :units, :lec, :lab, :dept, :sem, :yl)
        ");
        $stmt->execute([
            ':code' => $data['code'],
            ':name' => $data['name'],
            ':desc' => $data['description'] ?? null,
            ':units' => $data['units'] ?? 3.0,
            ':lec' => $data['lecture_hours'] ?? 3.0,
            ':lab' => $data['lab_hours'] ?? 0.0,
            ':dept' => $data['department'] ?? null,
            ':sem' => $data['semester'] ?? '1st',
            ':yl' => $data['year_level'] ?? 1
        ]);
        $id = (int)$this->db->lastInsertId();
        
        if (!empty($data['prerequisite_ids']) && is_array($data['prerequisite_ids'])) {
            $this->setPrerequisites($id, $data['prerequisite_ids']);
        }
        return $id;
    }
    
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];
        
        $allowed = ['code','name','description','units','lecture_hours','lab_hours','department','semester','year_level','is_active'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = :$f";
                $params[":$f"] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        
        $this->db->prepare("UPDATE subjects SET " . implode(', ', $fields) . " WHERE id = :id")->execute($params);
        
        if (!empty($data['prerequisite_ids'])) {
            $this->setPrerequisites($id, $data['prerequisite_ids']);
        }
        return true;
    }
    
    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM subjects WHERE id = ?")->execute([$id]);
    }
    
    public function getPrerequisites(int $subjectId): array {
        $stmt = $this->db->prepare("
            SELECT s.id, s.code, s.name 
            FROM subject_prerequisites sp 
            JOIN subjects s ON sp.prerequisite_id = s.id 
            WHERE sp.subject_id = ?
        ");
        $stmt->execute([$subjectId]);
        return $stmt->fetchAll();
    }
    
    public function setPrerequisites(int $subjectId, array $prereqIds): void {
        $this->db->prepare("DELETE FROM subject_prerequisites WHERE subject_id = ?")->execute([$subjectId]);
        $stmt = $this->db->prepare("INSERT INTO subject_prerequisites (subject_id, prerequisite_id) VALUES (?, ?)");
        foreach ($prereqIds as $pid) {
            if ((int)$pid !== $subjectId) $stmt->execute([$subjectId, (int)$pid]);
        }
    }
    
    public function getSchedules(int $subjectId): array {
        $stmt = $this->db->prepare("SELECT * FROM schedules WHERE subject_id = ? ORDER BY day_of_week, start_time");
        $stmt->execute([$subjectId]);
        return $stmt->fetchAll();
    }
    
    public function getScheduleCount(int $subjectId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM schedules WHERE subject_id = ?");
        $stmt->execute([$subjectId]);
        return (int)$stmt->fetchColumn();
    }
    
    public function getDepartments(): array {
        return $this->db->query("SELECT DISTINCT department FROM subjects WHERE department IS NOT NULL ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getAllSimple(): array {
        return $this->db->query("SELECT id, code, name, units FROM subjects WHERE is_active = 1 ORDER BY code")->fetchAll();
    }

    public function getAllSubjectNames(): array {
        return $this->db->query("SELECT DISTINCT name FROM subjects WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    }
}
