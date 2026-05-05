<?php

class OtpCode
{
    private $db;
    private $table = 'otp_codes';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function deleteExpired(): void
    {
        $this->db->exec("DELETE FROM {$this->table} WHERE expires_at < NOW()");
    }

    public function deleteByEmailPurpose(string $email, string $purpose): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE email = ? AND purpose = ?"
        );
        $stmt->execute([$email, $purpose]);
    }

    public function insert(string $email, string $purpose, string $otpCode, string $expiresAt): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (email, otp_code, purpose, expires_at, failed_attempts, created_at)
             VALUES (?, ?, ?, ?, 0, NOW())"
        );
        $stmt->execute([$email, $otpCode, $purpose, $expiresAt]);
    }

    /** Replace any existing OTP rows for this email + purpose */
    public function replace(string $email, string $purpose, string $otpCode, string $expiresAt): void
    {
        $this->deleteByEmailPurpose($email, $purpose);
        $this->insert($email, $purpose, $otpCode, $expiresAt);
    }

    public function findLatest(string $email, string $purpose): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE email = ? AND purpose = ?
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$email, $purpose]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function incrementFailedAttempts(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET failed_attempts = failed_attempts + 1 WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    public function deleteById(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Seconds since last OTP was issued for cooldown (null if none).
     */
    public function secondsSinceLastIssue(string $email, string $purpose): ?int
    {
        $row = $this->findLatest($email, $purpose);
        if (!$row) {
            return null;
        }

        return max(0, time() - strtotime($row['created_at']));
    }
}
