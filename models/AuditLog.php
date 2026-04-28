<?php
// models/AuditLog.php — Insert & fetch audit records

require_once __DIR__ . '/../config/database.php';

class AuditLog {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll(int $page = 1, int $perPage = ITEMS_PER_PAGE): array {
        $countStmt = $this->db->query("SELECT COUNT(*) FROM audit_log");
        $total = (int)$countStmt->fetchColumn();
        
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("
            SELECT a.*, u.username, u.full_name
            FROM audit_log a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return ['data' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'totalPages' => (int)ceil($total / $perPage)];
    }
    
    public function create(array $data): void {
        $stmt = $this->db->prepare("
            INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
            VALUES (:uid, :action, :etype, :eid, :details, :ip, :ua)
        ");
        $stmt->execute([
            ':uid' => $data['user_id'] ?? null,
            ':action' => $data['action'],
            ':etype' => $data['entity_type'] ?? null,
            ':eid' => $data['entity_id'] ?? null,
            ':details' => $data['details'] ?? null,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
        ]);
    }
}
