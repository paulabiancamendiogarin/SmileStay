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

        // prevent duplicate email
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

    /** Used after OTP verification when password is already hashed */
    public function createWithHashedPassword(array $data): bool
    {
        if ($this->emailExists($data['email'])) {
            return false;
        }

        $fields = ['name', 'email', 'password', 'role', 'created_at'];
        $placeholders = ['?', '?', '?', '?', 'NOW()'];
        $values = [
            $data['name'],
            $data['email'],
            $data['password_hash'],
            $data['role'] ?? 'customer',
        ];

        if (array_key_exists('google_auth_secret', $data)) {
            $fields[] = 'google_auth_secret';
            $placeholders[] = '?';
            $values[] = $data['google_auth_secret'];
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

        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?"
        );

        return $stmt->execute($values);
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
}