<?php
// FILE: models/User.php

class User {
  private $pdo;

  public function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  // AUTH
  public function findByEmail($email) {
    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    return $stmt->fetch();
  }

  // CRUD
  public function getAllUsers() {
    $stmt = $this->pdo->query("SELECT id, nama, email, role, created_at FROM users ORDER BY created_at DESC");
    return $stmt->fetchAll();
  }

  public function find($id) {
    $stmt = $this->pdo->prepare("SELECT id, nama, email, role, created_at FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
  }

  public function create($nama, $email, $password, $role) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $this->pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, :role)");
    return $stmt->execute(['nama' => $nama, 'email' => $email, 'password' => $hashed_password, 'role' => $role]);
  }

  public function update($id, $nama, $email, $role) {
    $stmt = $this->pdo->prepare("UPDATE users SET nama = :nama, email = :email, role = :role WHERE id = :id");
    return $stmt->execute(['nama' => $nama, 'email' => $email, 'role' => $role, 'id' => $id]);
  }

  public function delete($id) {
    $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
    return $stmt->execute(['id' => $id]);
  }

  public function resetPassword($id, $new_password) {
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $this->pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    return $stmt->execute(['password' => $hashed_password, 'id' => $id]);
  }
}
