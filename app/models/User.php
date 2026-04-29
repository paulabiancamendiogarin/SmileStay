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
return $stmt->fetch();
}

public function findByEmail($email) {
$stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
$stmt->execute([$email]);
return $stmt->fetch();
}

public function create($data) {
$stmt = $this->db->prepare(
"INSERT INTO {$this->table} (name, email, password, role) VALUES (?, ?, ?, ?)"
);
$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
$role = $data['role'] ?? 'customer';

$stmt->execute([
$data['name'],
$data['email'],
$hashedPassword,
$role
]);
return $this->db->lastInsertId();
}

public function verifyPassword($email, $password) {
$user = $this->findByEmail($email);
if ($user && password_verify($password, $user['password'])) {
return $user;
}
return false;
}

public function update($id, $data) {
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


public function getAll() {
$stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
return $stmt->fetchAll();
}

public function emailExists($email) {
$stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE email = ?");
$stmt->execute([$email]);
return $stmt->fetchColumn() > 0;
}
}