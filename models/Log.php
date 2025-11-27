<?php
// FILE: models/Log.php

class Log {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Catat aktivitas ke database.
     * @param string $activity Deskripsi aktivitas.
     * @param int|null $userId ID user yang melakukan aktivitas (NULL jika tidak login).
     */
    public function createLog($activity, $userId = null) {
        $stmt = $this->pdo->prepare("INSERT INTO log_aktivitas (user_id, aktivitas) VALUES (:user_id, :aktivitas)");
        
        $params = [
            'aktivitas' => $activity,
            'user_id' => $userId
        ];

        try {
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            error_log("Error creating log: " . $e->getMessage());
            return false;
        }
    }

    // Ambil 10 Log Aktivitas Terbaru (untuk Admin Dashboard)
    public function getLatestLogs($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT l.aktivitas, l.waktu, u.nama 
            FROM log_aktivitas l 
            LEFT JOIN users u ON l.user_id = u.id 
            ORDER BY l.waktu DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}