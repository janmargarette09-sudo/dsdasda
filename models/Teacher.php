<?php
// models/Teacher.php — Teacher CRUD + expertise queries

require_once __DIR__ . '/../config/database.php';

class Teacher {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all teachers with optional filters
     */
    public function getAll(array $filters = [], int $page = 1, int $perPage = ITEMS_PER_PAGE): array {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['search'])) {
            $where[] = "(first_name LIKE :search OR last_name LIKE :search OR employee_id LIKE :search OR email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['department'])) {
            $where[] = "department = :dept";
            $params[':dept'] = $filters['department'];
        }
        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['employment_type'])) {
            $where[] = "employment_type = :etype";
            $params[':etype'] = $filters['employment_type'];
        }
        
        $whereSql = implode(' AND ', $where);
        
        // Count total
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM teachers WHERE $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        
        // Fetch page
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("SELECT * FROM teachers WHERE $whereSql ORDER BY last_name, first_name LIMIT :limit OFFSET :offset");
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $teachers = $stmt->fetchAll();
        
        // Load expertise for each
        foreach ($teachers as &$t) {
            $t['expertise'] = $this->getExpertise($t['id']);
            $t['availability'] = $this->getAvailability($t['id']);
            $t['current_load'] = $this->getCurrentLoad($t['id']);
        }
        
        return [
            'data' => $teachers,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage)
        ];
    }
    
    /**
     * Get single teacher by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM teachers WHERE id = ?");
        $stmt->execute([$id]);
        $teacher = $stmt->fetch();
        if (!$teacher) return null;
        
        $teacher['expertise'] = $this->getExpertise($id);
        $teacher['availability'] = $this->getAvailability($id);
        $teacher['current_load'] = $this->getCurrentLoad($id);
        return $teacher;
    }
    
    /**
     * Create new teacher
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO teachers (employee_id, first_name, last_name, email, phone, department, max_units, min_units, employment_type, status)
            VALUES (:eid, :fn, :ln, :email, :phone, :dept, :max, :min, :etype, :status)
        ");
        $stmt->execute([
            ':eid' => $data['employee_id'],
            ':fn' => $data['first_name'],
            ':ln' => $data['last_name'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':dept' => $data['department'] ?? null,
            ':max' => $data['max_units'] ?? 24.0,
            ':min' => $data['min_units'] ?? 12.0,
            ':etype' => $data['employment_type'] ?? 'full_time',
            ':status' => $data['status'] ?? 'active'
        ]);
        $teacherId = (int)$this->db->lastInsertId();
        
        // Insert expertise if provided
        if (!empty($data['expertise']) && is_array($data['expertise'])) {
            $this->setExpertise($teacherId, $data['expertise']);
        }
        
        return $teacherId;
    }
    
    /**
     * Update teacher
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];
        
        $allowed = ['employee_id','first_name','last_name','email','phone','department','max_units','min_units','employment_type','status'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $sql = "UPDATE teachers SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        if (!empty($data['expertise']) && is_array($data['expertise'])) {
            $this->setExpertise($id, $data['expertise']);
        }
        
        return true;
    }
    
    /**
     * Delete teacher
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM teachers WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Get expertise for a teacher
     */
    public function getExpertise(int $teacherId): array {
        $stmt = $this->db->prepare("SELECT * FROM teacher_expertise WHERE teacher_id = ? ORDER BY proficiency_level, subject_area");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Set expertise (replace all)
     */
    public function setExpertise(int $teacherId, array $expertise): void {
        $this->db->prepare("DELETE FROM teacher_expertise WHERE teacher_id = ?")->execute([$teacherId]);
        $stmt = $this->db->prepare("INSERT INTO teacher_expertise (teacher_id, subject_area, proficiency_level) VALUES (?, ?, ?)");
        foreach ($expertise as $e) {
            $stmt->execute([$teacherId, $e['subject_area'], $e['proficiency_level'] ?? 'primary']);
        }
    }
    
    /**
     * Get availability slots
     */
    public function getAvailability(int $teacherId): array {
        $stmt = $this->db->prepare("SELECT * FROM teacher_availability WHERE teacher_id = ? ORDER BY FIELD(day_of_week,'Mon','Tue','Wed','Thu','Fri','Sat','Sun'), start_time");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Set availability (replace all)
     */
    public function setAvailability(int $teacherId, array $slots): void {
        $this->db->prepare("DELETE FROM teacher_availability WHERE teacher_id = ?")->execute([$teacherId]);
        $stmt = $this->db->prepare("INSERT INTO teacher_availability (teacher_id, day_of_week, start_time, end_time, is_preferred) VALUES (?, ?, ?, ?, ?)");
        foreach ($slots as $s) {
            $stmt->execute([$teacherId, $s['day_of_week'], $s['start_time'], $s['end_time'], $s['is_preferred'] ?? 1]);
        }
    }
    
    /**
     * Get current assigned units
     */
    public function getCurrentLoad(int $teacherId): float {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(s.units), 0) as total
            FROM assignments a
            JOIN schedules sch ON a.schedule_id = sch.id
            JOIN subjects s ON sch.subject_id = s.id
            WHERE a.teacher_id = ? AND a.status = 'active'
        ");
        $stmt->execute([$teacherId]);
        return (float)$stmt->fetchColumn();
    }
    
    /**
     * Get all departments (for filters)
     */
    public function getDepartments(): array {
        $stmt = $this->db->query("SELECT DISTINCT department FROM teachers WHERE department IS NOT NULL ORDER BY department");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

