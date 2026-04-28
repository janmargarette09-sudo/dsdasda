<?php
// models/User.php — User CRUD for admin management

require_once __DIR__ . '/../config/database.php';

class User {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll(): array {
        $stmt = $this->db->query("SELECT id, username, full_name, email, role, is_active, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, username, full_name, email, role, is_active, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public function create(array $data): int {
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            INSERT INTO users (username, password_hash, full_name, email, role)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['username'], $hash, $data['full_name'], $data['email'], $data['role'] ?? 'chair'
        ]);
        return (int)$this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];
        
        $allowed = ['username','full_name','email','role','is_active'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = :$f";
                $params[":$f"] = $data[$f];
            }
        }
        if (!empty($data['password'])) {
            $fields[] = "password_hash = :ph";
            $params[':ph'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (empty($fields)) return false;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        return $this->db->prepare($sql)->execute($params);
    }
    
    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }
}
