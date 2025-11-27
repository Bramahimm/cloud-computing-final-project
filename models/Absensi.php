<?php
// FILE: models/Absensi.php

class Absensi {
  private $pdo;

  public function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  // Cek Status Absen Hari Ini
  public function getTodayStatus($userId, $date) {
    $stmt = $this->pdo->prepare("SELECT * FROM absensi WHERE user_id = :user_id AND tanggal = :tanggal");
    $stmt->execute(['user_id' => $userId, 'tanggal' => $date]);
    return $stmt->fetch();
  }

  // Absen Masuk (Insert)
  public function checkIn($userId, $date, $jamMasuk, $ip, $lokasi) {
    $stmt = $this->pdo->prepare("INSERT INTO absensi (user_id, tanggal, jam_masuk, ip_address, lokasi_masuk) VALUES (:user_id, :tanggal, :jam_masuk, :ip_address, :lokasi)");
    return $stmt->execute([
      'user_id' => $userId,
      'tanggal' => $date,
      'jam_masuk' => $jamMasuk,
      'ip_address' => $ip,
      'lokasi' => $lokasi
    ]);
  }

  // Absen Pulang (Update)
  public function checkOut($userId, $date, $jamPulang, $lokasi) {
    $stmt = $this->pdo->prepare("UPDATE absensi SET jam_pulang = :jam_pulang, lokasi_pulang = :lokasi_pulang WHERE user_id = :user_id AND tanggal = :tanggal AND jam_pulang IS NULL");
    return $stmt->execute([
      'jam_pulang' => $jamPulang,
      'lokasi_pulang' => $lokasi,
      'user_id' => $userId,
      'tanggal' => $date
    ]);
  }

  // Riwayat Absen User
  public function getHistory($userId, $limit = 10) {
    $stmt = $this->pdo->prepare("SELECT * FROM absensi WHERE user_id = :user_id ORDER BY tanggal DESC LIMIT :limit");
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  // Laporan Admin (Filter)
  public function getReport($userId = null, $startDate = null, $endDate = null) {
    $sql = "
            SELECT a.*, u.nama, u.email
            FROM absensi a
            JOIN users u ON a.user_id = u.id
            WHERE 1=1
        ";
    $params = [];

    if ($userId) {
      $sql .= " AND a.user_id = :user_id";
      $params['user_id'] = $userId;
    }
    if ($startDate) {
      $sql .= " AND a.tanggal >= :start_date";
      $params['start_date'] = $startDate;
    }
    if ($endDate) {
      $sql .= " AND a.tanggal <= :end_date";
      $params['end_date'] = $endDate;
    }

    $sql .= " ORDER BY a.tanggal DESC, a.jam_masuk DESC";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }
}
