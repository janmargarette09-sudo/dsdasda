<?php
// services/AuditLogger.php — Writes to audit_log table

require_once __DIR__ . '/../config/database.php';

class AuditLogger {
    private static ?PDO $db = null;
    
    private static function getDb(): PDO {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }
    
    /**
     * Log an action to the audit trail
     */
    public static function log(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void {
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
        
        $stmt = self::getDb()->prepare("
            INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
            VALUES (:uid, :action, :etype, :eid, :details, :ip, :ua)
        ");
        $stmt->execute([
            ':uid' => $userId,
            ':action' => $action,
            ':etype' => $entityType,
            ':eid' => $entityId,
            ':details' => $details,
            ':ip' => $ip,
            ':ua' => $ua
        ]);
    }
    
    /**
     * Log teacher-related actions
     */
    public static function logTeacher(string $action, int $teacherId, string $details = ''): void {
        self::log($action, 'teacher', $teacherId, $details);
    }
    
    /**
     * Log subject-related actions
     */
    public static function logSubject(string $action, int $subjectId, string $details = ''): void {
        self::log($action, 'subject', $subjectId, $details);
    }
    
    /**
     * Log assignment-related actions
     */
    public static function logAssignment(string $action, int $assignmentId, string $details = ''): void {
        self::log($action, 'assignment', $assignmentId, $details);
    }
}
