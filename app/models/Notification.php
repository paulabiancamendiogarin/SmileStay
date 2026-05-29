<?php

class Notification
{
    private $db;
    private $table = 'notifications';
    private static ?bool $tableExistsCache = null;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    private function tableExists(): bool
    {
        if (self::$tableExistsCache !== null) {
            return self::$tableExistsCache;
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*)
                 FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?"
            );
            $stmt->execute([$this->table]);
            self::$tableExistsCache = ((int) $stmt->fetchColumn()) > 0;
        } catch (Throwable $e) {
            self::$tableExistsCache = false;
        }

        return (bool) self::$tableExistsCache;
    }

    public function create(string $type, string $title, ?string $message = null): bool
    {
        if (!$this->tableExists()) {
            return false;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table} (type, title, message, is_read, created_at)
                 VALUES (?, ?, ?, 0, NOW())"
            );

            return $stmt->execute([$type, $title, $message]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getRecent(int $limit = 10): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ?"
            );
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getUnreadCount(): int
    {
        if (!$this->tableExists()) {
            return 0;
        }

        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE is_read = 0");
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    public function markAllRead(): bool
    {
        if (!$this->tableExists()) {
            return false;
        }

        try {
            return (bool) $this->db->exec("UPDATE {$this->table} SET is_read = 1 WHERE is_read = 0");
        } catch (Throwable $e) {
            return false;
        }
    }
}

