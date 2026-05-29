<?php

class User {
    private $db;
    private $table = 'users';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {

        if ($this->emailExists($data['email'])) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (name, email, password, role, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        );

        return $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 'customer'
        ]);
    }

    public function createWithHashedPassword(array $data): bool
    {
        if ($this->emailExists($data['email'])) {
            return false;
        }

        $role = $data['role'] ?? 'customer';
        $isApproved = ($role === 'admin') ? 1 : 0;
        $qrVerified = !empty($data['qr_verified']) ? 1 : 0;

        $fields = ['name', 'email', 'password', 'role', 'qr_verified', 'is_approved', 'created_at'];
        $placeholders = ['?', '?', '?', '?', '?', '?', 'NOW()'];
        $values = [
            $data['name'],
            $data['email'],
            $data['password_hash'],
            $role,
            $qrVerified,
            $isApproved,
        ];

        if (array_key_exists('google_auth_secret', $data)) {
            $fields[] = 'google_auth_secret';
            $placeholders[] = '?';
            $values[] = $data['google_auth_secret'];
        }

        if ($isApproved) {
            $fields[] = 'approved_at';
            $placeholders[] = 'NOW()';
        }

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (" . implode(', ', $fields) . ")
             VALUES (" . implode(', ', $placeholders) . ")"
        );

        return $stmt->execute($values);
    }

    public function verifyPassword($email, $password) {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function update($id, $data) {

        if (empty($data)) return false;

        $allowed = [
            'name', 'email', 'password', 'role', 'google_auth_secret',
            'qr_verified', 'is_approved', 'approved_at',
        ];

        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue;
            }
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?"
        );

        return $stmt->execute($values);
    }

    public function markQrVerified(int $id): bool
    {
        return $this->update($id, ['qr_verified' => 1]);
    }

    public function approve(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET is_approved = 1, approved_at = NOW() WHERE id = ? AND role = 'customer'"
        );

        return $stmt->execute([$id]);
    }

    public function getPendingApproval(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table}
             WHERE role = 'customer' AND is_approved = 0
             ORDER BY created_at ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCustomers(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table}
             WHERE role = 'customer'
             ORDER BY created_at DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function emailExists($email) {
        if (!$email) return false;

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingCount(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM {$this->table} WHERE role = 'customer' AND is_approved = 0"
        );

        return (int) $stmt->fetchColumn();
    }

    public function getMonthlyRegistrations(int $year): array
    {
        $stmt = $this->db->prepare(
            "SELECT MONTH(created_at) AS m, COUNT(*) AS c
             FROM {$this->table}
             WHERE YEAR(created_at) = ?
             GROUP BY MONTH(created_at)
             ORDER BY m ASC"
        );
        $stmt->execute([$year]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = array_fill(1, 12, 0);
        foreach ($rows as $r) {
            $out[(int) $r['m']] = (int) $r['c'];
        }
        return $out;
    }
}
