<?php
// FILE: config/database.php

define('DB_HOST', 'localhost');
define('DB_USER', 'bramahimm'); // Ganti dengan user database MySQL Anda
define('DB_PASS', 'bramlafayet123'); // Ganti dengan password database Anda
define('DB_NAME', 'absensi_db'); // Ganti dengan nama database Anda

function getDBConnection() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (\PDOException $e) {
        // Log error ke file (lebih aman daripada menampilkan ke user)
        error_log("Database connection error: " . $e->getMessage(), 0);
        // Tampilkan pesan error sederhana ke user
        die("Koneksi database gagal. Silakan coba lagi nanti.");
    }
}